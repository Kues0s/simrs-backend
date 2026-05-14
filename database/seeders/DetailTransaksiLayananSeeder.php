<?php

namespace Database\Seeders;

use App\Models\DetailTransaksiLayanan;
use Illuminate\Database\Seeder;

class DetailTransaksiLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DetailTransaksiLayanan::factory()->count(5)->create();
    }
}
