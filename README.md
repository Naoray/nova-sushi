# Sushi meets Nova
This repository is a demonstration on how you can combine Caleb Porzio's [sushi](https://github.com/calebporzio/sushi) package with [Laravel Nova](https://nova.laravel.com/) to create CRUD functionality for third-party APIs.

## Goal
What we want to do is transform the incoming API records from JSON/XML (whatever other format) into an Eloquent Model. With the resulting Eloquent records we can create relationship methods and create a corresponding Nova resource to view (read), create, update or delete the data. Utilizing the [model events](https://laravel.com/docs/8.x/eloquent#events) that are fired on create, update & delete we can add the final ingredient to connect the actions on our Model back to the API via [Observers](https://laravel.com/docs/8.x/eloquent#observers).

1. provide basic CRUD functionality for third-pary API records
2. make relations between those records visible
3. show relations between our local records and the external API records

**C** - create API records through a Nova resource formular
**R** - read API records and display them in Nova resource table 
**U** - update API records through a Nova resource formular
**D** - delete API records through a Nova action.

## Why?
**It always depends on the use case.**

If your project only sends data to an API and does not care about which records are created.. then you can stop right here! The approach we are exploring is only useful for projects who care what records are created and want to consume (and change) them. Hoever, the use case doesn't necessarily end here: Through Nova, you can explore the relations between the external records of the API or between your own database records and the API's records.

## How?
Consuming API's isn't rocket science. The thing that makes this approach interesting is not the complexity but rather the simplicity on how you can combine existing packages to create something that their creators probably didn't intend them to do.

---

Let's start by exploring which dependencies are necessary to make our little experiement work.

### Dependencies
The versions behind the package names are the ones which I used in this project:
- `calebporzio/sushi` (*v2.1.1*)
- `laravel/nova` (*3.22.0*)
- `stripe/stripe-php` (*v7.75.0*) or what I used `laravel/cashier` (*v12.9.1*) -> can be exchanged by (almost) every other API library

*Be aware that older or future versions might invalidate this approach!*

---

### The Basic Mechanic
If we check back into the *Goal* section, the first step is to transform the incoming data into an Elqouent Model. We will use *Sushi* to make this happen.

#### What's Sushi?
Caleb describes [Sushi](https://github.com/calebporzio/sushi) as `Eloquent's missing "array" driver.` which means that you can apply the `\Sushi\Sushi` Trait to any [Eloquent Model](https://laravel.com/docs/8.x/eloquent), give it a static `array` with the data you want it to provide. This enables you to treat your otherwise static data as a normal Eloquent Model, which means you can apply filter methods like `where()` or associate your static data with other *normal* Models.

```php
class Role extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        ['id' => 1, 'label' => 'admin'],
        ['id' => 2, 'label' => 'manager'],
        ['id' => 3, 'label' => 'user'],
    ];
}

// use where filter
$admin = Role::where('label', 'admin')->first();

// attach to other Eloquent models
$user = User::first();
$user->role()->associate($admin);
```

*Sushi* does this magic by creating a SQlite database for every model and populate the table's rows in runtime (s. [How It Works](https://github.com/calebporzio/sushi#how-it-works)). Instead of using static method we want to consume third-party API records. We omit the `$rows` property and instead use the `getRows()` method. The downside of omitting the `$rows` property on the model is, that *Sushi* now will not cache our results, but instead use an in memory database.

<!-- @question: Can we reenable the caching mechanics by overriding Sushi's core? -->

#### Transform API into Models
In this example we will be using the *Stripe API*, but you can use any (almost) any API. Let's start by creating a `StripeModel` from which our other *Stripe* related models will extend from.

```php
abstract class StripeModel extends Model
{
    use Sushi;

    abstract public static function apiResource(): string;
    abstract public static function keys(): array;

    public function getRows()
    {
        $apiResource = static::apiResource();

        return $this->transformToArray($apiResource::all(['limit' => 100], Cashier::stripeOptions()));
    }

    protected function transformToArray(Collection $stripeData): array
    {
        return collect($stripeData->data)
            ->map(function ($item) {
                return collect($item->toArray())
                    ->map(fn ($value, $key) => $this->castStripeData($key, $value))
                    ->all();
            })->all();
    }

    protected function castStripeData($key, $value)
    {
        if (is_array($value)) {
            $value = empty($value) ? null : $this->castAttributeAsJson($key, $value);
        } elseif (array_key_exists($key, $this->casts)) {
            $value = $this->castAttribute($key, $value);
        }

        return $value;
    }
}
```

As we discussed before, we use the `getRows()` method to retrieve API data and transform it into an array which is then consumable as Eloquent Model via *Sushi*. The `transformToArray()` method will be different per API. In *Stripe* we receive a `\Stripe\Collection` instance. We extract the data from the *Stripe* collection with `->data` and call `toArray()` on the given `Stripe\StripeObject[]` to retrieve the single key value pairs of each record. We then loop over the records and cast them with Laravel's existing `castAttribute()` method (or `castAttributeAsJson()` for arrays) into the format which is later defined on each individual child model.

Next we create the first Model we want to consume from the *Stripe API*: [Customer](https://stripe.com/docs/api/customers/object). We use the [Stripe documentation](https://stripe.com/docs/api/customers/object) to fill in our `$casts` and the `keys()` method. Then we return the `Stripe\Customer::class` from the `apiResource()` and voila: we can now retrieve Stripe Customers via our `Customer` model. We also add a `user()` method which returns a `HasOne` relation. On that note: we should also add the `Billable` trait to the `User` model (required from *Cashier*) and migrate the *Cashier* migrations. This will assure that we have a `stripe_id` column on our `users` table.

*If you don't use Cashier, just make sure your local model (User) has some column which points to the external resource (Customer).*

```php
use Stripe\Customer as StripeCustomer;

class Customer extends StripeModel
{
    protected $casts = [
        'created' => 'datetime',
        'preferred_locales' => 'array',
        'invoice_settings' => 'json',
        'metadata' => 'json',
        'address' => 'json',
        'sources' => 'json',
    ];

    public static function apiResource(): string
    {
        return StripeCustomer::class;
    }

    public static function keys(): array
    {
        return [
            'id',
            'object',
            'address',
            'balance',
            'created',
            'currency',
            'default_source',
            'delinquent',
            'description',
            'discount',
            'email',
            'invoice_prefix',
            'invoice_settings',
            'metadata',
            'name',
            'next_invoice_sequence',
            'phone',
            'preferred_locales',
            'shipping',
            'tax_exempt',
        ];
    }

    public function user()
    {
        return $this->hasOne(User::class, 'stripe_id');
    }
}
```

#### Nova Resource
*You should already have installed Nova, if not: [install nova](https://nova.laravel.com/docs/3.0/installation.html#installing-nova-via-composer).*

To view our API records we create a nova resource `php artisan nova:resource Customer`. We will add a `HasOne` relation as one `Customer` will always have one `User`. I also added `Email`, but you can go ahead and add any column we defined in our `keys()` method on the model.

```php
public function fields(Request $request)
{
    return [
        ID::make(__('ID'), 'id')->sortable(),
        HasOne::make('User'),
        Text::make('Email'),
    ];
}
```

Let's create now a `UserSeeder` to have a user availble which we will use to log into the Nova dashboard: `php artisan make:seeder UserSeeder`. Fill in the following, which will create a `User` with an email of `admin@test.com` and password of `password` and add the class to the `DatabaseSeeder`.

```php
// UserSeeder
public function run()
{
    User::factory()->create(['email' => 'admin@test.com']);
}

// Database Seeder
public function run()
{
    $this->call([UserSeeder::class]);
}
```

When we now run the `php artisan migrate:fresh --seed` command, we can login into our Nova dashboard at `/nova` with username `admin@test.com` and password `password`. If we checkout the `Customer` resource we will encounter an exception `Undefined offset: 0`. This is because *Sushi* is expecting some data and tries to get the first row to create the SQLite DB for your model, but we are currently returning an empty array. Let's fix that error by modifying our `StripeModel@transformToArray()` method. We will check if the `$transformed` array is empty and fill it with null values for each key.

```diff
protected function transformToArray(Collection $stripeData): array
{
+   $transformed = collect($stripeData->data)
        ->map(function ($item) {
            return collect($item->toArray())
                ->map(fn ($value, $key) => $this->castStripeData($key, $value))
                ->all();
        })->all();

+   if (empty($transformed)) {
+       $transformed = [array_fill_keys(static::keys(), null)];
+   }

+   return $transformed;
}

If you check the Nova dashboard again you will see that we got rid of the error, but now we see one row with `-` as value for every column. To get rid of this empty row - which is only created when no API results are returned - we add a `GlobalScope` to the `StripeModel@booted()` method which will filter out all records where the `id` is `null`.

```diff
\\ App\Models\StripeModel
+public static function booted()
+{
+   static::addGlobalScope('null-id', function (Builder $builder) {
+       $builder->whereKeyNot(null);
+   });
+}
```

Refresh again, and you should now see an empty table :smile:.

#### Get some demo Data
Before we continue with relationships and view our records in Nova, we first have to create some demo data. We will modify the previous created `UserSeeder` to create more `User` model and their corresponding `Stripe\Customer` records. I am using the previously `Billable` trait we added to the `User` Model earlier to be able to call `createAsStripeCustomer()`, but you can also create the records by directly calling the API methods (Just make sure to save the returned external_id into your local model -> User in our case).

```php
public function run()
{
    User::factory()->create(['email' => 'admin@test.com'])->createAsStripeCustomer();
    collect()->range(1, 5)->each(fn () => User::factory()->create()->createAsStripeCustomer());
}
```

The first line will create a `User` with the email of *admin@test.com*, which we will utilize later to log into the Nova dashboard, and a *Stripe Customer*. The second line creates 5 other `User` models and for each of them it also creates a *Stripe Customer*.

Let's re-run `php artisan migrate:fresh --seed`. Now checkout `nova/resources/customers`! You will notice that the `ID` column displays `0`, but apart from that we already can see the data we specified in our Nova resource. Let's fix the `0` values for the `ID` by adding `protected $keyType = 'string'` to our `StripeModel`. This will ensure that the primary-key of our *Sushi* model is treated as string rather than an integer. If you now click on the `view` link on a `Customer` Nova resource, you should be able to see the related `User` resource at the bottom of the resource.

#### The magic of Observers

## Notes
- [x] (READ) Basic Mechanic -> Stripe via Sushi to Model
- [x] Displaying results in Nova
- (Create, Update, Delete) -> Model Observers
- Relations between external records
- [x] Relations between internal and external records

## Restrictions
### Pagination
- not every API will work

### Search

## Possible Improvements
### Cache
- improves performance with the cost of a delayed update
### Transformers
- 
*spatie/laravel-fractal*

## Other Projects
- https://github.com/grosv/eloquent-sheets

## Sources
- [Sushi](https://github.com/calebporzio/sushi)
- [Laravel Nova](https://nova.laravel.com/)
- [Observers](https://laravel.com/docs/8.x/eloquent#observers)
- [Stripe API](https://stripe.com/docs/api)%  