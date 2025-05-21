<?php

namespace App\Http\Controllers;

use App\Models\LaporanPenggajian;
use App\Models\TukangCukur;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanPenggajianController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
    $query = LaporanPenggajian::with(['barber', 'pesanan'])->latest();

    if ($request->filled('bulan')) {
        try {
            $tanggal = \Carbon\Carbon::createFromFormat('Y-m', $request->bulan);
            $query->whereMonth('created_at', $tanggal->format('m'))
                  ->whereYear('created_at', $tanggal->format('Y'));
        } catch (\Exception $e) {
            return redirect()->route('laporan_penggajian.index')->with('error', 'Format bulan tidak valid.');
        }
    }
        $laporanPenggajian = LaporanPenggajian::with(['tukang_cukur', 'pesanan'])->latest()->paginate(10);
        
            // Inisialisasi total
            $totalPendapatan = 0;
            $totalKomisi = 0;
            $totalGaji = 0;
        
            foreach ($laporanPenggajian as $laporan) {
                $jumlahPotong = $laporan->jumlah_potong ?? 0;
                $tarif = $laporan->tarif_per_potong ?? 0;
        
                $pendapatan = $jumlahPotong * $tarif;
                $komisi = $pendapatan * 0.02;
                $gaji = $pendapatan - $komisi;
        
                $totalPendapatan += $pendapatan;
                $totalKomisi += $komisi;
                $totalGaji += $gaji;
            }
        
            $total = [
                'pendapatan' => $totalPendapatan,
                'komisi' => $totalKomisi,
                'gaji' => $totalGaji,
            ];
        
            return view('laporan_penggajian.index', compact('laporanPenggajian', 'total'));        
    }

    public function create()
    {
        // Mengambil data tukang cukur dan pesanan untuk dropdown
        $barbers = TukangCukur::all();
        $pesanans = Pesanan::all();
        return view('laporan_penggajian.create', compact('barbers', 'pesanans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barber' => 'required|string|exists:tukang_cukur,id',
            'id_pesanan' => 'required|string|exists:pesanan,id',
            'status' => 'required|string',
        ]);

        LaporanPenggajian::create([
            'id_barber' => $request->id_barber,
            'id_pesanan' => $request->id_pesanan,
            'status' => $request->status,
        ]);

        return redirect()->route('laporan_penggajian.index')
            ->with('success', 'Data laporan penggajian berhasil dibuat.');
    }

    public function show(LaporanPenggajian $laporanPenggajian)
    {
        return view('laporan_penggajian.show', compact('laporanPenggajian'));
    }

    public function edit(LaporanPenggajian $laporanPenggajian)
    {
        // Mengambil data tukang cukur dan pesanan untuk dropdown
        $barbers = TukangCukur::all();
        $pesanans = Pesanan::all();
        return view('laporan_penggajian.edit', compact('laporanPenggajian', 'barbers', 'pesanans'));
    }

    public function update(Request $request, LaporanPenggajian $laporanPenggajian)
    {
        $request->validate([
            'id_barber' => 'required|string|exists:tukang_cukur,id',
            'id_pesanan' => 'required|string|exists:pesanan,id',
            'status' => 'required|string',
        ]);

        $laporanPenggajian->update($request->only([
            'id_barber', 'id_pesanan', 'status'
        ]));

        return redirect()->route('laporan_penggajian.index')
            ->with('success', 'Data laporan penggajian berhasil diperbarui.');
    }

    public function destroy(LaporanPenggajian $laporanPenggajian)
    {
        $laporanPenggajian->delete();
        return redirect()->route('laporan_penggajian.index')
            ->with('success', 'Data laporan penggajian berhasil dihapus.');
    }
    public function cetakPdf(Request $request)
{
    $query = LaporanPenggajian::with(['tukang_cukur', 'pendapatan']);

    // Jika filter bulan digunakan
    if ($request->filled('bulan')) {
        $bulan = $request->bulan;
        $query->whereMonth('created_at', date('m', strtotime($bulan)))
              ->whereYear('created_at', date('Y', strtotime($bulan)));
    }

    $laporanPenggajian = $query->get();

    $pdf = PDF::loadView('laporan_penggajian.pdf', compact('laporanPenggajian'));
    return $pdf->stream('laporan_penggajian.pdf');
}
}