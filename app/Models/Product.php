<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'brand',
        'category_id',
        'subcategory_id'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id')->withTrashed();
    }

    public function subcategory()
    {
        return $this->belongsTo(ProductCategory::class, 'subcategory_id')->withTrashed();
    }
}
