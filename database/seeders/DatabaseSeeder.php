<?php

namespace Database\Seeders;

// use App\Models\User;
use Database\Seeders\LayananSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LayananSeeder::class,
            TransaksiSeeder::class,
            AntrianPembayaranSeeder::class,
            DetailTransaksiLayananSeeder::class,
            DetailTransaksiObatSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
