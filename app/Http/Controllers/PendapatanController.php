<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendapatan;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use Illuminate\Support\Facades\DB;

class PendapatanController extends Controller
{
    public function pendapatan(Request $request)
    {
        $query = Pesanan::select(
            'id as id_pesanan',
            DB::raw('(nominal + ongkos_kirim) as total_bayar'),
            'tgl_pesanan as tanggal_bayar',
            'id_barber',
            'id_pelanggan'
        )->with(['barber', 'pelanggan', 'jadwal'])
        ->where('status_pembayaran', 'paid'); // Hanya ambil yang sudah bayar

        if ($request->filled('barber')) {
            $query->where('id_barber', $request->barber);
        }

        if ($request->filled('bulan')) {
            $bulan = \Carbon\Carbon::parse($request->bulan);
            $query->whereMonth('tgl_pesanan', $bulan->format('m'))
                  ->whereYear('tgl_pesanan', $bulan->format('Y'));
        }

        $pendapatan = $query->get();

        // Generate ID pendapatan otomatis untuk setiap pesanan
        $pendapatan = $pendapatan->map(function ($item, $index) {
            $item->id_pendapatan_generated = 'PD' . ($index + 1);
            return $item;
        });

        $barbers = TukangCukur::all();

        return view('pendapatan', compact('pendapatan', 'barbers'));
    }

    public function pendapatanFromPendapatan(Request $request)
    {
        $query = Pendapatan::with(['pesanan.barber', 'pesanan.pelanggan', 'tukang_cukur', 'pelanggan']);

        if ($request->filled('barber')) {
            $query->where('id_barber', $request->barber);
        }

        if ($request->filled('bulan')) {
            $bulan = \Carbon\Carbon::parse($request->bulan);
            $query->whereMonth('tanggal_bayar', $bulan->format('m'))
                  ->whereYear('tanggal_bayar', $bulan->format('Y'));
        }

        $pendapatan = $query->get();
        $barbers = TukangCukur::all();

        return view('pendapatan', compact('pendapatan', 'barbers'));
    }
}
