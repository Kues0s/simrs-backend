<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksiLayanan extends Model
{
    protected $table = 'd_transaksi_obat';
    protected $primaryKey = 'id_detail_layanan';
    protected $fillable = [
        'id_layanan',
        'id_transaksi',
        'jumlah_layanan',
    ];
    protected $cast = [
        'jumlah_layanan' => 'integer',
    ];
}
