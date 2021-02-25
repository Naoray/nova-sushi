<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Cashier;
use Stripe\Product as StripeProduct;
use Sushi\Sushi;

class Product extends Model
{
    use HasFactory;
    use Sushi, HasDirectStripeAccess;

    const TYPE_UNIFI = 'unifi';
    const TYPE_UNMS = 'unms';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created';

    protected $guarded = [];

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated';

    protected $casts = [
        'id' => 'string',
        'metadata' => 'object',
        'active' => 'boolean',
        'livemode' => 'boolean',
        'attributes' => 'json',
        'deactivate_on' => 'json',
        'images' => 'json',
        'package_dimensions' => 'json',
        'shippable' => 'boolean',
        'updated' => 'dateTime',
        'created' => 'dateTime',
    ];

    /**
     * Updateable attributes defined by Stripe.
     */
    public static $updateable = [
        'active',
        'description',
        'metadata',
        'name',
        'attributes',
    ];

    public function getRows()
    {
        return $this->transformToArray(
            config('app.env') !== 'testing'
                ? StripeProduct::all(['limit' => 100], Cashier::stripeOptions())
                : []
        );
    }

    /**
     * Returns the plans associated with the product.
     *
     * @return HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class, 'product');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeSubscribedTo($query, $teamId)
    {
        return $query->with([
            'plans' => function ($query) use ($teamId) {
                $query->subscribedTo($teamId);
            },
        ]);
    }

    /**
     * Returns all keys of the stripe object.
     */
    protected function stripeKeys(): array
    {
        return [
            'id',
            'object',
            'active',
            'attributes',
            'caption',
            'created',
            'deactivate_on',
            'description',
            'images',
            'livemode',
            'metadata',
            'name',
            'package_dimensions',
            'shippable',
            'type',
            'update',
            'url',
        ];
    }
}
