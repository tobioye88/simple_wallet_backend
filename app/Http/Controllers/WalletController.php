<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    //
    public function balance(Request $request)
    {
        $user = $request->user;
        $wallet = Wallet::with('user')->where('user_id', $user->id)->first();
        return ResponseHelper::success($wallet);
    }

    public function transactions(Request $request)
    {
        $user = $request->user;
        $transactions = Transaction::
            with("wallet")
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();
        return ResponseHelper::success($transactions);
    }

    public function add(Request $request)
    {
        try {

            DB::beginTransaction();
            $transaction = $this->creditAmountToWallet(
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
            $transaction = $this->sendMoneyToAnotherWallet(
                $sourceWallet,
                $destinationWallet,
                $request->input('amount')
            );
            DB::commit();
            return ResponseHelper::success($transaction);
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error(null, $e->getMessage());
        }
    }

    public function creditAmountToWallet(User $user, Wallet $wallet, int $amount){
        $transaction = Transaction::create([
            'user_id' => $user['id'],
            'wallet_id' => $wallet['id'],
            'transaction_id' => strtoupper(Str::random()),
            'type' => 'CREDIT',
            'amount' => $amount,
            'currency' => "NGN",
        ]);
        $wallet->balance += $amount;
        $wallet->save();
        return $transaction;
    }

    public function debitAmountToWallet(User $user, Wallet $wallet, int $amount){
        $transaction = Transaction::create([
            'user_id' => $user['id'],
            'wallet_id' => $wallet['id'],
            'transaction_id' => strtoupper(Str::random()),
            'type' => 'DEBIT',
            'amount' => $amount,
            'currency' => "NGN",
        ]);
        $wallet->balance -= $amount;
        $wallet->save();
        return $transaction;
    }

    public function sendMoneyToAnotherWallet(Wallet $sourceWallet, Wallet $destinationWallet, int $amount){
        if($sourceWallet->balance > $amount){
            $sourceWallet->balance -= $amount;
            $sourceWallet->save();

            $destinationWallet->balance += $amount;
            $destinationWallet->save();

            $transaction = $this->creditAmountToWallet(
                User::find($destinationWallet->user_id),
                $destinationWallet,
                $amount
            );
            $debitTransaction = $this->debitAmountToWallet(
                User::find($sourceWallet->user_id),
                $sourceWallet,
                $amount
            );
            return $debitTransaction;
        }
    }

}
