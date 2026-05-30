<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'success' => true,
            'message' => 'Daftar antrian pembayaran',
            'data'    => $antrian,
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

        return response()->json([
            'success' => true,
            'message' => 'Daftar antrian skip',
            'data'    => $antrian,
        ], 200);
    }

    /**
     * Statistik Antrian Pembayaran.
     */
    public function statistik(): \Illuminate\Http\JsonResponse
    {
        $total      = AntrianPembayaran::whereDate('waktu_masuk', Carbon::today())->count();
        $menunggu   = AntrianPembayaran::where('status_antrian', 'menunggu')->count();
        $dipanggil  = AntrianPembayaran::where('status_antrian', 'dipanggil')->count();
        $selesai    = AntrianPembayaran::where('status_antrian', 'selesai')->count();
        $tidakHadir = AntrianPembayaran::where('status_antrian', 'tidak_hadir')->count();

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

}
