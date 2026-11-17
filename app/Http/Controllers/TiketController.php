<?php

namespace App\Http\Controllers;

use App\Models\Tiket;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TiketController extends Controller
{
    /**
     * Mengambil ID user yang sedang login, atau membuat/memperbarui 1 akun lokal
     * agar transaksi tetap berjalan tanpa error saat modul dijalankan tanpa login.
     */
    private function getCurrentUserId()
    {
        if (auth()->check()) {
            return auth()->id();
        }

        $demoUser = User::updateOrCreate(
            ['email' => 'demo@workshop.test'],
            [
                'name' => 'Cannava',
                'password' => Hash::make('password123'),
            ]
        );

        return $demoUser->id;
    }

    public function index()
    {
        $tikets = Tiket::orderBy('id_tiket', 'desc')->get();
        return view('daftar_tiket', compact('tikets'));
    }

    public function create()
    {
        return view('form_tiket');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_tiket' => 'required',
            'harga' => 'required|numeric',
            'kuota' => 'required|numeric',
            'benefit' => 'required'
        ]);

        Tiket::create($request->all());
        return redirect()->route('tiket.index')->with('success', 'Tiket berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tiket = Tiket::findOrFail($id);
        return view('form_tiket', compact('tiket'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipe_tiket' => 'required',
            'harga' => 'required|numeric',
            'kuota' => 'required|numeric',
            'benefit' => 'required'
        ]);

        $tiket = Tiket::findOrFail($id);
        $tiket->update($request->all());
        return redirect()->route('tiket.index')->with('success', 'Tiket berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tiket = Tiket::findOrFail($id);
        $tiket->delete();
        return redirect()->route('tiket.index')->with('success', 'Tiket berhasil dihapus.');
    }

    // --- HALAMAN KONFIRMASI BELI ---
    public function formBeli($id)
    {
        $tiket = Tiket::findOrFail($id);
        return view('beli_tiket', compact('tiket'));
    }

    public function beli(Request $request, $id)
    {
        $request->validate([
            'jumlah_beli' => 'required|integer|min:1'
        ]);

        $tiket = Tiket::findOrFail($id);
        $jumlah = $request->jumlah_beli;

        if ($tiket->kuota < $jumlah) {
            return back()->with('error', 'Maaf, sisa kuota tiket tidak mencukupi! Sisa: ' . $tiket->kuota);
        }

        $total_harga = $tiket->harga * $jumlah;
        $userId = $this->getCurrentUserId();

        Transaksi::create([
            'user_id' => $userId,
            'tiket_id' => $tiket->id_tiket,
            'jumlah_beli' => $jumlah,
            'total_harga' => $total_harga
        ]);

        $tiket->decrement('kuota', $jumlah);

        return redirect()->route('tiket.saya')->with('success', 'Berhasil membeli ' . $jumlah . ' tiket!');
    }

    public function tiketSaya()
    {
        $userId = $this->getCurrentUserId();
        $transaksis = Transaksi::where('user_id', $userId)->with('tiket')->get();
        return view('tiket_saya', compact('transaksis'));
    }

    public function batalkan($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $userId = $this->getCurrentUserId();

        if ($transaksi->user_id !== $userId) {
            return back()->with('error', 'Akses ditolak.');
        }

        $tiket = Tiket::findOrFail($transaksi->tiket_id);
        $tiket->increment('kuota', $transaksi->jumlah_beli);
        $transaksi->delete();

        return redirect()->route('tiket.saya')->with('success', 'Tiket berhasil dibatalkan. Kuota telah dikembalikan.');
    }

    public function laporanPenjualan()
    {
        $transaksis = Transaksi::with(['user', 'tiket'])->orderBy('created_at', 'desc')->get();
        return view('laporan_penjualan', compact('transaksis'));
    }
}