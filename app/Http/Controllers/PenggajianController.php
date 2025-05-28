<?php

namespace App\Http\Controllers;

use App\Models\Penggajian;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Http\Requests\UpdatePenggajianRequest;
use App\Http\Requests\BayarPenggajianRequest;
use App\Services\PenggajianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PenggajianController extends Controller
{
    protected $penggajianService;

    public function __construct(PenggajianService $penggajianService)
    {
        $this->penggajianService = $penggajianService;
    }

    public function index(Request $request)
    {
        $query = Penggajian::query();

        if ($request->filled('nama_barber')) {
            $query->where('nama_barber', 'like', '%' . $request->input('nama_barber') . '%');
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pesanan', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pesanan', '<=', $request->input('tanggal_sampai'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $penggajian = $query->orderBy('tanggal_pesanan', 'desc')->paginate(10);
        $barbers = TukangCukur::pluck('nama', 'nama');

        $penggajian->appends($request->query());

        return view('penggajian.index', compact('penggajian', 'barbers'));
    }

    public function create()
    {
        $barbers = TukangCukur::all();
        return view('penggajian.generate', compact('barbers'));
    }

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
                $request->input('tanggal_dari'),
                $request->input('tanggal_sampai'),
                $request->input('id_barber')
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

    public function bayar(Request $request)
    {
        // dd(request()->all());
            $request->validate([
            'id_gaji' => 'required|array',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            // Simpan file
            $path = $request->file('bukti_transfer')->store('assets/images', 'public');

            // Simpan ke database
            foreach ($request->id_gaji as $id) {
                $gaji = Penggajian::findOrFail($id);
                $gaji->status = 'lunas';
                $gaji->bukti_transfer = $path;
                $gaji->save();
            }

            return redirect()->route('penggajian.index')->with('success', 'Pembayaran berhasil dilakukan!');
        } catch (\Exception $e) {
            Log::error('Error during payment processing: ' . $e->getMessage());
            return redirect()->route('penggajian.index')->with('error', 'Pembayaran gagal: ' . $e->getMessage());
        }
    }


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
 * @param \Illuminate\Http\Request $request
         */
    public function update(UpdatePenggajianRequest $request, $id)
    {
        try {
            $penggajian = Penggajian::findOrFail($id);

            $potongan = $request->input('potongan', 0);
            $totalGaji = $penggajian->total_bayar - $potongan;

            $penggajian->update([
                'potongan' => $potongan,
                'total_gaji' => $totalGaji,
                'status' => $request->input('status')
            ]);

            return redirect()->route('penggajian.index')
                ->with('success', 'Data penggajian berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Gagal update data penggajian: ' . $e->getMessage());
        }
    }

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

    public function laporan(Request $request)
    {
        $laporan = $this->penggajianService->getLaporanPenggajian(
            $request->input('tanggal_dari'),
            $request->input('tanggal_sampai'),
            $request->input('id_barber')
        );

        $barbers = TukangCukur::all();

        return view('penggajian.laporan', compact('laporan', 'barbers'));
    }

    public function export(Request $request)
    {
        return response()->json([
            'message' => 'Export feature will be implemented',
            'filters' => $request->all()
        ]);
    }

    public function showBayarForm(Request $request)
    {
        $ids = explode(',', $request->get('ids', ''));

        if (empty($ids) || $ids[0] === '') {
            return redirect()->route('penggajian.index')
                ->with('error', 'Tidak ada data gaji yang dipilih.');
        }

        // Cukup pakai Eloquent, ini sudah cukup
        $selectedGaji = Penggajian::whereIn('id_gaji', $ids)
            ->where('status', '!=', 'lunas')
            ->get();

        if ($selectedGaji->isEmpty()) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Data gaji tidak ditemukan atau sudah lunas.');
        }

        return view('penggajian.bayar_gaji', compact('selectedGaji'));
    }

}
