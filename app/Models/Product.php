<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Upload;

class Product extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'upload_id',
        'title',
        'description',
        'price',
        'status',
        'shopify_product_id',
        'error_message',
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }
}