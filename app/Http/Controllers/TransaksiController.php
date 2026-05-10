<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $transaksi = Transaksi::all();
            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi',
                'data' => $transaksi
            ],200);
        } catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi Kesalahan',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'pasien_id' => 'required|integer',
                'id_antrian' => 'required|integer',
                'id_rm' => 'nullable|integer',
                'id_resep' => 'nullable|integer',
                'tanggal' => 'required|date',
                'status' => 'required|in:menunggu,selesai,batal',
            ]);

            // LANGKAH 2: Verifikasi NIK ke API Kelompok 1
            $cekPasien = Http::timeout(5)
                ->withToken($request->bearerToken()) // ← pakai token kasir yang login
                ->get("http://kelompok1.local/api/pasien", [
                    'pasien_id' => $validated['pasien_id']
                ]);

            if ($cekPasien->failed() || empty($cekPasien->json('data'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien_id tidak ditemukan di sistem',
                ], 404);
            }

            $dataPasien = $cekPasien->json('data');

           // LANGKAH 4: Simpan ke DB
            $transaksi = Transaksi::create([
                'pasien_id'   => $validated['pasien_id'],
                'nik'         => $validated['nik'],
                'id_antrian'  => $validated['id_antrian'],
                'id_rm'       => $validated['id_rm'] ?? null,
                'id_resep'    => $validated['id_resep'] ?? null,
                'tanggal'     => $validated['tanggal'],
                'subtotal'    => $validated['subtotal'],
                'status'      => $validated['status'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data'    => $transaksi,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa terhubung ke sistem kelompok 1',
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id_transaksi)
    {
        try{
            $transaksi = Transaksi::findOrFail($id_transaksi);
            $cekPasien = Http::timeout(5)
            ->withToken(request()->bearerToken())
            ->get("http://kelompok1.local/api/pasien", [
                'nik' => $transaksi->nik
            ]);
            $cekAntrian = Http::timeout(5)
            ->withToken(request()->bearerToken())
            ->get("http://kelompok1.local/api/antrian/{$transaksi->id_antrian}");

            if ($cekPasien->successful()) {
                $transaksi->data_pasien = $cekPasien->json('data');
            }

            if ($cekAntrian->successful()) {
                $transaksi->data_antrian = $cekAntrian->json('data');
            }

            return response()->json([
                'success' => true,
                'data'    => $transaksi,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id_transaksi)
    {
        try {
            $transaksi = Transaksi::findOrFail($id_transaksi);

            // Cek apakah transaksi boleh diupdate
            if ($transaksi->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah selesai tidak bisa diupdate',
                ], 422);
            }

            $validated = $request->validate([
                'id_rm'       => 'sometimes|nullable|integer',
                'id_resep'    => 'sometimes|nullable|integer',
                'tanggal'     => 'sometimes|date',
                'subtotal'    => 'sometimes|numeric|min:0',
                'diskon'      => 'sometimes|nullable|numeric|min:0',
                'pajak'       => 'sometimes|nullable|numeric|min:0',
                'total_akhir' => 'sometimes|numeric|min:0',
                'status'      => 'sometimes|in:menunggu,selesai,batal',
            ]);

            // Jika id_rm diupdate, verifikasi ke API kelompok 2
            if (!empty($validated['id_rm'])) {
                $cekRm = Http::timeout(5)
                    ->withToken($request->bearerToken())
                    ->get("http://kelompok2.local/api/rekam-medik/{$validated['id_rm']}");

                if ($cekRm->failed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rekam medis tidak ditemukan',
                    ], 404);
                }
            }

            // Jika id_resep diupdate, verifikasi ke API kelompok 2
            if (!empty($validated['id_resep'])) {
                $cekResep = Http::timeout(5)
                    ->withToken($request->bearerToken())
                    ->get("http://kelompok2.local/api/e-resep/{$validated['id_resep']}");

                if ($cekResep->failed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resep tidak ditemukan',
                    ], 404);
                }
            }

            $transaksi->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data'    => $transaksi,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa terhubung ke sistem kelompok lain',
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaksi $transaksi)
    {
        try {
            $transaksi = Transaksi::findOrFail($id);
            
            // Cek apakah transaksi boleh dihapus
            // Hanya transaksi dengan status 'menunggu' yang boleh dihapus
            if ($transaksi->status !== 'menunggu') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi yang sudah selesai atau batal tidak bisa dihapus',
                ], 422);
            }
            $transaksi->delete();
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
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
