<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_transaksi',
        'metode',
        'jumlah_pembayaran',
        'tanggal_pembayaran',
    ];

    protected $casts = [
        'jumlah_pembayaran' => 'decimal:2',
        'tanggal_pembayaran' => 'datetime',
    ];
}
