<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tiket - Workshop</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body>
    @php
        $currentUser = auth()->user();
        $userName = $currentUser ? $currentUser->name : 'Cannava';
        $currentRole = $currentUser ? $currentUser->role : session('demo_role', 'admin');
    @endphp

    <div class="kotak kotak-lebar">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #dee2e6;">
            <h3 style="margin: 0;">Halo, {{ $userName }} <span style="font-weight: 400; font-size: 14px; color: #6c757d;">({{ $currentRole }})</span></h3>
            
            @if($currentUser && Route::has('logout'))
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Keluar</button>
                </form>
            @else
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 13px; color: #6c757d;">Mode:</span>
                    <a href="{{ route('switch.role', 'admin') }}" class="btn {{ $currentRole === 'admin' ? 'btn-primary' : 'btn-outline-dark' }}" style="padding: 6px 12px; font-size: 13px;">Admin</a>
                    <a href="{{ route('switch.role', 'user') }}" class="btn {{ $currentRole === 'user' ? 'btn-primary' : 'btn-outline-dark' }}" style="padding: 6px 12px; font-size: 13px;">User</a>
                </div>
            @endif
        </div>

        <h2 style="margin-bottom: 20px;">Daftar Tiket Tersedia</h2>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if($currentRole === 'admin')
            <div style="margin-bottom: 20px; display: flex; gap: 10px;">
                <a href="{{ route('tiket.create') }}" class="btn btn-outline-dark">+ Tambah Tiket</a>
                @if(Route::has('admin.create'))
                    <a href="{{ route('admin.create') }}" class="btn btn-outline-dark">+ Tambah Admin</a>
                @endif
                <a href="{{ route('admin.laporan') }}" class="btn btn-outline-dark">Data Penjualan</a>
            </div>
        @endif

        @if($currentRole === 'user')
            <div style="margin-bottom: 20px;">
                <a href="{{ route('tiket.saya') }}" class="btn btn-outline-dark">Lihat Tiket Saya</a>
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Tipe Tiket</th>
                    <th>Harga</th>
                    <th>Sisa Kuota</th>
                    <th>Benefit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tikets as $row)
                <tr>
                    <td><strong>{{ $row->tipe_tiket }}</strong></td>
                    <td>Rp {{ number_format($row->harga, 0, ',', '.') }}</td>
                    <td>{{ $row->kuota }}</td>
                    <td>{{ $row->benefit }}</td>
                    <td>
                        @if($currentRole === 'admin')
                            <div class="aksi-flex">
                                <a href="{{ route('tiket.edit', $row->id_tiket) }}" class="btn btn-secondary">Edit</a>
                                <form action="{{ route('tiket.destroy', $row->id_tiket) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus tiket ini?')">Hapus</button>
                                </form>
                            </div>
                        @else
                            @if($row->kuota > 0)
                                <a href="{{ route('tiket.form_beli', $row->id_tiket) }}" class="btn btn-primary">Beli Tiket</a>
                            @else
                                <span style="color: #adb5bd; font-size: 14px;">Habis Terjual</span>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d;">Belum ada tiket tersedia. Silakan klik "+ Tambah Tiket" pada mode Admin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>