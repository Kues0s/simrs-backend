<?php

namespace Database\Seeders;

use App\Models\DetailTransaksiObat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailTransaksiObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DetailTransaksiObat::factory()->count(5)->create();
    }
}
