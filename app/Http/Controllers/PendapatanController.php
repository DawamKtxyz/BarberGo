<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendapatan;
use App\Models\Pesanan;
use App\Models\TukangCukur;

class PendapatanController extends Controller
{
    public function pendapatan(Request $request)
    {
        // Ganti 'barber' jadi 'tukangCukur'
        $query = Pesanan::with(['barber', 'pelanggan', 'jadwal', 'pendapatan']);

        if ($request->filled('barber')) {
            $query->where('id_barber', $request->barber);
        }

        if ($request->filled('bulan')) {
            $bulan = \Carbon\Carbon::parse($request->bulan);
            $query->whereMonth('tgl_pesanan', $bulan->format('m'))
                  ->whereYear('tgl_pesanan', $bulan->format('Y'));
        }

        $pendapatan = $query->get();
        $barbers = TukangCukur::all();

        return view('pendapatan', compact('pendapatan', 'barbers'));
    }

    public function pendapatanFromPendapatan(Request $request)
    {
        // Ganti 'pesanan.barber' jadi 'pesanan.tukangCukur'
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
