<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksiObat extends Model
{
    protected $table = 'd_transaksi_obat';
    protected $primaryKey = 'id_detail_obat';
    protected $fillable = [
        'id_transaksi',
        'id_obat',
        'jumlah_obat',
    ];
    protected $cast = [
        'jumlah_obat' => 'integer',
    ];
}
