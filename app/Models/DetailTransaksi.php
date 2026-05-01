<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_transaksi',
        'id_layanan',
        'id_obat',
        'jenis',
        'harga_detail',
        'jumlah',
        'total',
        'keterangan',
    ];
    protected $casts = [
        'id_transaksi' => 'integer',
        'id_layanan' => 'integer',
        'id_obat' => 'integer',
        'jenis' => 'string',
        'harga_detail' => 'decimal:1',
        'jumlah' => 'integer',
        'total' => 'decimal:1',
        'keterangan' => 'string',
    ];
}
