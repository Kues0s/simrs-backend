<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntrianPembayaran extends Model
{
    protected $table = 'antrian_pembayaran';
    protected $primaryKey = 'id_antrian_pay';
    const CREATED_AT = null;
    const UPDATED_AT = null;

    protected $fillable = [
        'id_transaksi',
        'no_antrian_pay',
        'status_antrian',
        'waktu_masuk',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
