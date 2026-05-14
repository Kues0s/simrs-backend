<?php

namespace Database\Factories;

use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Dummy statis — nanti ganti setelah API kelompok 1 bisa diakses
            'pasien_id'  => $this->faker->numberBetween(1, 5),
            'id_antrian' => $this->faker->numberBetween(1, 5),
            'id_rm'      => $this->faker->numberBetween(1, 5),
            'id_resep'   => null, // nullable, kosongkan dulu
            'tanggal'    => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status'     => $this->faker->randomElement(['menunggu', 'selesai']),
        ];
    }
}
