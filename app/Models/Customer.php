<?php

namespace App\Models;

use Sushi\Sushi;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use App\Transformers\CustomerTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Cashier;
use Stripe\Customer as StripeCustomer;

class Customer extends Model
{
    use HasFactory;
    use Sushi, HasDirectStripeAccess;

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

    protected $casts = [
        'id' => 'string',
        'invoice_settings' => 'json',
        'created' => 'datetime',
        'preferred_localse' => 'array',
        'metadata' => 'json',
        'address' => 'json',
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

    protected function transformer(): TransformerAbstract
    {
        return new CustomerTransformer;
    }

    public function getRows()
    {
        return $this->transformToArray(StripeCustomer::all(['limit' => 100], Cashier::stripeOptions()));
    }

    /**
     * Returns all keys of the stripe object.
     */
    protected function stripeKeys(): array
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
}
