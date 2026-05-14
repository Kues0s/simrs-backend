<?php

namespace Database\Factories;

use App\Models\DetailTransaksiObat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailTransaksiObat>
 */
class DetailTransaksiObatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi' => Transaksi::inRandomOrder()->first()->id_transaksi,
            'id_obat'      => $this->faker->numberBetween(1, 5), // dummy statis
            'jumlah_obat'  => $this->faker->numberBetween(1, 10),
        ];
    }
}
