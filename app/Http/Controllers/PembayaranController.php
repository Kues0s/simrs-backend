<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PembayaranController extends Controller
{
    /**
     * Menampilkan List Pembayaran
     */
    public function index()
    {
        $pembayaran = Pembayaran::with('transaksi')->get();
        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diambil',
            'data' => $pembayaran,
        ], 200);
    }


    /**
     * Menambahkan Data Pembayaran 
     */
   public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_transaksi'      => 'required|integer|exists:transaksi,id_transaksi',
                'metode'            => 'required|in:cash,qris,debit',
                'total_tagihan'     => 'required|numeric|min:0', 
                'jumlah_pembayaran' => 'required|numeric|min:0',
            ]);

            // Cek transaksi
            $transaksi = Transaksi::findOrFail($validated['id_transaksi']);

            if ($transaksi->status === 'selesai') {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi ini sudah dibayar',
                ], 400);
            }

            // Cek jumlah pembayaran cukup
            if ($validated['jumlah_pembayaran'] < $validated['total_tagihan']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang dari total tagihan',
                    'data'    => [
                        'total_tagihan'     => $validated['total_tagihan'],
                        'jumlah_pembayaran' => $validated['jumlah_pembayaran'],
                        'kurang'            => $validated['total_tagihan'] - $validated['jumlah_pembayaran'],
                    ]
                ], 400);
            }

            // Hitung kembalian
            $kembalian = $validated['jumlah_pembayaran'] - $validated['total_tagihan']; 

            DB::beginTransaction();

            // 1. Simpan pembayaran
            $pembayaran = Pembayaran::create([
                'id_transaksi'       => $validated['id_transaksi'],
                'metode'             => $validated['metode'],
                'jumlah_pembayaran'  => $validated['jumlah_pembayaran'],
                'status_pembayaran'  => 'berhasil',
                'tanggal_pembayaran' => Carbon::now()->toDateTimeString(),
            ]);

            // 2. Update status transaksi → selesai
            $transaksi->update(['status' => 'selesai']);

            // 3. Hit API kelompok 1 → update status antrian
            if ($transaksi->id_antrian) {
                $antriResponse = Http::put(env('K1_API_BASE_URL') . '/antrian/' . $transaksi->id_antrian . '/status',['status' => 'lunas'] 
                );

                if ($antriResponse->failed()) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran gagal karena tidak dapat mengupdate status antrian',
                        'error'   => $antriResponse->body(),
                    ], 500);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data'    => [
                    'pembayaran' => [
                        'id_pembayaran'      => $pembayaran->id_pembayaran,
                        'id_transaksi'       => $pembayaran->id_transaksi,
                        'metode'             => $pembayaran->metode,
                        'total_tagihan'      => $validated['total_tagihan'], 
                        'jumlah_pembayaran'  => $pembayaran->jumlah_pembayaran,
                        'kembalian'          => $kembalian,
                        'status_pembayaran'  => $pembayaran->status_pembayaran,
                        'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                    ],
                    'transaksi'  => [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'status'       => 'selesai',
                    ],
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Menampilkan Data Pembayaran Tertentu
     */
   public function show(String $id_pembayaran)
    {
        try {
            $pembayaran = Pembayaran::with('transaksi')->findOrFail($id_pembayaran);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diambil',
                'data'    => $pembayaran,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
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
     * Update Data Pembayaran
     */
    public function update(Request $request, String $id_pembayaran)
    {
        try {
            $validated = $request->validate([
                'metode'            => 'sometimes|required|in:cash,qris,debit',
                'jumlah_pembayaran' => 'sometimes|required|numeric|min:0',
                'status_pembayaran' => 'sometimes|required|in:pending,berhasil,gagal',
            ]);

            $pembayaran = Pembayaran::findOrFail($id_pembayaran);

            // Cek pembayaran sudah berhasil
            if ($pembayaran->status_pembayaran === 'berhasil') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah berhasil tidak bisa diupdate',
                ], 400);
            }

            $pembayaran->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil diupdate',
                'data'    => $pembayaran,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menghapus Data Pembayaran
     */
    public function destroy(String $id_pembayaran)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id_pembayaran);

            // Cek pembayaran sudah berhasil
            if ($pembayaran->status_pembayaran === 'berhasil') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran yang sudah berhasil tidak bisa dihapus',
                ], 400);
            }

            $pembayaran->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data pembayaran berhasil dihapus',
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
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
     * Statistik Laporan Pembayaran Harian, Bulanan, Tahunan
     */
    public function statistik(Request $request)
    {
        try {
            $bulan = $request->query('bulan', Carbon::now()->month);
            $tahun = $request->query('tahun', Carbon::now()->year);

            // LAPORAN HARIAN
            $harian = Pembayaran::with('transaksi')
                ->whereDate('tanggal_pembayaran', Carbon::today())
                ->where('status_pembayaran', 'berhasil')
                ->get();

           
            // LAPORAN BULANAN
            $bulanan = Pembayaran::with('transaksi')
                ->whereMonth('tanggal_pembayaran', $bulan)
                ->whereYear('tanggal_pembayaran', $tahun)
                ->where('status_pembayaran', 'berhasil')
                ->get();

            // LAPORAN TAHUNAN
            $tahunan = Pembayaran::with('transaksi')
                ->whereYear('tanggal_pembayaran', $tahun)
                ->where('status_pembayaran', 'berhasil')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistik laporan berhasil diambil',
                'data'    => [
                    'harian'  => [
                        'jumlah_transaksi'  => $harian->count(),
                        'total_pendapatan'  => $harian->sum('jumlah_pembayaran'),
                    ],
                    'bulanan' => [
                        'bulan'             => $bulan,
                        'tahun'             => $tahun,
                        'jumlah_transaksi'  => $bulanan->count(),
                        'total_pendapatan'  => $bulanan->sum('jumlah_pembayaran'),
                    ],
                    'tahunan' => [
                        'tahun'            => $tahun,
                        'jumlah_transaksi' => $tahunan->count(),
                        'total_pendapatan' => $tahunan->sum('jumlah_pembayaran'),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Laporan Transaksi Berdasarkan Rentang Tanggal
     */
    public function laporan(Request $request)
    {
        try {
            $validated = $request->validate([
                'tanggal_mulai' => 'required|date',
                'tanggal_akhir' => 'required|date|after_or_equal:tanggal_mulai',
            ]);

            // Ambil semua pembayaran berhasil dalam rentang tanggal
            $pembayaran = Pembayaran::with('transaksi')
                ->whereDate('tanggal_pembayaran', '>=', $validated['tanggal_mulai'])
                ->whereDate('tanggal_pembayaran', '<=', $validated['tanggal_akhir'])
                ->where('status_pembayaran', 'berhasil')
                ->get();

            // Total transaksi
            $totalTransaksi = $pembayaran->count();

            // Pendapatan layanan → biaya dokter + perawat per transaksi
            // dari API kelompok 2
            $pendapatanLayanan = 0;
            $pendapatanObat    = 0;

            foreach ($pembayaran as $item) {
                // Hit API kelompok 2 → biaya dokter
                $dokterResponse = Http::get(env('K2_API_BASE_URL') . '/dokter/' . $item->transaksi->id_dokter);
                if ($dokterResponse->successful()) {
                    $pendapatanLayanan += $dokterResponse->json('data.biaya_layanan');
                }

                // Hit API kelompok 2 → biaya perawat
                $perawatResponse = Http::get(env('K2_API_BASE_URL') . '/perawat/' . $item->transaksi->id_perawat);
                if ($perawatResponse->successful()) {
                    $pendapatanLayanan += $perawatResponse->json('data.biaya_layanan');
                }

                // Hit API kelompok 4 → total obat
                if ($item->transaksi->id_resep) {
                    $resepResponse = Http::get(env('K4_API_BASE_URL') . '/e-resep/' . $item->transaksi->id_resep);
                    if ($resepResponse->successful()) {
                        $resepData   = $resepResponse->json();
                        $detailResep = $resepData[0]['detail_resep'] ?? [];

                        foreach ($detailResep as $obat) {
                            $pendapatanObat += $obat['JUMLAH'] * $obat['obat']['HARGA_JUAL'];
                        }
                    }
                }
            }

            $totalPendapatan = $pendapatanLayanan + $pendapatanObat;

            return response()->json([
                'success' => true,
                'message' => 'Laporan transaksi berhasil diambil',
                'data'    => [
                    'periode' => [
                        'tanggal_mulai' => $validated['tanggal_mulai'],
                        'tanggal_akhir' => $validated['tanggal_akhir'],
                    ],
                    'ringkasan' => [
                        'total_transaksi'   => $totalTransaksi,
                        'pendapatan_obat'   => $pendapatanObat,
                        'pendapatan_layanan'=> $pendapatanLayanan,
                        'total_pendapatan'  => $totalPendapatan,
                    ],
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
