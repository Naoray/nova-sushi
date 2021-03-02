<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stripe\Product as StripeProduct;
use Sushi\Sushi;

class Product extends StripeModel
{
    use HasFactory;
    use Sushi;

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
        return $this->getConvertedRows(StripeProduct::class);
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

    /**
     * Returns all keys of the stripe object.
     */
    public static function keys(): array
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
