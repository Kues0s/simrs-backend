<?php

use App\Http\Controllers\AntrianPembayaranController;
use App\Http\Controllers\DetailTransaksiLayananController;
use App\Http\Controllers\DetailTransaksiObatController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


//API Transaksi
Route::get('/transaksi/jumlah-transaksi', [TransaksiController::class, 'jumlahTransaksi']);
Route::get('/transaksi', [TransaksiController::class, 'index']);
Route::get('/transaksi/{id_transaksi}', [TransaksiController::class, 'show']);
Route::post('/transaksi', [TransaksiController::class, 'store']);
Route::put('/transaksi/{id_transaksi}', [TransaksiController::class, 'update']);
Route::delete('/transaksi/{id_transaksi}', [TransaksiController::class, 'destroy']);

//API Layanan
Route::get('/layanan', [LayananController::class, 'index']);
Route::post('/layanan', [LayananController::class, 'store']);
Route::get('/layanan/{id_layanan}', [LayananController::class, 'show']);
Route::put('/layanan/{id_layanan}', [LayananController::class, 'update']);
Route::delete('/layanan/{id_layanan}', [LayananController::class, 'destroy']);

//API Pembayaran
Route::get('/pembayaran', [PembayaranController::class, 'index']);
Route::post('/pembayaran', [PembayaranController::class, 'store']);
Route::get('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'show']);
Route::put('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'update']);
Route::delete('/pembayaran/{id_pembayaran}', [PembayaranController::class, 'destroy']);

//API Antrian_Pembayaran
Route::get('/antrian_pembayaran', [AntrianPembayaranController::class, 'index']);
Route::post('/antrian_pembayaran', [AntrianPembayaranController::class, 'store']);
Route::get('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'show']);
Route::put('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'update']);
Route::delete('/antrian_pembayaran/{id_antrian_pay}', [AntrianPembayaranController::class, 'destroy']);

//API Detail_Transaksi_Layanan
Route::get('/transaksi_layanan', [DetailTransaksiLayananController::class, 'index']);
Route::post('/transaksi_layanan', [DetailTransaksiLayananController::class, 'store']);
Route::get('/transaksi_layanan/{id_detail_layanan}', [DetailTransaksiLayananController::class, 'show']);
Route::put('/transaksi_layanan/{id_detail_layanan}', [DetailTransaksiLayananController::class, 'update']);
Route::delete('/transaksi_layanan/{id_detail_layanan}', [DetailTransaksiLayananController::class, 'destroy']);


//API Detail_Transaksi_Obat
Route::get('/transaksi_obat', [DetailTransaksiObatController::class, 'index']);
Route::post('/transaksi_obat', [DetailTransaksiObatController::class, 'store']);
Route::get('/transaksi_obat/{id_detail_obat}', [DetailTransaksiObatController::class, 'show']);
Route::put('/transaksi_obat/{id_detail_obat}', [DetailTransaksiObatController::class, 'update']);
Route::delete('/transaksi_obat/{id_detail_obat}', [DetailTransaksiObatController::class, 'destroy']);