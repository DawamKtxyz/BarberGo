<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penggajian;
use App\Models\TukangCukur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    /**
     * Get penggajian data for logged-in barber
     */
    public function getPenggajianBarber(Request $request)
    {
        try {
            $barber = Auth::guard('sanctum')->user();

            if (!$barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $query = Penggajian::where('id_barber', $barber->id);

            // Filter berdasarkan status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan periode
            if ($request->filled('tanggal_dari')) {
                $query->whereDate('tanggal_pesanan', '>=', $request->tanggal_dari);
            }

            if ($request->filled('tanggal_sampai')) {
                $query->whereDate('tanggal_pesanan', '<=', $request->tanggal_sampai);
            }

            // Default: data 3 bulan terakhir
            if (!$request->filled('tanggal_dari') && !$request->filled('tanggal_sampai')) {
                $query->whereDate('tanggal_pesanan', '>=', Carbon::now()->subMonths(3));
            }

            $penggajian = $query->orderBy('tanggal_pesanan', 'desc')
                               ->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $penggajian->items(),
                'pagination' => [
                    'current_page' => $penggajian->currentPage(),
                    'last_page' => $penggajian->lastPage(),
                    'per_page' => $penggajian->perPage(),
                    'total' => $penggajian->total(),
                    'has_more' => $penggajian->hasMorePages(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving penggajian data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get penggajian statistics for logged-in barber
     */
    public function getStatsPenggajian()
    {
        try {
            $barber = Auth::guard('sanctum')->user();

            if (!$barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $currentMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            // Total gaji menanti (belum lunas)
            $totalGajiMenanti = Penggajian::where('id_barber', $barber->id)
                ->where('status', 'belum lunas')
                ->sum('total_gaji');

            // Total gaji diterima bulan ini
            $totalGajiDiterima = Penggajian::where('id_barber', $barber->id)
                ->where('status', 'lunas')
                ->whereBetween('updated_at', [$currentMonth, $endOfMonth])
                ->sum('total_gaji');

            // Jumlah transaksi menanti
            $jumlahTransaksiMenanti = Penggajian::where('id_barber', $barber->id)
                ->where('status', 'belum lunas')
                ->count();

            // Jumlah transaksi selesai bulan ini
            $jumlahTransaksiSelesai = Penggajian::where('id_barber', $barber->id)
                ->where('status', 'lunas')
                ->whereBetween('updated_at', [$currentMonth, $endOfMonth])
                ->count();

            // Pendapatan total bulan ini (dari pesanan yang sudah dibayar)
            $pendapatanBulanIni = Penggajian::where('id_barber', $barber->id)
                ->whereBetween('tanggal_pesanan', [$currentMonth, $endOfMonth])
                ->sum('total_bayar');

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_gaji_menanti' => $totalGajiMenanti,
                    'total_gaji_diterima' => $totalGajiDiterima,
                    'jumlah_transaksi_menanti' => $jumlahTransaksiMenanti,
                    'jumlah_transaksi_selesai' => $jumlahTransaksiSelesai,
                    'pendapatan_bulan_ini' => $pendapatanBulanIni,
                    'periode' => Carbon::now()->format('F Y'),
                    'formatted_total_gaji_menanti' => 'Rp ' . number_format($totalGajiMenanti, 0, ',', '.'),
                    'formatted_total_gaji_diterima' => 'Rp ' . number_format($totalGajiDiterima, 0, ',', '.'),
                    'formatted_pendapatan_bulan_ini' => 'Rp ' . number_format($pendapatanBulanIni, 0, ',', '.'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving penggajian stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific penggajian detail
     */
    public function showPenggajian($id)
    {
        try {
            $barber = Auth::guard('sanctum')->user();

            if (!$barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $penggajian = Penggajian::where('id_gaji', $id)
                ->where('id_barber', $barber->id)
                ->first();

            if (!$penggajian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Penggajian not found'
                ], 404);
            }

            // Get related pesanan data if needed
            $pesanan = DB::table('pesanan')->where('id', $penggajian->id_pesanan)->first();
            $jadwal = null;

            if ($penggajian->jadwal_id) {
                $jadwal = DB::table('jadwal_tukang_cukur')
                    ->where('id', $penggajian->jadwal_id)
                    ->first();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'penggajian' => $penggajian,
                    'pesanan' => $pesanan,
                    'jadwal' => $jadwal,
                    'bukti_transfer_url' => $penggajian->bukti_transfer
                        ? asset('storage/' . $penggajian->bukti_transfer)
                        : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving penggajian detail: ' . $e->getMessage()
            ], 500);
        }
    }
}
