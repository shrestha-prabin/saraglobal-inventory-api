<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ResponseModel;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

use function PHPSTORM_META\map;

class InventoryController extends Controller
{
    /**
     * Add an unuqie product item
     */
    public function addInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial_num_prefix' => 'required',
            'serial_num_start' => 'required|int',
            'serial_num_end' => 'required|int',
            'product_id' => 'required',
            'is_defective' => 'boolean',
            'manufacture_date' => 'nullable|date',
            'expiry_date' => 'nullable|date'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $user = Auth::user();

        $serial_num_prefix = $request->serial_num_prefix;
        $serial_num_start = (int)$request->serial_num_start;
        $serial_num_end = (int)$request->serial_num_end;

        if ($serial_num_start > $serial_num_end) {
            return ResponseModel::failed([
                'message' => 'Invalid Range'
            ]);
        }

        $serial_numbers = [];
        $arrData = [];
        for ($i = $serial_num_start; $i <= $serial_num_end; $i++) {
            $serial_number = $serial_num_prefix . $i;

            if (Inventory::where('serial_no', $serial_number)->count() > 0) {
                return ResponseModel::success([
                    'message' => 'Serial Number: ' . $serial_number . ' already exists'
                ]);
            }
            $now = Carbon::now()->toDateTimeString();

            array_push($serial_numbers, $serial_number);
            array_push(
                $arrData,
                [

                    'product_id' => $request->product_id,
                    'is_defective' => $request->is_defective,
                    'manufacture_date' => $request->manufacture_date,
                    'expiry_date' => $request->expiry_date,
                    'user_id' => $user->id,
                    'serial_no' => $serial_number,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }

        DB::beginTransaction();

        Inventory::insert($arrData);

        // $user = Inventory::create(array_merge(
        //     $validator->validated(),
        //     [
        //         'user_id' => $user->id,
        //         'serial_no' => $serial_number
        //     ]
        // ));

        DB::commit();


        return ResponseModel::success([
            'message' => sizeof($serial_numbers) . ' New product added'
        ]);
    }

    /**
     * Accessible to role `admin`
     * Get inventory data of all users
     *
     * @return \Illuminate\Http\Response
     */
    public function getInventory(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $query = Inventory::with([
            'user:id,name',
            'product:id,name,category_id,subcategory_id',
            'product.category' => function ($q) {
                $q->select('id', 'name');
            },
            'product.subcategory' => function ($q) {
                $q->select('id', 'name');
            }
        ]);

        return ResponseModel::success(
            $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        );
    }

    /**
     * Get inventory data of single user
     */
    public function getUserInventory(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        $user = Auth::user();

        $query = Inventory::with([
            'product:id,name,category_id,subcategory_id',
            'product.category' => function ($q) {
                $q->select('id', 'name');
            },
            'product.subcategory' => function ($q) {
                $q->select('id', 'name');
            }
        ])->where('user_id', $user->id);

        return ResponseModel::success(
            $paginate
                ? $query->paginate($perPage, ['*'], 'page', $currentPage)
                : $query->get()
        );
    }

    /**
     * Invengory Item details
     */

    public function getInventoryItemDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'serial_no' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $query = Inventory::with([
            'product:id,name,category_id,subcategory_id',
            'product.category' => function ($q) {
                $q->select('id', 'name');
            },
            'product.subcategory' => function ($q) {
                $q->select('id', 'name');
            },
            'user:id,name,email',
        ])->where('serial_no', $request->serial_no)->first();

        if (!$query) {
            return ResponseModel::failed([
                'message' => 'Product inventory not found'
            ]);
        }

        return ResponseModel::success(
            $query
        );
    }

    /**
     * Transfer Inventory
     */
    public function transferInventory(Request $request)
    {
        DB::beginTransaction();

        $seller = Auth::user();

        $validator = Validator::make($request->all(), [
            'buyer_user_id' => 'required',
            'inventory_items' => 'required|array|min:1|not_in:0',
            'amount' => 'numeric|min:0|not_in:0',
            'remarks' => 'required|max:1000',
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        $seller_user_id = $seller->id;
        $buyer_user_id = $request->buyer_user_id;
        $inventory_item_ids = $request->inventory_items;
        $amount = $request->amount;
        $remarks = $request->remarks;

        // Check if buyer exists for given id
        if (!User::find($buyer_user_id)) {
            return ResponseModel::failed([
                'message' => 'Buyer not found'
            ]);
        }

        // Check if buyer and seller is same
        if ($seller_user_id == $buyer_user_id) {
            return ResponseModel::failed([
                'message' => 'Cannot transfer items to self'
            ]);
        }

        foreach ($inventory_item_ids as $inventory_item_id) {
            $inventory_item = Inventory::find($inventory_item_id);

            // Check if user holds the inventory item
            if ($inventory_item->user_id != $seller->id) {
                return ResponseModel::failed([
                    'message' => 'Item not found. Serial Number: ' . $inventory_item->serial_no
                ]);
            }

            // Transfer inventory item to new user
            // Change `user_id` each inventory item to buyer's id
            $inventory_item->user_id = $buyer_user_id;
            $inventory_item->save();
        }

        // Transaction Id format yy mm sequence

        $lastTransaction = Transaction::orderBy('created_at', 'DESC')->first();

        if ($lastTransaction) {
            $lastTransactionId = (int)$lastTransaction->transaction_id;
        } else {
            $lastTransactionId = 0;
        }

        $transactionId = str_pad($lastTransactionId + 1, 10, '0', STR_PAD_LEFT);

        // Remove year & month
        $transactionId = substr($transactionId, 4, strlen((string)$transactionId));

        // Add year & month
        $transactionId = date('ym') . $transactionId;

        // Save transaction
        $newTransaction = Transaction::create([
            'transaction_id' => $transactionId,
            'seller_user_id' => $seller_user_id,
            'buyer_user_id' => $buyer_user_id,
            'items_count' => sizeof($inventory_item_ids),
            'amount' => $amount,
            'remarks' => $remarks,
        ]);

        // Save each item separately
        foreach ($inventory_item_ids as $inventory_item_id) {

            TransactionItem::create([
                'inventory_item_id' => $inventory_item_id,
                'transaction_id' => $newTransaction->id
            ]);
        }

        DB::commit();

        return ResponseModel::success([
            'message' => sizeof($inventory_item_ids) . ' items transferred successfully'
        ]);
    }

    public function getProductStock(Request $request)
    {
        return Inventory::with('product')
            ->where('user_id', Auth::user()->id)
            ->selectRaw('product_id, COUNT(*) as count')
            ->groupBy('product_id')
            ->get();
    }

    public function getCategoryStock(Request $request)
    {
        $category_list = ProductCategory::where('parent_category_id', null)->get();
        $data = [];
        foreach ($category_list as $category_item) {
            $item = [
                'category_id' => $category_item->id,

                'count' => Inventory::with([
                    'product'
                ])
                    ->where('user_id', Auth::user()->id)
                    ->whereHas('product', function ($q) use ($category_item) {
                        $q->where('category_id', $category_item->id);
                    })->count(),

                'category' => ProductCategory::find($category_item->id)
            ];
            array_push($data, $item);
        }

        return $data;
    }
}
