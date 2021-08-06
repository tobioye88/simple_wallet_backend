<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use App\Models\Wallet;
use App\Utilities\JWT;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    //login
    //register

    public function login(Request $request)
    {
        try{
            $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $body = $request->only(['email', 'password']);
            $user = User::where('email', $body['email'])->firstOrFail();
            if (!Hash::check($body['password'], $user['password'])) {
                throw new Exception("Invalid username or password");
            }
            $token = JWT::sign($user, "SUpErSecrete!@#@@@@");
            return ResponseHelper::success(['token' => $token]);
        }catch (Exception $e){
            return response(ResponseHelper::error($e, "Bad request"), 400);
        }

    }

    public function register(Request $request)
    {
        try{
            $body = $request->only([
                'first_name',
                'last_name',
                'password',
                'email',
                'bvn',
                'dob',
            ]);

            DB::beginTransaction();
            $body['password'] = bcrypt($body['password']);
            $user = User::create($body);
            $wallet = Wallet::create([
                'user_id' => (int) $user['id'],
                'address' => strtoupper(Str::random()),
                'balance' => 0,
                'currency' => 'NGN'
            ]);
            $user['wallet'] = $wallet;
            DB::commit();

            return ResponseHelper::success($user, "User created successfully");
        } catch(Exception $e){
            DB::rollBack();
            return response(ResponseHelper::error($e, "Bad request"), 400);
        }
    }
}
