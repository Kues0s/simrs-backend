<?php

namespace App\Models;

use App\Models\DetailTransaksiLayanan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;
    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'nama_layanan',
        'tarif_dokter',
        'tarif_perawat',
        'status_layanan',
    ];
    
    protected $cast = [
        'tarif_dokter' => 'decimal:1',
        'tarif_perawat' => 'decimal:1',
    ];

    public function detailTransaksiLayanan()
    {
        return $this->hasMany(DetailTransaksiLayanan::class, 'id_layanan', 'id_layanan');
    }

}