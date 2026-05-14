<?php

namespace App\Models;

use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksiObat extends Model
{
    protected $table = 'd_transaksi_obat';
    protected $primaryKey = 'id_detail_obat';
    public $timestamps = false;
    protected $fillable = [
        'id_transaksi',
        'id_obat',
        'jumlah_obat',
    ];

    protected $casts = [
        'id_transaksi' => 'integer',
        'id_obat' => 'integer',
        'jumlah_obat' => 'integer',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

}
