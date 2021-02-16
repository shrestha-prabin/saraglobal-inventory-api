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
    public function getProductList()
    {
        return ResponseModel::success([
            'inventory' => Product::with(['category:id,name', 'subcategory:id,name'])
                ->paginate(50)
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
