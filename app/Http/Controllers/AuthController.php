<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->response(
                false,
                $validator->errors(),
                null
            );
        }

        if (!$token = Auth::attempt($validator->validated())) {
            return $this->response(
                false,
                ['message' => 'Invalid email or password'],
                null
            );
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->response(
                false,
                $validator->errors(),
                null
            );
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return $this->response(
            true,
            null,
            [
                'message' => 'User successfully registered',
                'user' => $user
            ],
        );
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(Auth::refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(Auth::user());
    }

    public function delete(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            return Response::json([
                "message" => "User Deleted Successfully"
            ]);
        } else {
            return Response::json([
                "message" => "User Not Found"
            ]);
        }
    }

    public function restore(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::withTrashed()->find($user_id);

        if ($user) {
            if ($user->deleted_at) {
                $user->restore();
                return Response::json([
                    "message" => "User Restored"
                ]);
            } else {
                return Response::json([
                    "message" => "User Already Restored"
                ]);
            }
        } else {
            return Response::json([
                "message" => "User Not Found"
            ]);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return $this->response(
            true,
            null,
            [
                'message' => 'Login Successful',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
                'user' => Auth::user()
            ]
        );
        return response()->json();
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
