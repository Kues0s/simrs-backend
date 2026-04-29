<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';
    const UPDATED_AT = 'update_at';
    protected $fillable = [
        'id_pengguna', 
        'nik', 
        'id_antrian',
        'id_rm',
        'id_resep',
        'tanggal',
        'subtotal',
        'diskon',
        'pajak',
        'total_akhir',
        'status'];
    protected $casts = [
        'id_pengguna' => 'integer',
        'id_antrian' => 'integer',
        'id_rm' => 'integer',
        'id_resep' => 'integer',
        'tanggal' => 'datetime',
        'subtotal' => 'decimal:1',
        'diskon' => 'decimal:1',
        'pajak' => 'decimal:1',
        'total_akhir' => 'decimal:1',
        'status' => 'enum:menunggu,selesai,batal',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
