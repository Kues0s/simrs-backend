<?php

namespace Database\Factories;

use App\Models\Pembayaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pembayaran>
 */
class PembayaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi'       => Transaksi::inRandomOrder()->first()->id_transaksi,
            'metode'             => $this->faker->randomElement(['tunai', 'transfer', 'bpjs', 'kartu_debit']),
            'jumlah_pembayaran'  => $this->faker->randomElement([100000, 150000, 200000, 250000, 300000, 500000]),
            'status_pembayaran'  => $this->faker->randomElement(['pending', 'berhasil', 'gagal']),
            'tanggal_pembayaran' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
