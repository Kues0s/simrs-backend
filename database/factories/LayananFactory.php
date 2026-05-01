<?php

namespace Database\Factories;

use App\Models\Layanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Layanan>
 */
class LayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_layanan' => $this->faker->randomElement(['Tambal Gigi', 'Cabut Gigi', 'Pembersihan Karang', 'Pasang Behel', 'Pemeriksaan Gigi', 'Pemutihan Gigi', 'Perawatan Saluran Akar', 'Pembuatan Gigi Palsu', 'Pemasangan Crown', 'Pemasangan Implan']),
            'kategori' => 'Gigi',
            'harga' => $this->faker->randomFloat(1, 50000, 1000000), // Harga antara 50rb - 1JT
            'status_layanan' => 'aktif', // Kamu bisa pakai enum di sini jika sudah didefinisikan
        ];
    }
}
