<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TiketController;

// Langsung buka halaman daftar tiket tanpa login
Route::get('/', function () {
    return redirect()->route('tiket.index');
});

// Fitur ganti mode sementara (Admin / User) sebelum digabung dengan modul Login teman
Route::get('/switch-role/{role}', function ($role) {
    if (in_array($role, ['admin', 'user'])) {
        session(['demo_role' => $role]);
    }
    return redirect()->route('tiket.index');
})->name('switch.role');

// Rute Tiket (Terbuka tanpa middleware auth/admin sementara)
Route::get('/tiket', [TiketController::class, 'index'])->name('tiket.index');

// Rute Pembelian & Tiket Saya (User)
Route::get('/tiket/beli/{id}', [TiketController::class, 'formBeli'])->name('tiket.form_beli');
Route::post('/tiket/beli/{id}', [TiketController::class, 'beli'])->name('tiket.beli');
Route::get('/tiket-saya', [TiketController::class, 'tiketSaya'])->name('tiket.saya');
Route::delete('/tiket/batal/{id}', [TiketController::class, 'batalkan'])->name('tiket.batal');

// Rute Kelola Tiket & Laporan (Admin)
Route::get('/tiket/create', [TiketController::class, 'create'])->name('tiket.create');
Route::post('/tiket/store', [TiketController::class, 'store'])->name('tiket.store');
Route::get('/tiket/{id}/edit', [TiketController::class, 'edit'])->name('tiket.edit');
Route::put('/tiket/{id}', [TiketController::class, 'update'])->name('tiket.update');
Route::delete('/tiket/{id}', [TiketController::class, 'destroy'])->name('tiket.destroy');
Route::get('/admin/laporan', [TiketController::class, 'laporanPenjualan'])->name('admin.laporan');