<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'path'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
