<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'stock_holder_user_id',
        'stock',
        'stock_defective',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function stockHolder()
    {
        return $this->belongsTo(User::class, 'stock_holder_user_id')->withTrashed();
    }
}
