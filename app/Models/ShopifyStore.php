<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopifyStore extends Model
{
    protected $fillable = [
        'name',
        'store_url',
        'access_token',
        'api_version',
        'collection_id',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function uploads()
    {
        return $this->hasMany(Upload::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function toShopifyConfig(): array
    {
        return [
            'store_url' => $this->store_url,
            'access_token' => $this->access_token,
            'api_version' => $this->api_version,
            'collection_id' => $this->collection_id,
        ];
    }
}
