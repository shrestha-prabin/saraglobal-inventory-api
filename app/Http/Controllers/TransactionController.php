<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function getTransactionList(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $user = Auth::user();

        $query = Transaction::with([
            'seller:id,name',
            'buyer:id,name',
            'product:id,name,category_id,subcategory_id'
        ])
            ->where('seller_user_id', $user->id)
            ->orWhere('buyer_user_id', $user->id);

        return ResponseModel::success([
            'transaction' =>  $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        ]);
    }
}
