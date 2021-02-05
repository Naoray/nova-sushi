<?php

namespace App\Models;

use Sushi\Sushi;
use Mollie\Laravel\Facades\Mollie;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract;
use App\Transformers\CustomerTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use Sushi;
    use HasFactory;
    use HasMollieAccess;

    const CREATED_AT = 'createdAt';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'dashboard_link',
    ];

    protected function transformer(): TransformerAbstract
    {
        return new CustomerTransformer;
    }

    public function getRows()
    {
        $data = request()->has('page') && request()->has('perPage')
            ? Mollie::api()->customers()->page()
            : Mollie::api()->customers()->page(request('page'), request('perPage'));

        return $this->transformToArray(Mollie::api()->customers()->page(request('page') ?? null,  ?? null));
    }
}
