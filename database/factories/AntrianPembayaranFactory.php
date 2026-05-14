<?php

namespace Database\Factories;

use App\Models\AntrianPembayaran;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AntrianPembayaran>
 */
class AntrianPembayaranFactory extends Factory
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
            'no_pembayaran'  => $this->faker->unique()->numberBetween(1, 100),
            'status_antrian' => $this->faker->randomElement(['menunggu', 'dipanggil', 'selesai']),
            'waktu_masuk'    => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
