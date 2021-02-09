<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return Product::all();
        return Inventory::with(['product', 'stockHolder'])->get();
        return Inventory::find(1)->product;
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

        $seller_user_id = $request->get('seller_user_id');
        $buyer_user_id = $request->get('buyer_user_id');
        $product_id = $request->get('product_id');
        $count = $request->get('count');
        $amount = $request->get('amount');
        $remarks = $request->get('remarks');

        $seller = User::find($seller_user_id);
        $buyer = User::find($buyer_user_id);

        $product = Product::find($product_id);

        if (!$seller || !$buyer) {
            return $this->errorResponse('User not found');
        }

        if ($seller_user_id == $buyer_user_id) {
            return $this->errorResponse('Buyer and seller cannot be same');
        }

        if (!$seller || !$buyer) {
            return $this->errorResponse('User not found');
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
            return $this->errorResponse('Insufficient stock. Available Stock - '.$sellerInventory->stock);
        }
        
        $sellerInventory->stock -= $count;
        $buyerInventory->stock += $count;

        $buyerInventory->save();
        $sellerInventory->save();

        DB::commit();

        return $this->response(true, null, (['message' => $count.' Transfer Successful from '.$seller->name.' to '.$buyer->name]));
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
