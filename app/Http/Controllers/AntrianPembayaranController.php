<?php

namespace App\Http\Controllers;

use App\Helpers\K1ApiHelper;
use App\Models\AntrianPembayaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class AntrianPembayaranController extends Controller
{
    /**
     * Menampilkan Seluruh Antrian.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $antrian = AntrianPembayaran::with('transaksi')
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->orderBy('no_pembayaran', 'asc')
            ->get();

       $data = $antrian->map(function ($item) {
            $namaPasien = '-';
            $poli       = '-';

            if ($item->transaksi && $item->transaksi->pasien_id) {
                $pasien     = K1ApiHelper::getPasien($item->transaksi->pasien_id);
                $namaPasien = $pasien['nama_pasien'];
                $poli   = $pasien['nama_poli'];
            }

            return [
                'id_antrian_pay' => $item->id_antrian_pay,
                'no_pembayaran'  => $item->no_pembayaran,
                'status_antrian' => $item->status_antrian,
                'waktu_masuk'    => $item->waktu_masuk->format('Y-m-d H:i:s'),
                'nama_pasien'    => $namaPasien,
                'poli'           => $poli,
                'id_transaksi'   => $item->id_transaksi,
                'id_antrian'   => $item->transaksi->id_antrian ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar antrian pembayaran',
            'data'    => $data,
        ], 200);
    }

    /**
     * Menampilkan Antrian Skip (tidak hadir)
     */
    public function indexSkip(): \Illuminate\Http\JsonResponse
    {
        $antrian = AntrianPembayaran::with('transaksi')
            ->where('status_antrian', 'tidak_hadir')
            ->orderBy('no_pembayaran', 'asc')
            ->get();

       $data = $antrian->map(function ($item) {
            $namaPasien = '-';
            $namaPoli   = '-';

            if ($item->transaksi && $item->transaksi->pasien_id) {
                $pasien     = K1ApiHelper::getPasien($item->transaksi->pasien_id);
                $namaPasien = $pasien['nama_pasien'];
                $namaPoli   = $pasien['nama_poli'];
            }
            return [
                'id_antrian_pay' => $item->id_antrian_pay,
                'no_pembayaran'  => $item->no_pembayaran,
                'status_antrian' => $item->status_antrian,
                'nama_pasien'    => $namaPasien,
                'nama_poli'      => $namaPoli,
                'waktu_masuk'    => $item->waktu_masuk->format('Y-m-d H:i:s'),
                'id_antrian'   => $item->transaksi->id_antrian ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar antrian skip',
            'data'    => $data,
        ], 200);
    }

    /**
     * Statistik Antrian Pembayaran.
     */
    public function statistik(): \Illuminate\Http\JsonResponse
    {
        $total = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->count();
        $menunggu = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->where('status_antrian', 'menunggu')->count();
        $dipanggil  = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->where('status_antrian', 'dipanggil')->count();
        $selesai    = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->where('status_antrian', 'selesai')->count();
        $tidakHadir = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->where('status_antrian', 'tidak_hadir')->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik antrian',
            'data'    => [
                'total_antrian'   => $total,
                'menunggu'        => $menunggu,
                'sedang_dilayani' => $dipanggil,
                'selesai'         => $selesai,
                'tidak_hadir'     => $tidakHadir,
            ],
        ], 200);
    }

    /**
     * Memanggil Antrian (update status menjadi dipanggil).
     */
    public function panggil($id): \Illuminate\Http\JsonResponse
    {
        try {
            $antrian = AntrianPembayaran::findOrFail($id);

            // Cek status
            if ($antrian->status_antrian === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Antrian sudah selesai',
                ], 400);
            }

            $antrian->update(['status_antrian' => 'dipanggil']);

            return response()->json([
                'success' => true,
                'message' => 'Pasien berhasil dipanggil',
                'data'    => $antrian,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data antrian tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Skip Antrian (tidak hadir).
     */
    public function skip($id): \Illuminate\Http\JsonResponse
    {
        try {
            $antrian = AntrianPembayaran::findOrFail($id);

            // Cek status
            if ($antrian->status_antrian === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Antrian yang sudah selesai tidak bisa di-skip',
                ], 400);
            }

            $antrian->update(['status_antrian' => 'tidak_hadir']);

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil di-skip',
                'data'    => $antrian,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data antrian tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan Data Antrian Pembayaran Tertentu Berdasarkan ID Antrian Pay.
     */
    public function showById($id): \Illuminate\Http\JsonResponse
    {
        try{
            $antrian = AntrianPembayaran::with('transaksi')->findOrFail($id);

            $namaPasien = '-';
            $poli       = '-';

            if ($antrian->transaksi && $antrian->transaksi->pasien_id) {
                $pasien     = K1ApiHelper::getPasien($antrian->transaksi->pasien_id);
                $namaPasien = $pasien['nama_pasien'];
                $poli   = $pasien['nama_poli'];
            }
            return response()->json([
                'success' => true,
                'message' => 'Data antrian',
                'data'    => [
                    'nama_pasien' => $namaPasien,
                    'poli' => $poli,
                    'id_transaksi' => $antrian->transaksi->id_transaksi,
                    'id_antrian' => $antrian->transaksi->id_antrian,
                    'antrian' => $antrian,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data antrian tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan Data Antrian Pembayaran yang sedang Dilayani.
     */
    public function sedangDilayani(): \Illuminate\Http\JsonResponse
    {
        // Cek apakah ada yang sedang dipanggil
        $antrian = AntrianPembayaran::with('transaksi')
            ->where('status_antrian', 'dipanggil')
            ->orderBy('no_pembayaran', 'asc')
            ->first();

        // Jika tidak ada yang dipanggil → ambil yang menunggu pertama
        if (!$antrian) {
            $antrian = AntrianPembayaran::with('transaksi')
                ->where('status_antrian', 'menunggu')
                ->orderBy('no_pembayaran', 'asc')
                ->first();
        }

        // Jika tidak ada sama sekali
        if (!$antrian) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada antrian',
                'data'    => null,
            ], 200);
        }

        $namaPasien = '-';
        $poli       = '-';

        if ($antrian->transaksi && $antrian->transaksi->pasien_id) {
            $pasien     = K1ApiHelper::getPasien($antrian->transaksi->pasien_id);
            $namaPasien = $pasien['nama_pasien'];
            $poli       = $pasien['nama_poli'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Antrian sedang dilayani',
            'data'    => [
                'id_antrian_pay' => $antrian->id_antrian_pay,
                'no_pembayaran'  => $antrian->no_pembayaran,
                'status_antrian' => $antrian->status_antrian,
                'waktu_masuk'    => $antrian->waktu_masuk->format('Y-m-d H:i:s'),
                'nama_pasien'    => $namaPasien,
                'poli'           => $poli,
                'id_transaksi'   => $antrian->transaksi->id_transaksi ?? null,
                'id_antrian'     => $antrian->transaksi->id_antrian ?? null,
            ],
        ], 200);
    }
}
