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
            $data = DetailTransaksiLayanan::orderBy('id_detail_layanan', 'desc')->get();
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DetailTransaksiLayanan $detailTransaksiLayanan)
    {
        //
    }
}
