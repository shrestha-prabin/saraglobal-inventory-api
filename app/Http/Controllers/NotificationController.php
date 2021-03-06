<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\ResponseModel;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        return ResponseModel::success([
            'expiring_items' => $this->getAboutToExpireItems()
            ]
        );
    }

    function getAboutToExpireItems() {
        $query = Inventory::with([
            'user:id,name',
            'product:id,name,category_id,subcategory_id',
            'product.category' => function ($q) {
                $q->select('id', 'name');
            },
            'product.subcategory' => function ($q) {
                $q->select('id', 'name');
            },
        ])->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'DESC');

        return $query->get()->map(function ($item) {
                $expiry_date = strtotime($item['expiry_date']);
                $now = time();
                $datediff = $expiry_date-$now;
                $days_remaining = floor($datediff/(60*60*24));
                $item['days_remaining'] = $days_remaining;
                $item['display_message'] = $item['serial_no'] . ' expires in ' . $days_remaining .' days';
                return $item;
            })->filter(function($item){
                // return $item['days_remaining'] <= 30;
                return true;
            });

    }
}
