<?php

namespace App\Models;

use App\Models\DetailTransaksiLayanan;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Model;
use App\Models\AntrianPembayaran;


class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = [
        'pasien_id', 
        'id_antrian',
        'id_rm',
        'id_resep',
        'tanggal',
        'status'];

    protected $casts = [
        'pasien_id' => 'integer',
        'id_antrian' => 'integer',
        'id_rm' => 'integer',
        'id_resep' => 'integer',
        'tanggal' => 'datetime',
        'status' => 'string',
    ];

    // Relasi ke detail transaksi
    public function detailTransaksiLayanan()
    {
        return $this->hasMany(DetailTransaksiLayanan::class, 'id_transaksi', 'id_transaksi');
    }

    // Relasi ke pembayaran
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    public function antrianPembayaran()
    {
        return $this->hasOne(AntrianPembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    // 1. Menghitung Subtotal Layanan (Harga x Jumlah)
    public function getSubtotalLayananAttribute()
    {
        return $this->detailLayanan->sum(function ($layanan) {
            return $layanan->harga * $layanan->jumlah;
        });
    }
    
}
