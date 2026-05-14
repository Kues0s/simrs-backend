<?php

namespace App\Models;

use App\Models\Transaksi;
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
        'status_pembayaran',
        'tanggal_pembayaran',
    ];

    protected $casts = [
        'id_transaksi' => 'integer',
        'jumlah_pembayaran' => 'decimal:1',
        'tanggal_pembayaran' => 'datetime',
    ];

    // Relasi ke transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
