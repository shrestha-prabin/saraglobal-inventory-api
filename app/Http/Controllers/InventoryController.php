<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ResponseModel;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function createInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'stock' => 'required|numeric|min:1|not_in:0'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $user = Auth::user();

        $product_id = $request->product_id;
        $stock = $request->stock;

        // Check if inventory for given product already exists
        $inventory = Inventory::where('product_id', $product_id)
            ->where('stock_holder_user_id', $user->id)
            ->first();

        // If inventory already exists, add new stock to existing inventory
        if ($inventory) {
            $inventory->stock += $stock;
            $inventory->save();
        } else {
            // Create new stock
            Inventory::create([
                'product_id' => $product_id,
                'stock_holder_user_id' => $user->id,
                'stock' => $stock
            ]);
        }

        return ResponseModel::success([
            'message' => 'New stocks added'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInventory(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $query = Inventory::with([
            'product', 'stockHolder'
        ]);

        return ResponseModel::success([
            'inventory' => $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        ]);
    }

    public function getUserInventory(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $user = Auth::user();

        $query = Inventory::with([
            'product'
        ])->where('stock_holder_user_id', $user->id);

        return ResponseModel::success([
            'inventory' => $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        ]);
    }

    public function transferStock(Request $request)
    {
        DB::beginTransaction();

        $seller = Auth::user();

        $validator = Validator::make($request->all(), [
            'buyer_user_id' => 'required',
            'product_id' => 'required',
            'stock' => 'required|numeric|min:1|not_in:0',
            'amount' => 'numeric|min:0|not_in:0',
            'remarks' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $seller_user_id = $seller->id;
        $buyer_user_id = $request->buyer_user_id;
        $stock = $request->stock;
        $amount = $request->amount;
        $remarks = $request->remarks;

        $buyer = User::find($buyer_user_id);

        $product_id = $request->product_id;
        $product = Product::find($product_id);

        if (!$buyer) {
            return ResponseModel::failed([
                'message' => 'Buyer not found'
            ]);
        }

        if ($seller_user_id == $buyer_user_id) {
            return ResponseModel::failed([
                'message' => 'Cannot transfer stock to self'
            ]);
        }

        $sellerInventory = Inventory::where('product_id', $product_id)
            ->where('stock_holder_user_id', $seller_user_id)
            ->first();

        $buyerInventory = Inventory::where('product_id', $product_id)
            ->where('stock_holder_user_id', $buyer_user_id)
            ->first();

        if (!$sellerInventory) {
            return ResponseModel::failed([
                'message' => 'Seller inventory not found'
            ]);
        }

        if (!$buyerInventory) {
            // Buyer inventory not found
            // Create new one
            $buyerInventory = new Inventory([
                'product_id' => $product_id,
                'stock_holder_user_id' => $buyer_user_id,
                'stock' => 0
            ]);
        }

        if ($sellerInventory->stock < $stock) {
            return ResponseModel::failed([
                'message' => 'Insufficient stock. Available Stock - ' . $sellerInventory->stock
            ]);
        }

        // perform transaction
        $sellerInventory->stock -= $stock;
        $buyerInventory->stock += $stock;

        $buyerInventory->save();
        $sellerInventory->save();


        $lastTransaction = Transaction::orderBy('created_at', 'DESC')->first();
        if ($lastTransaction) {
            $lastTransactionId = (int)$lastTransaction->transaction_id;
        } else {
            $lastTransactionId = 0;
        }

        // save transaction
        Transaction::create([
            'transaction_id' => str_pad($lastTransactionId + 1, 10, '0', STR_PAD_LEFT),
            'seller_user_id' => $seller_user_id,
            'buyer_user_id' => $buyer_user_id,
            'product_id' => $product_id,
            'stock' => $stock,
            'amount' => $amount,
            'remarks' => $remarks,
        ]);

        DB::commit();

        return ResponseModel::success([
            'message' => $stock . ' Transfer Successful from ' . $seller->name . ' to ' . $buyer->name
        ]);
    }
}
