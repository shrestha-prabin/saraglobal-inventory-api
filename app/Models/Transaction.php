<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'seller_user_id',
        'buyer_user_id',
        'items_count',
        'amount',
        'remarks'
    ];


    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id')->withTrashed();
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id')->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }
}
