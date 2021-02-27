<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;

class ResponseModel extends Model
{
    protected $fillable = [
        'success',
        'error',
        'data'
    ];

    public static function create($succes, $error, $data, $code)
    {
        return Response::json([
            'success' => $succes,
            'error' => $error,
            'data' => $data
        ], $code);
    }

    public static function success($data)
    {
        return ResponseModel::create(true, null, $data, 200);
    }

    public static function failed($error, $code=200)
    {
        return ResponseModel::create(false, $error, null, $code);
    }
}
