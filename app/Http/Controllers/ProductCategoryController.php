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

        $query = ProductCategory::with([
            'subcategories'
        ])->where('parent_category_id', null);

        return ResponseModel::success(
            $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        );

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

    public function getCategoryDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed(
                $validator->errors()
            );
        }

        $query = ProductCategory::with(
            'parentCategory:id,name',
            'subcategories:id,name,parent_category_id',
        )->find($request->category_id);

        if (!$query) {
            return ResponseModel::failed([
                'message' => 'Product category not found'
            ]);
        }

        return ResponseModel::success(
            $query
        );
    }
}
