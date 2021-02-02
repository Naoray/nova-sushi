<?php

namespace App\Models;

use Sushi\Sushi;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    use Sushi;

    const CREATED_AT = 'createdAt';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name', 'email'];

    public static function booted()
    {
        // static::addGlobalScope('null-id', function ($builder) {
        //     $builder->whereKeyNot(null);
        // });
    }

    public function getRows()
    {
        return collect(Mollie::api()->customers()->page())
            ->map(fn ($customerData) => [
                'id' => $customerData->id,
                'name' => $customerData->name,
                'email' => $customerData->email,
                'dashboard_link' => $customerData->_links->dashboard->href,
            ])
            ->all();
    }

    /**
     * Returns all keys of the stripe object.
     */
    protected function keys(): array
    {
        return [
            'id',
            'name',
            'email',
            'dashboard_link',
        ];
    }
}
