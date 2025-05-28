<?php

namespace App\Http\Controllers;

use App\Models\LaporanPenggajian;
use App\Models\DetailLaporanPenggajian;
use App\Models\TukangCukur;
use App\Models\Pesanan;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class LaporanPenggajianController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $query = LaporanPenggajian::with(['barber'])->latest();

        // Filter berdasarkan barber
        if ($request->filled('id_barber')) {
            $query->where('id_barber', $request->id_barber);
        }

        // Filter berdasarkan bulan
        if ($request->filled('bulan')) {
            try {
                $tanggal = Carbon::createFromFormat('Y-m', $request->bulan);
                $query->whereMonth('created_at', $tanggal->format('m'))
                      ->whereYear('created_at', $tanggal->format('Y'));
            } catch (\Exception $e) {
                return redirect()->route('laporan_penggajian.index')
                    ->with('error', 'Format bulan tidak valid.');
            }
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporanPenggajian = $query->paginate(10);

        // Hitung total keseluruhan
        $allData = $query->get();

        $totalKeseluruhan = [
        'total_pendapatan' => $allData->sum('total_pendapatan'),
        'total_komisi' => $allData->sum('potongan_komisi'),
        'total_gaji' => $allData->sum('total_gaji'),
        ];


        // Data untuk filter
        $barbers = TukangCukur::all();

        // Append query parameters to pagination links
        $laporanPenggajian->appends($request->query());

        return view('laporan_penggajian.index', compact('laporanPenggajian', 'totalKeseluruhan', 'barbers'));
    }

    public function create()
    {
        $barbers = TukangCukur::all();
        return view('laporan_penggajian.create', compact('barbers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barber' => 'required|exists:tukang_cukur,id',
            'periode_dari' => 'required|date',
            'periode_sampai' => 'required|date|after_or_equal:periode_dari',
        ], [
            'id_barber.required' => 'Barber harus dipilih',
            'id_barber.exists' => 'Barber tidak ditemukan',
            'periode_dari.required' => 'Periode dari harus diisi',
            'periode_sampai.required' => 'Periode sampai harus diisi',
            'periode_sampai.after_or_equal' => 'Periode sampai harus setelah atau sama dengan periode dari',
        ]);

        try {
            DB::beginTransaction();

            // Cek apakah sudah ada laporan untuk barber dan periode yang sama
            $existingLaporan = LaporanPenggajian::where('id_barber', $request->id_barber)
                ->where('periode_dari', $request->periode_dari)
                ->where('periode_sampai', $request->periode_sampai)
                ->first();

            if ($existingLaporan) {
                return redirect()->back()
                    ->with('error', 'Laporan untuk barber dan periode ini sudah ada')
                    ->withInput();
            }

            // Ambil data barber
            $barber = TukangCukur::findOrFail($request->id_barber);

            // Ambil data pesanan dalam periode dan barber yang dipilih
            $pesananQuery = Pesanan::with(['pelanggan'])
                ->where('id_barber', $request->id_barber)
                ->whereBetween('tgl_pesanan', [$request->periode_dari, $request->periode_sampai])
                ->where('status_pembayaran', 'paid'); // Hanya pesanan yang sudah selesai

            $pesanans = $pesananQuery->get();

            if ($pesanans->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'Tidak ada pesanan yang ditemukan untuk barber dan periode ini')
                    ->withInput();
            }

            // Hitung statistik dengan formula baru
            $jumlahPesanan = $pesanans->count();
            $jumlahPelanggan = $pesanans->pluck('id_pelanggan')->unique()->count();

            // Total pendapatan = sum(nominal + ongkos_kirim)
            // Asumsi ongkos_kirim otomatis 10.000 per pesanan
            $totalPendapatan = 0;
            foreach ($pesanans as $pesanan) {
                $nominalPesanan = $pesanan->nominal ?? 0;
                $ongkosKirim = 10000; // Ongkos kirim tetap 10.000
                $totalPendapatan += ($nominalPesanan + $ongkosKirim);
            }

            // Komisi = 5% per pesanan
            $persentaseKomisiPerPesanan = 5; // 5% per pesanan
            $totalPersentaseKomisi = $persentaseKomisiPerPesanan * $jumlahPesanan;
            $potonganKomisi = ($totalPendapatan * $totalPersentaseKomisi) / 100;

            // Total gaji = Total pendapatan - Komisi
            $totalGaji = $totalPendapatan - $potonganKomisi;

            // Buat laporan penggajian
            $laporan = LaporanPenggajian::create([
                'id_barber' => $request->id_barber,
                'nama_barber' => $barber->nama,
                'jumlah_pesanan' => $jumlahPesanan,
                'jumlah_pelanggan' => $jumlahPelanggan,
                'total_pendapatan' => $totalPendapatan,
                'potongan_komisi' => $potonganKomisi,
                'total_gaji' => $totalGaji,
                'periode_dari' => $request->periode_dari,
                'periode_sampai' => $request->periode_sampai,
                'status' => 'Belum Dibayar'
            ]);

            // Buat detail laporan untuk setiap pesanan
            foreach ($pesanans as $pesanan) {
                $nominalPesanan = $pesanan->nominal ?? 0;
                $ongkosKirim = 10000;
                $totalBayarPesanan = $nominalPesanan + $ongkosKirim;

                DetailLaporanPenggajian::create([
                    'id_laporan' => $laporan->id_gaji,
                    'id_pesanan' => $pesanan->id,
                    'id_pelanggan' => $pesanan->id_pelanggan,
                    'nama_pelanggan' => $pesanan->pelanggan->nama ?? 'N/A',
                    'tanggal_pesanan' => $pesanan->tgl_pesanan,
                    'nominal_bayar' => $totalBayarPesanan
                ]);
            }

            DB::commit();

            return redirect()->route('laporan_penggajian.index')
                ->with('success', "Laporan penggajian berhasil dibuat untuk {$barber->nama} dengan {$jumlahPesanan} pesanan (Komisi: {$totalPersentaseKomisi}%)");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat laporan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $laporan = LaporanPenggajian::with(['barber', 'detailLaporan.pelanggan'])
                ->findOrFail($id);

            return view('laporan_penggajian.show', compact('laporan'));
        } catch (\Exception $e) {
            return redirect()->route('laporan_penggajian.index')
                ->with('error', 'Laporan tidak ditemukan');
        }
    }

    public function edit($id)
    {
        try {
            $laporan = LaporanPenggajian::findOrFail($id);
            $barbers = TukangCukur::all();

            return view('laporan_penggajian.edit', compact('laporan', 'barbers'));
        } catch (\Exception $e) {
            return redirect()->route('laporan_penggajian.index')
                ->with('error', 'Laporan tidak ditemukan');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Belum Dibayar,Dibayar',
            'potongan_tambahan' => 'nullable|numeric|min:0'
        ]);

        try {
            $laporan = LaporanPenggajian::findOrFail($id);

            // Hitung ulang total gaji jika ada potongan tambahan
            $potonganTambahan = $request->potongan_tambahan ?? 0;
            $totalGaji = $laporan->total_pendapatan - $laporan->potongan_komisi - $potonganTambahan;

            $laporan->update([
                'status' => $request->status,
                'total_gaji' => $totalGaji
            ]);

            return redirect()->route('laporan_penggajian.index')
                ->with('success', 'Laporan penggajian berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()->route('laporan_penggajian.index')
                ->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $laporan = LaporanPenggajian::findOrFail($id);

            // Hapus detail laporan terlebih dahulu
            DetailLaporanPenggajian::where('id_laporan', $id)->delete();

            // Hapus laporan utama
            $laporan->delete();

            DB::commit();

            return redirect()->route('laporan_penggajian.index')
                ->with('success', 'Laporan penggajian berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('laporan_penggajian.index')
                ->with('error', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
    }

    public function cetakPdf(Request $request)
    {
        $query = LaporanPenggajian::with(['barber']);

        // Filter berdasarkan bulan jika ada
        if ($request->filled('bulan')) {
            try {
                $tanggal = Carbon::createFromFormat('Y-m', $request->bulan);
                $query->whereMonth('created_at', $tanggal->format('m'))
                      ->whereYear('created_at', $tanggal->format('Y'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Format bulan tidak valid');
            }
        }

        // Filter berdasarkan barber jika ada
        if ($request->filled('id_barber')) {
            $query->where('id_barber', $request->id_barber);
        }

        $laporanPenggajian = $query->get();

        $totalKeseluruhan = [
            'total_pendapatan' => $laporanPenggajian->sum('total_pendapatan'),
            'total_komisi' => $laporanPenggajian->sum('potongan_komisi'),
            'total_gaji' => $laporanPenggajian->sum('total_gaji'),
        ];

        $pdf = PDF::loadView('laporan_penggajian.pdf', compact('laporanPenggajian', 'totalKeseluruhan'));

        $filename = 'laporan_penggajian_' . date('Y-m-d_H-i-s') . '.pdf';
        return $pdf->stream($filename);
    }

    // Method untuk generate laporan otomatis bulanan
    public function generateBulanan(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m',
            'id_barber' => 'nullable|exists:tukang_cukur,id'
        ]);

        try {
            DB::beginTransaction();

            $bulan = Carbon::createFromFormat('Y-m', $request->bulan);
            $periodeAwal = $bulan->copy()->startOfMonth();
            $periodeAkhir = $bulan->copy()->endOfMonth();

            $barbersQuery = TukangCukur::query();
            if ($request->filled('id_barber')) {
                $barbersQuery->where('id', $request->id_barber);
            }
            $barbers = $barbersQuery->get();

            $totalGenerated = 0;

            foreach ($barbers as $barber) {
                // Cek apakah sudah ada laporan untuk bulan ini
                $existingLaporan = LaporanPenggajian::where('id_barber', $barber->id)
                    ->where('periode_dari', $periodeAwal->format('Y-m-d'))
                    ->where('periode_sampai', $periodeAkhir->format('Y-m-d'))
                    ->first();

                if ($existingLaporan) {
                    continue; // Skip jika sudah ada
                }

                // Ambil pesanan untuk barber ini dalam periode
                $pesanans = Pesanan::with(['pelanggan'])
                    ->where('id_barber', $barber->id)
                    ->whereBetween('tgl_pesanan', [$periodeAwal, $periodeAkhir])
                    ->where('status_pembayaran', 'paid')
                    ->get();

                if ($pesanans->isEmpty()) {
                    continue; // Skip jika tidak ada pesanan
                }

                // Hitung statistik dengan formula baru
                $jumlahPesanan = $pesanans->count();
                $jumlahPelanggan = $pesanans->pluck('id_pelanggan')->unique()->count();

                // Total pendapatan = sum(nominal + ongkos_kirim)
                $totalPendapatan = 0;
                foreach ($pesanans as $pesanan) {
                    $nominalPesanan = $pesanan->nominal ?? 0;
                    $ongkosKirim = 10000; // Ongkos kirim tetap 10.000
                    $totalPendapatan += ($nominalPesanan + $ongkosKirim);
                }

                // Komisi = 5% per pesanan
                $persentaseKomisiPerPesanan = 5; // 5% per pesanan
                $totalPersentaseKomisi = $persentaseKomisiPerPesanan * $jumlahPesanan;
                $potonganKomisi = ($totalPendapatan * $totalPersentaseKomisi) / 100;

                // Total gaji = Total pendapatan - Komisi
                $totalGaji = $totalPendapatan - $potonganKomisi;

                // Buat laporan
                $laporan = LaporanPenggajian::create([
                    'id_barber' => $barber->id,
                    'nama_barber' => $barber->nama,
                    'jumlah_pesanan' => $jumlahPesanan,
                    'jumlah_pelanggan' => $jumlahPelanggan,
                    'total_pendapatan' => $totalPendapatan,
                    'potongan_komisi' => $potonganKomisi,
                    'total_gaji' => $totalGaji,
                    'periode_dari' => $periodeAwal->format('Y-m-d'),
                    'periode_sampai' => $periodeAkhir->format('Y-m-d'),
                    'status' => 'Belum Dibayar'
                ]);

                // Buat detail laporan
                foreach ($pesanans as $pesanan) {
                    $nominalPesanan = $pesanan->nominal ?? 0;
                    $ongkosKirim = 10000;
                    $totalBayarPesanan = $nominalPesanan + $ongkosKirim;

                    DetailLaporanPenggajian::create([
                        'id_laporan' => $laporan->id_gaji,
                        'id_pesanan' => $pesanan->id,
                        'id_pelanggan' => $pesanan->id_pelanggan,
                        'nama_pelanggan' => $pesanan->pelanggan->nama ?? 'N/A',
                        'tanggal_pesanan' => $pesanan->tgl_pesanan,
                        'nominal_bayar' => $totalBayarPesanan
                    ]);
                }

                $totalGenerated++;
            }

            DB::commit();

            if ($totalGenerated > 0) {
                return redirect()->route('laporan_penggajian.index')
                    ->with('success', "Berhasil generate {$totalGenerated} laporan penggajian untuk bulan " . $bulan->format('F Y'));
            } else {
                return redirect()->route('laporan_penggajian.index')
                    ->with('info', 'Tidak ada laporan baru yang di-generate. Semua laporan untuk periode ini sudah ada atau tidak ada pesanan.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('laporan_penggajian.index')
                ->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }
}
