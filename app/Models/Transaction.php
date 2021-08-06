<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_id',
        'type',
        'amount',
        'currency',
    ];
    public function getUpdatedAtColumn()
    {
        return null;
    }

    public function user()
    {
        return $this->hasOne(User::class, "id", 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, "id", 'wallet_id');
    }
}
