<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getInventory(Request $request)
    {
        return ResponseModel::success([
            'inventory' => Inventory::with(['product', 'stockHolder'])
                ->paginate(50)
        ]);
    }

    public function getUserInventory()
    {
        $user = Auth::user();

        return ResponseModel::success([
            'inventory' => Inventory::with(['product'])
                ->where('stock_holder_user_id', $user->id)
                ->paginate(50)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    public function transferStock(Request $request)
    {
        DB::beginTransaction();
        
        $seller = Auth::user();

        $validator = Validator::make($request->all(), [
            'buyer_user_id' => 'required',
            'product_id' => 'required',
            'count' => 'required|numeric|min:0|not_in:0',
            'amount' => 'required|numeric|min:0|not_in:0',
            'remarks' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }
        
        $seller_user_id = $seller->id;
        $buyer_user_id = $request->buyer_user_id;
        $count = $request->count;
        $amount = $request->amount;
        $remarks = $request->remarks;

        $buyer = User::find($buyer_user_id);

        $product_id = $request->product_id;
        $product = Product::find($product_id);

        if (!$buyer) {
            return ResponseModel::failed([
                'message' => 'User not found'
            ]);
        }

        if ($seller_user_id == $buyer_user_id) {
            return $this->errorResponse('Buyer and seller cannot be same');
        }

        $sellerInventory = Inventory::where('product_id', $product_id)
            ->where('stock_holder_user_id', $seller_user_id)
            ->first();

        $buyerInventory = Inventory::where('product_id', $product_id)
            ->where('stock_holder_user_id', $buyer_user_id)
            ->first();

        if (!$sellerInventory) {
            return $this->errorResponse('Seller inventory not found');
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

        if ($sellerInventory->stock < $count) {
            return $this->errorResponse('Insufficient stock. Available Stock - ' . $sellerInventory->stock);
        }

        $sellerInventory->stock -= $count;
        $buyerInventory->stock += $count;

        $buyerInventory->save();
        $sellerInventory->save();

        DB::commit();

        return $this->response(true, null, (['message' => $count . ' Transfer Successful from ' . $seller->name . ' to ' . $buyer->name]));
    }

    public function errorResponse($message)
    {
        return $this->response(
            false,
            (['message' => $message]),
            null
        );
    }


    public function response($succes, $error, $data)
    {
        return Response::json([
            'success' => $succes,
            'error' => $error,
            'data' => $data
        ]);
    }
}
