<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Models\Pelanggan;
use App\Models\JadwalTukangCukur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $pesanan = Pesanan::with(['barber', 'pelanggan', 'jadwal'])
                         ->latest()
                         ->paginate(10);
        return view('pesanan.index', compact('pesanan'));
    }

    public function create()
    {
        // Hanya ambil tukang cukur yang sudah diverifikasi
        $barbers = TukangCukur::verified()->get();
        $pelanggans = Pelanggan::all();

        return view('pesanan.create', compact('barbers', 'pelanggans'));
    }

    // Tambahkan method untuk mendapatkan harga tukang cukur
    public function getBarberDetails($id)
    {
        $barber = TukangCukur::findOrFail($id);
        return response()->json([
            'harga' => $barber->harga
        ]);
    }

    // Tambahkan method untuk mendapatkan detail pelanggan
    public function getPelangganDetails($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        return response()->json([
            'email' => $pelanggan->email,
            'telepon' => $pelanggan->telepon,
            'alamat' => $pelanggan->alamat,
        ]);
    }

    public function getJadwal($id)
    {
        // Fetch schedules for the selected barber
        $jadwal = JadwalTukangCukur::where('tukang_cukur_id', $id)
                    ->where('tanggal', '>=', date('Y-m-d')) // Only show future schedules
                    ->orderBy('tanggal')
                    ->orderBy('jam')
                    ->get();

        return response()->json($jadwal);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barber' => 'required|exists:tukang_cukur,id',
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'jadwal_id' => 'required|exists:jadwal_tukang_cukur,id',
            'nominal' => 'required|numeric',
            'id_transaksi' => 'nullable|string',
            'alamat_lengkap' => 'required|string',
            'ongkos_kirim' => 'required|numeric',
            'email' => 'required|email',
            'telepon' => 'required|string',
        ]);

        // Cek apakah barber sudah diverifikasi
        $barber = TukangCukur::findOrFail($request->id_barber);
        if (!$barber->is_verified) {
            return redirect()->back()
                ->withErrors(['id_barber' => 'Tukang cukur belum diverifikasi.'])
                ->withInput();
        }

        // Get the selected schedule to use its date
        $jadwal = JadwalTukangCukur::findOrFail($request->jadwal_id);

        // Generate transaction ID if not provided
        $id_transaksi = $request->id_transaksi ?: 'TRX-' . Str::random(8);

        $pesanan = Pesanan::create([
            'id_barber' => $request->id_barber,
            'id_pelanggan' => $request->id_pelanggan,
            'jadwal_id' => $request->jadwal_id,
            'tgl_pesanan' => $jadwal->tanggal,
            'nominal' => $request->nominal,
            'id_transaksi' => $id_transaksi,
            'alamat_lengkap' => $request->alamat_lengkap,
            'ongkos_kirim' => $request->ongkos_kirim,
            'email' => $request->email,
            'telepon' => $request->telepon,
            'status_pembayaran' => 'pending',
        ]);

        // Redirect to payment process
        return redirect()->route('pembayaran.show', $pesanan->id)
            ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan ke pembayaran.');
    }

    public function show(Pesanan $pesanan)
    {
        return view('pesanan.show', compact('pesanan'));
    }

    public function edit(Pesanan $pesanan)
    {
        // Hanya ambil tukang cukur yang sudah diverifikasi
        $barbers = TukangCukur::verified()->get();
        $pelanggans = Pelanggan::all();

        // Get schedules for the barber
        $jadwals = JadwalTukangCukur::where('tukang_cukur_id', $pesanan->id_barber)
                    ->where('tanggal', '>=', date('Y-m-d'))
                    ->orderBy('tanggal')
                    ->orderBy('jam')
                    ->get();

        return view('pesanan.edit', compact('pesanan', 'barbers', 'pelanggans', 'jadwals'));
    }

    public function update(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'id_barber' => 'required|exists:tukang_cukur,id',
            'id_pelanggan' => 'required|exists:pelanggan,id',
            'jadwal_id' => 'required|exists:jadwal_tukang_cukur,id',
            'nominal' => 'required|numeric',
            'id_transaksi' => 'nullable|string',
            'alamat_lengkap' => 'required|string',
            'ongkos_kirim' => 'required|numeric',
            'email' => 'required|email',
            'telepon' => 'required|string',
        ]);

        // Cek apakah barber sudah diverifikasi
        $barber = TukangCukur::findOrFail($request->id_barber);
        if (!$barber->is_verified) {
            return redirect()->back()
                ->withErrors(['id_barber' => 'Tukang cukur belum diverifikasi.'])
                ->withInput();
        }

        // Get the selected schedule to use its date
        $jadwal = JadwalTukangCukur::findOrFail($request->jadwal_id);

        $pesanan->update([
            'id_barber' => $request->id_barber,
            'id_pelanggan' => $request->id_pelanggan,
            'jadwal_id' => $request->jadwal_id,
            'tgl_pesanan' => $jadwal->tanggal,
            'nominal' => $request->nominal,
            'id_transaksi' => $request->id_transaksi,
            'alamat_lengkap' => $request->alamat_lengkap,
            'ongkos_kirim' => $request->ongkos_kirim,
            'email' => $request->email,
            'telepon' => $request->telepon,
        ]);

        return redirect()->route('pesanan.index')
            ->with('success', 'Data pesanan berhasil diperbarui.');
    }

    public function destroy(Pesanan $pesanan)
    {
        $pesanan->delete();
        return redirect()->route('pesanan.index')
            ->with('success', 'Data pesanan berhasil dihapus.');
    }
}
