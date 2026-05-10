<?php

namespace App\Http\Controllers;

use App\Models\DetailTransaksiObat;
use Illuminate\Http\Request;

class DetailTransaksiObatController extends Controller
{
    /**
     * Menampilkan LIst Transaksi Obat
     */
    public function index()
    {
        $data = DetailTransaksiObat::orderBy('id_detail_obat', 'desc')->get();
        return response()->json([
            'succes' => true,
            'message' => 'Data Transkasi Obat berhasil ditemukan',
            'data' => $data,
        ], 200);
    }

    /**
     * Menamabhkan data transaksi obat.
     */
    public function store(Request $request)
    {
        $validateData = $request->validate([
            'id_obat' => 'required|integer',
            'id_transaksi' => 'required|integer',
            'jumlah_obat' => 'required|integer',
        ]);

        $data = DetailTransaksiObat::create($validateData);

        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil ditambahkan',
            'data' => $data,
        ],201);

    }

    /**
     * Menampilkan Data Transaksi Obat tertentu.
     */
    public function show(DetailTransaksiObat $detailTransaksiObat)
    {
        $data = DetailTransaksiObat::find($detailTransaksiObat);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan', 
            ],404);
        }

        return response()->json([
            'succes' => true,
            'message' => 'Data ditemukan',
            'data' => $data,
        ],200);
    }

    /**
     * Update data Transaksi_layanan.
     */
    public function update(Request $request, DetailTransaksiObat $detailTransaksiObat)
    {
        $data = DetailTransaksiObat::find($detailTransaksiObat);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan!',
            ],404);
        }

        $validateData = $request->validate([
            'id_obat' => 'required|integer',
            'id_transaksi' => 'required|integer',
            'jumlah_obat' => 'required|integer',
        ]);

        $data->update($validateData);

        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil diperbaharui',
            'data' => $data,
        ]);
    }

    /**
     * Menghapus Data Transaksai Obat.
     */
    public function destroy(DetailTransaksiObat $detailTransaksiObat)
    {
        $data = DetailTransaksiObat::find($detailTransaksiObat);
        if(!$data){
            return response()->json([
                'succes' => false,
                'message' => 'Data tidak ditemukan!',
            ],404);
        }
        $data->delete();
        return response()->json([
            'succes' => true,
            'message' => 'Data berhasil dihapus!',
            'data' => $data,
        ]);
    }
}
