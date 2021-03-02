<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Stripe\Plan as StripePlan;
use Sushi\Sushi;

class Plan extends StripeModel
{
    use HasFactory;
    use Sushi;

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
        return $this->getConvertedRows(StripePlan::class);
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
