<?php

namespace Database\Factories;

use App\Models\DetailTransaksiLayanan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailTransaksiLayanan>
 */
class DetailTransaksiLayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi'   => Transaksi::inRandomOrder()->first()->id_transaksi,
            'id_layanan'     => Layanan::inRandomOrder()->first()->id_layanan,
            'jumlah_layanan' => $this->faker->numberBetween(1, 5),
        ];
    }
}
