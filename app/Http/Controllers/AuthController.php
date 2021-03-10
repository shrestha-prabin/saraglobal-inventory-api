<?php

namespace App\Http\Controllers;

use App\Models\ResponseModel;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Stringable;
use Illuminate\Support\Str;

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
            return ResponseModel::failed($validator->errors());
        }

        if (!$token = Auth::attempt($validator->validated())) {
            return ResponseModel::failed([
                'message' => 'Invalid email or password'
            ]);
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
            // 'password' => 'required|string|confirmed|min:6',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        DB::beginTransaction();
        $password = Str::random(8);

        $user = User::create(array_merge(
            $validator->validated(),
            [
                'password' => bcrypt($password)
            ]
        ));

        if (strtolower($request->role) != 'customer') {
            MailController::send(
                Auth::user()->name,
                $request->name,
                $request->email,
                $request->role,
                $password
            );
        }

        DB::commit();

        return ResponseModel::success([
            'message' => 'User successfully registered',
            'user' => $user
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return ResponseModel::success([
            'message' => 'User successfully signed out',
        ]);
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
        return ResponseModel::success([
            'user' => Auth::user(),
        ]);
    }

    public function delete(Request $request)
    {
        $user_id = $request->user_id;
        $user = User::find($user_id);

        if ($user) {
            $user->delete();
            return ResponseModel::success([
                'message' => 'User Deleted Successfully',
            ]);
        } else {
            return ResponseModel::failed([
                'message' => 'User Not Found'
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
                return ResponseModel::success([
                    'message' => "User Restored"
                ]);
            } else {
                return ResponseModel::failed([
                    'message' => 'User Already Restored'
                ]);
            }
        } else {
            return ResponseModel::failed([
                'message' => 'User Not Found'
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
        return ResponseModel::success([
            'message' => 'Login Successful',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user()
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users',
            'old_password' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }
        
        // compare old password and current password
        if (!Hash::check(
            $request->old_password,
            User::where('email', $request->email)->first('password')->password
        )) {
            return ResponseModel::failed([
                'message' => 'Invalid email or password'
            ]);
        }

        // update password
        $user = User::where('email', $request->email)->first();
        $user['password'] = bcrypt($request->password);
        $user->save();

        // clear token
        Auth::logout();

        return ResponseModel::success([
            'message' => 'Password updated successfully'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|exists:users'
        ]);

        if ($validator->fails()) {
            return ResponseModel::failed($validator->errors());
        }

        DB::beginTransaction();

        $email = $request->email;
        $user = User::where('email', $email)->first();

        $newPassword = Str::random(8);

        if (User::where('email', $email)->first()->role != 'customer') {
            MailController::sendPasswordResetMail(
                $email,
                $newPassword
            );
        } else {
            return ResponseModel::failed([
                'message' => 'Unauthorized',
            ], 401);
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        DB::commit();

        return ResponseModel::success([
            'message' => 'New Password has been sent to ' . $email
        ]);
    }
}
