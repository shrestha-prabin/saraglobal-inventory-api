<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function getTransactionList(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $user = Auth::user();

        $query = Transaction::with([
            'seller:id,name,email',
            'buyer:id,name,email',
            'items:id,inventory_item_id,transaction_id',
            'items.inventoryItem' => function ($query) {
                $query->select('id', 'serial_no', 'product_id');
            },
        ])
            ->where('seller_user_id', $user->id)
            ->orWhere('buyer_user_id', $user->id)
            ->orderBy('created_at', 'DESC');

        return ResponseModel::success(
            $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        );
    }

    public function getTransactionDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $details = Transaction::with([
            'seller:id,name,email',
            'buyer:id,name,email',
            'items.inventoryItem' => function ($query) {
                $query->select('id', 'serial_no', 'product_id');
            },
            'items.inventoryItem.product' => function ($query) {
                $query->select('id', 'name', 'category_id', 'subcategory_id');
            },
            'items.inventoryItem.product.category' => function ($query) {
                $query->select('id', 'name');
            },
            'items.inventoryItem.product.subcategory' => function ($query) {
                $query->select('id', 'name');
            }
        ])
            ->where('transaction_id', $request->transaction_id)->get();

        if (sizeof($details) == 0) {
            return ResponseModel::failed([
                'message' => 'Transaction not found'
            ]);
        }

        return ResponseModel::success(
            $details
        );
    }

}
