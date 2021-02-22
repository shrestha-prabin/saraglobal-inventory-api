<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $paginate = $request->paginate;
        $currentPage = $request->page;
        $perPage = $request->per_page;

        return ResponseModel::success([
            'users' => $paginate
                ? User::paginate($perPage, ['*'], 'page', $currentPage)
                : User::all()
        ]);
    }
}
