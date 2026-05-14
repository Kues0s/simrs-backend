<?php

namespace App\Models;

use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AntrianPembayaran extends Model
{
    use HasFactory;
    protected $table = 'antrian_pembayaran';
    protected $primaryKey = 'id_antrian_pay';
    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'no_pembayaran',
        'status_antrian',
        'waktu_masuk',
    ];

    protected $casts = [
        'id_transaksi' => 'integer',
        'no_pembayaran' => 'integer',
        'status_antrian' => 'string',
        'waktu_masuk' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }
}
