<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Upload;

class Product extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'upload_id',
        'title',
        'description',
        'price',
        'status',
        'shopify_product_id',
        'error_message',
        'source_handle',
        'source_sku',
        'source_key',
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}
