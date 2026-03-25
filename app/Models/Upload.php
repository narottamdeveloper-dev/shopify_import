<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{

const STATUS_PENDING = 'pending';
const STATUS_PROCESSING = 'processing';
const STATUS_COMPLETED = 'completed';
const STATUS_FAILED = 'failed';
    protected $fillable = [
        'file_name',
        'file_path',
        'status',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}