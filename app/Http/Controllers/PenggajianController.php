<?php

namespace App\Http\Controllers;

use App\Models\Penggajian;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Models\Pelanggan;
use App\Http\Requests\UpdatePenggajianRequest;
use App\Http\Requests\BayarPenggajianRequest;
use App\Services\PenggajianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PenggajianController extends Controller
{
    protected $penggajianService;

    public function __construct(PenggajianService $penggajianService)
    {
        $this->penggajianService = $penggajianService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Penggajian::query();

        // Filter berdasarkan nama barber
        if ($request->filled('nama_barber')) {
            $query->where('nama_barber', 'like', '%' . $request->nama_barber . '%');
        }

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pesanan', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pesanan', '<=', $request->tanggal_sampai);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penggajian = $query->orderBy('tanggal_pesanan', 'desc')->paginate(10);

        // Untuk dropdown filter barber
        $barbers = TukangCukur::pluck('nama', 'nama');

        // Append query parameters to pagination links
        $penggajian->appends($request->query());

        return view('penggajian.index', compact('penggajian', 'barbers'));
    }

    /**
     * Show the form for generating penggajian
     */
    public function create()
    {
        $barbers = TukangCukur::all();
        return view('penggajian.generate', compact('barbers'));
    }

    /**
     * Generate penggajian dari pesanan
     */
    public function generate(Request $request)
    {
        $request->validate([
            'tanggal_dari' => 'required|date',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari',
            'id_barber' => 'nullable|exists:tukang_cukur,id'
        ], [
            'tanggal_dari.required' => 'Tanggal dari harus diisi',
            'tanggal_sampai.required' => 'Tanggal sampai harus diisi',
            'tanggal_sampai.after_or_equal' => 'Tanggal sampai harus setelah atau sama dengan tanggal dari',
            'id_barber.exists' => 'Barber tidak ditemukan'
        ]);

        try {
            $generated = $this->penggajianService->generateFromPesanan(
                $request->tanggal_dari,
                $request->tanggal_sampai,
                $request->id_barber
            );

            if ($generated > 0) {
                return redirect()->route('penggajian.index')
                    ->with('success', "Berhasil generate {$generated} data penggajian");
            } else {
                return redirect()->route('penggajian.index')
                    ->with('info', 'Tidak ada data pesanan baru untuk di-generate atau semua pesanan sudah ada di penggajian');
            }
        } catch (\Exception $e) {
            return redirect()->route('penggajian.create')
                ->with('error', 'Gagal generate penggajian: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Proses pembayaran gaji
     */
    public function bayar(BayarPenggajianRequest $request)
    {
        try {
            // Cast ke Illuminate\Http\Request untuk method file()
            /** @var \Illuminate\Http\Request $request */
            $result = $this->penggajianService->processBayar(
                $request['id_gaji'],
                $request->file('bukti_transfer')
            );

            if ($result['success']) {
                return redirect()->route('penggajian.index')
                    ->with('success', "Pembayaran gaji berhasil diproses untuk {$result['updated']} data");
            } else {
                return redirect()->route('penggajian.index')
                    ->with('error', 'Gagal memproses pembayaran: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);
            return view('penggajian.edit', compact('penggajian'));
        } catch (\Exception $e) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Data penggajian tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePenggajianRequest $request, $id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            // Hitung ulang total gaji menggunakan array access
            $potongan = $request['potongan'] ?? 0;
            $totalGaji = $penggajian->total_bayar - $potongan;

            $penggajian->update([
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'status' => $request['status']
            ]);

            return redirect()->route('penggajian.index')
                ->with('success', 'Data penggajian berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Gagal update data penggajian: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $result = $this->penggajianService->deletePenggajian($id);

            if ($result['success']) {
                return redirect()->route('penggajian.index')
                    ->with('success', 'Data penggajian berhasil dihapus');
            } else {
                return redirect()->route('penggajian.index')
                    ->with('error', 'Gagal menghapus data: ' . $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Gagal menghapus data penggajian: ' . $e->getMessage());
        }
    }

    /**
     * Show laporan penggajian
     */
    public function laporan(Request $request)
    {
        $laporan = $this->penggajianService->getLaporanPenggajian(
            $request->tanggal_dari,
            $request->tanggal_sampai,
            $request->id_barber
        );

        $barbers = TukangCukur::all();

        return view('penggajian.laporan', compact('laporan', 'barbers'));
    }

    /**
     * Export laporan ke Excel/PDF
     */
    public function export(Request $request)
    {
        // Implementation untuk export bisa ditambahkan nanti
        // Menggunakan library seperti Laravel Excel atau DomPDF

        return response()->json([
            'message' => 'Export feature will be implemented',
            'filters' => $request->all()
        ]);
    }
}
