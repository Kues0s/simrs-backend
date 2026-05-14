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
        $layanan = [
            'Konsultasi Umum',
            'Konsultasi Spesialis',
            'Rawat Inap',
            'Rawat Jalan',
            'Tindakan Medis',
            'Laboratorium',
            'Radiologi',
            'Fisioterapi',
        ];

        return [
            'nama_layanan'   => $this->faker->unique()->randomElement($layanan),
            'tarif_dokter'   => $this->faker->randomElement([50000, 75000, 100000, 150000, 200000]),
            'tarif_perawat'  => $this->faker->randomElement([25000, 30000, 40000, 50000]),
            'status_layanan' => $this->faker->randomElement(['aktif', 'nonaktif']),
        ];
    }
}
