<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiLayanan;
use Illuminate\Http\Request;

class DetailTransaksiLayananController extends Controller
{
    /**
     * Menampilkan List Detail Transaksi Layanan.
     */
    public function index()
    {
        try{
            //Query Menampilkan Data berdasar kan id_detail_layanan terbesar
            $data = DetailTransaksiLayanan::orderBy('id_detail_layanan', 'desc')->get();

            //Return Response dalam bentuk json
            return response()->json([
                'succes' => true,
                'message' => 'Data transaksi layanan berhasil dimuat',
                'data' => $data,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Terdapat Kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menambahkan Data Transakasi Layanan 
     */
    public function store(Request $request)
    {
        //Validasi Data
        $validateData = $request->validate([
            'id_layanan' => 'required|integer',
            'id_transaksi' => 'required|integer',
            'jumlah_layanan' => 'required|integer',
        ]);

        //Simpan Data
        $data = DetailTransaksiLayanan::create($validateData);

        //return response dalam bentuk json
        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil disimpan',
            'data' => $data,
        ],201);
    }

    /**
     * Menampilkan Detail_transaksi_layanan tertentu
     */
    public function show(DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        //Cari data berdasarkan id
        $data = DetailTransaksiLayanan::find($detailTransaksiLayanan);

        //Jika tidak ada data
        if(!$data){
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
        ], 404);
        }

        //jika data ada
        return response()->json([
            'succes' => true,
            'message' => 'Berhasil memuat data berdasarkan id',
            'data' => $data,
        ], 200);
    }

    /**
     * Update Data_transaksi_layanan
     */
    public function update(Request $request, DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        // 1. Mencari data dulu berdasarkan ID
        $data = DetailTransaksiLayanan::find($detailTransaksiLayanan);

        // jika data tidak ada
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        // 2. Validasi data yang di request
        $validatedData = $request->validate([
            'id_layanan'     => 'required|integer',
            'id_transaksi'   => 'required|integer',
            'jumlah_layanan' => 'required|integer',
        ]);

        // 3. Update data ke database
        $data->update($validatedData);

        // 4. Kembalikan response sukses
        return response()->json([
            'success' => true,
            'message' => 'Data detail transaksi berhasil diupdate',
            'data'    => $data
        ]);
    }

    /**
     * Menghapus Berdasarkan id_detail_layanan
     */
    public function destroy(DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        $data = DetailTransaksiLayanan::find($detailTransaksiLayanan);

        if(!data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan',
            ],404);
        }

        $data->delete();
        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil dihapus',
        ],);
    }
}
