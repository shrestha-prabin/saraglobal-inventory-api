<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProductCategoryController extends Controller
{
    public function getProductCategories(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;


        return ResponseModel::success([
            'inventory' => $paginate
                ? ProductCategory::paginate($perPage, ['*'], 'page', $currentPage)
                : ProductCategory::all()
        ]);

        // $id = 2;

        // return ProductCategory::find(1)->subcategories;

        // return User::withTrashed()->find(2)->inventories;

        // return User::find(2)->parentUser;


        // return User::with(['parentUser'])->whereHas('parentUser', function ($query) use ($id) {   
        //     // $query->where('id', $id);   
        // })->get();

        // return Response::json([
        //     'user' => User::find($id),
        //     'child' => User::find($id)->childUsers,
        //     'parent' => User::find($id)->parentUser,
        // ]);
    }

    public function addProductCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:product_categories|between:2,100',
            'parent_category_id' => '',
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed(
                $validator->errors()
            );
        }

        ProductCategory::create($validator->validated());

        return ResponseModel::success([
            'message' => 'New Category Added'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
