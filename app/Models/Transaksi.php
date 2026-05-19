<?php

namespace App\Models;

use App\Models\AntrianPembayaran;
use App\Models\DetailTransaksiLayanan;
use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Transaksi extends Model
{
    use HasFactory;
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

    // Relasi ke detail transaksi layanan
    public function detailTransaksiLayanan()
    {
        return $this->hasMany(DetailTransaksiLayanan::class, 'id_transaksi', 'id_transaksi');
    }

    // Relasi ke detail transaksi obat
    public function detailTransaksiObat()
    {
        return $this->hasMany(DetailTransaksiObat::class, 'id_transaksi', 'id_transaksi');
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
    
    // Subtotal layanan
    public function getSubtotalLayananAttribute()
    {
        return $this->detailTransaksiLayanan->sum(function ($item) {
            return $item->subtotal;
        });
    }

    // Subtotal obat → 0 dulu karena data obat dari kelompok 4
    public function getSubtotalObatAttribute()
    {
        return 0;
    }

    // Total bayar
    public function getTotalBayarAttribute()
    {
        return $this->subtotal_layanan + $this->subtotal_obat;
    }
}
