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
    const UPDATED_AT = 'update_at';

    protected $fillable = [
        'nama_layanan',
        'kategori',
        'harga',
        'status_layanan',
    ];
    protected $cast = [
        'harga' => 'decimal:1',
    ];

    // Relasi ke detail transaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksiLayanan::class, 'id_layanan', 'id_layanan');
    }
}
