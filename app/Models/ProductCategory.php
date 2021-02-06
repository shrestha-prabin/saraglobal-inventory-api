<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'parent_category_id'
    ];

    public function parentCategory()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_category_id')->withTrashed();
    }

    public function subcategories()
    {
        return $this->hasMany(ProductCategory::class, 'parent_category_id')->withTrashed();
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')->withTrashed();
    }
}
