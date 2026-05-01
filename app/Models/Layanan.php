<?php

namespace App\Models;

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
}
