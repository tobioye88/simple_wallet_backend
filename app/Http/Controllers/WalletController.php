<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Services\TransactionService;

class WalletController extends Controller
{
    //
    public function balance(Request $request)
    {
        $user = $request->user;
        $wallet = Wallet::with('user')->where('user_id', $user->id)->first();
        return ResponseHelper::success($wallet);
    }

    public function add(Request $request)
    {
        try {
            DB::beginTransaction();
            $transaction = TransactionService::creditAmountToWallet(
                User::find($request->user->id),
                Wallet::where('user_id', $request->user->id)->first(),
                $request->input('amount'));
            DB::commit();
            return ResponseHelper::success($transaction);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(null, $e->getMessage());
        }
    }

    public function send(Request $request)
    {
        try {
            $request->validate([
                'wallet' => 'required|string',
                'amount' => 'required|integer',
            ]);
            DB::beginTransaction();
            $userId = $request->user->id;
            $address = $request->input('wallet');
            $sourceWallet = Wallet::where('user_id', $userId)->first();
            $destinationWallet = Wallet::where('address', $address)->first();

            if(!$sourceWallet){
                throw new Exception("Source wallet doesn't not exist");
            }
            if(!$destinationWallet){
                throw new Exception("Destination wallet doesn't not exist");
            }
            $transaction = TransactionService::sendMoneyToAnotherWallet(
                $sourceWallet,
                $destinationWallet,
                $request->input('amount')
            );
            DB::commit();
            return ResponseHelper::success($transaction);
        } catch (Exception $e) {
            DB::rollBack();
            return response(ResponseHelper::error(null, $e->getMessage()), 400);
        }
    }



}
