<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'balance',
        'address',
        'balance',
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
}
