<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Laravel\Cashier\Cashier;
use Stripe\Plan as StripePlan;
use Sushi\Sushi;

class Plan extends Model
{
    use HasFactory;
    use Sushi, HasDirectStripeAccess;

    protected $keyType = 'string';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'metadata' => 'object',
        'active' => 'boolean',
        'livemode' => 'boolean',
        'tiers' => 'json',
        'created' => 'datetime',
    ];

    /**
     * Updateable attributes defined by Stripe.
     */
    public static $updateable = [
        'active',
        'metadata',
        'nickname',
        'product',
        'trial_period_days',
    ];

    public function getRows()
    {
        return $this->transformToArray(
            config('app.env') !== 'testing'
                ? StripePlan::all(['limit' => 100], Cashier::stripeOptions())
                : []
        );
    }

    /**
     * Returns the product associated with the plan.
     *
     * @return BelongsTo
     */
    public function stripeProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product');
    }

    /**
     * Get the subscription items related to the subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(SubscriptionItem::class, 'stripe_plan');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function getNameAttribute()
    {
        return Str::of($this->id)
            ->beforeLast('-')
            ->__toString();
    }

    /**
     * Returns all keys of the stripe object.
     */
    public static function keys(): array
    {
        return [
            'id',
            'object',
            'active',
            'aggregate_usage',
            'amount',
            'amount_decimal',
            'billing_scheme',
            'created',
            'currency',
            'interval',
            'interval_count',
            'livemode',
            'metadata',
            'nickname',
            'product',
            'tiers',
            'tiers_mode',
            'transform_usage',
            'trial_period_days',
            'usage_type',
        ];
    }
}
