<?php

namespace App\Models;


use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class DetailTransaksiLayanan extends Model
{
    use HasFactory;
    protected $table = 'd_transaksi_layanan';
    protected $primaryKey = 'id_detail_layanan';
    public $timestamps = false;

    protected $fillable = [
        'id_layanan',
        'id_transaksi',
        'jumlah_layanan',
    ];
    protected $casts = [
        'id_layanan' => 'integer',
        'id_transaksi' => 'integer',
        'jumlah_layanan' => 'integer',
    ];

    // Relasi ke model Transaksi
    public function transaksi()
    {
        // Setiap detail transaksi layanan pasti terhubung ke 1 transaksi
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    // 1. Menghitung Subtotal Layanan (Harga x Jumlah)
    public function getSubtotalAttribute()
    {
        return $this->jumlah_layanan * ($this->layanan->tarif_dokter + $this->layanan->tarif_perawat);
    }
}
