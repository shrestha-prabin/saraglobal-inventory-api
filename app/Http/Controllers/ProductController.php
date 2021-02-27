<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ResponseModel;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    public function getProductList(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $query = Product::with([
            'category:id,name',
            'subcategory:id,name'
        ]);

        return ResponseModel::success(
            $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        );
    }

    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products|between:2,100',
            'description' => 'max:1000',
            'brand' => 'max:100',
            'category_id' => 'required',
            'subcategory_id' => ''
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed(
                $validator->errors()
            );
        }

        Product::create($validator->validated());

        return ResponseModel::success([
            'message' => 'New Product Added'
        ]);
    }

    public function getProductDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed(
                $validator->errors()
            );
        }

        $query = Product::with(
            'category:id,name',
            'subcategory:id,name',
        )->find($request->product_id);

        if (!$query) {
            return ResponseModel::failed([
                'message' => 'Product not found'
            ]);
        }

        return ResponseModel::success(
            $query
        );
    }


    public function getProductTransactionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial_no' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $serial_no = $request->serial_no;

        $query = TransactionItem::with([
            'inventoryItem:id,serial_no,product_id,user_id',
            'transaction:id,transaction_id,seller_user_id,buyer_user_id',
            'transaction.buyer' => function ($q) {
                $q->select('id', 'name', 'email');
            },
            'transaction.seller' => function ($q) {
                $q->select('id', 'name', 'email');
            },
        ])
            ->whereHas('inventoryItem', function ($query) use ($serial_no) {
                $query->where('serial_no', $serial_no);
            })->get();

        return ResponseModel::success([
            $query
        ]);
    }
}
