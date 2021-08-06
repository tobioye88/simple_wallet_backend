<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class TransactionController extends Controller
{
    //
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
}
