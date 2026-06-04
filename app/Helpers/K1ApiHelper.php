<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class K1ApiHelper
{
    // Ambil token kelompok 1
    public static function getToken(): string
    {
        if (Cache::has('k1_token')) {
            return Cache::get('k1_token');
        }

        $response = Http::post(env('K1_API_BASE_URL') . '/auth/login', [
            'email'       => env('K1_EMAIL'),
            'password'    => env('K1_PASSWORD'),
            'remember_me' => true,
        ]);

        if ($response->failed()) {
            throw new \Exception('Gagal login ke API kelompok 1');
        }

        $token = $response->json('data.token');

        Cache::put('k1_token', $token, now()->addDays(7));

        return $token;
    }

    // Ambil data pasien by id
    public static function getPasien($pasienId): array
    {
        $namaPasien = '-';
        $namaPoli   = '-';

        try {
            $token    = self::getToken();
            $response = Http::withToken($token)
                ->get(env('K1_API_BASE_URL') . '/pasien/' . $pasienId);

            // Token expired → refresh
            if ($response->status() === 401) {
                Cache::forget('k1_token');
                $token    = self::getToken();
                $response = Http::withToken($token)
                    ->get(env('K1_API_BASE_URL') . '/pasien/' . $pasienId);
            }

            if ($response->successful()) {
                $data       = $response->json();
                $namaPasien = $data['data']['nama_lengkap'] ?? '-';
                $pendaftaran = $data['data']['pendaftaran'];
                $namaPoli   = !empty($pendaftaran)
                              ? $pendaftaran[0]['unit']['nama_unit'] ?? '-'
                              : '-';
            }

        } catch (\Exception $e) {
            $namaPasien = '-';
            $namaPoli   = '-';
        }

        return [
            'nama_pasien' => $namaPasien,
            'nama_poli'   => $namaPoli,
        ];
    }
}