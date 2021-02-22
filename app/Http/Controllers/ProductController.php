<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ResponseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     */
    public function getProductList(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $query = Product::with([
            'category:id,name',
            'subcategory:id,name'
        ]);

        return ResponseModel::success([
            'inventory' => $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products|between:2,100',
            'description' => 'max:1000',
            'brand' => 'max:100',
            'category_id' => 'required',
            'subcategory_id' => '',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date'
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

}
