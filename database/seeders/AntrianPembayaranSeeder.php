<?php

namespace Database\Seeders;

use App\Models\AntrianPembayaran;
use Illuminate\Database\Seeder;

class AntrianPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AntrianPembayaran::factory()->count(5)->create();
    }
}
