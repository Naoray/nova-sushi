<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stripe\Customer as StripeCustomer;
use Sushi\Sushi;

class Customer extends StripeModel
{
    use HasFactory;
    use Sushi;

    protected $casts = [
        'created' => 'datetime',
        'preferred_locales' => 'array',
        'invoice_settings' => 'array',
        'metadata' => 'json',
        'address' => 'json',
        'sources' => 'json',
    ];

    /**
     * Updateable attributes defined by Stripe.
     */
    public static $updateable = [
        'address',
        'description',
        'email',
        'metadata',
        'name',
        'phone',
        'shipping',
        'balance',
        'coupon',
        'default_source',
        'invoice_prefix',
        'invoice_settings',
        'next_invoice_sequence',
        'preferred_locales',
        'promotion_code',
        'source',
        'tax_exempt',
    ];

    public function getRows()
    {
        return $this->getConvertedRows(StripeCustomer::class);
    }

    /**
     * Returns all keys of the stripe object.
     */
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
