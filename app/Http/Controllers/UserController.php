<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserList(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        return ResponseModel::success(
            $paginate
                ? User::paginate($perPage, ['*'], 'page', $currentPage)
                : User::all()
        );
    }

    /**
     * Return child user list of a user
     * If admin, return all users
     * If dealer, return subdealer assigned under the dealer
     * If subdealer, return clients assigned under the subdealer
     */
    public function getClientList(Request $request)
    {
        $user = Auth::user();

        // TODO: Return nested children
        // $dealers = [];
        // $subdealers = [];
        // $customers = [];

        return ResponseModel::success(
            User::where('parent_user_id', $user->id)->get()
        );
    }
}
