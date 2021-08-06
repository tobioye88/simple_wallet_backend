<?php


namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Str;

class TransactionService
{
    public static function creditAmountToWallet(User $user, Wallet $wallet, int $amount){
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

    public static function debitAmountToWallet(User $user, Wallet $wallet, int $amount){
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

    public static function sendMoneyToAnotherWallet(Wallet $sourceWallet, Wallet $destinationWallet, int $amount){
        if($sourceWallet->balance < $amount){
            throw new Exception("Insufficient funds");
        }
        if($sourceWallet->address == $destinationWallet->address){
            throw new Exception("Illegal transaction");
        }

        $transaction = self::creditAmountToWallet(
            User::find($destinationWallet->user_id),
            $destinationWallet,
            $amount
        );
        $debitTransaction = self::debitAmountToWallet(
            User::find($sourceWallet->user_id),
            $sourceWallet,
            $amount
        );
        return $debitTransaction;
    }
}
