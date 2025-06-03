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
        // PERUBAHAN: Join dengan tabel tukang_cukur untuk mendapatkan nama_bank
        $query = Penggajian::join('tukang_cukur', 'penggajian.nama_barber', '=', 'tukang_cukur.nama')
                          ->select('penggajian.*', 'tukang_cukur.nama_bank');

        if ($request->filled('nama_barber')) {
            $query->where('penggajian.nama_barber', 'like', '%' . $request->input('nama_barber') . '%');
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('penggajian.tanggal_pesanan', '>=', $request->input('tanggal_dari'));
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('penggajian.tanggal_pesanan', '<=', $request->input('tanggal_sampai'));
        }

        if ($request->filled('status')) {
            $query->where('penggajian.status', $request->input('status'));
        }

        $penggajian = $query->orderBy('penggajian.tanggal_pesanan', 'desc')->paginate(10);
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
        Log::info('Bayar Gaji Request Data:', $request->all());

        $request->validate([
            'id_gaji' => 'required|array|min:1',
            'id_gaji.*' => 'required|exists:penggajian,id_gaji',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'id_gaji.required' => 'Data gaji yang akan dibayar harus dipilih',
            'id_gaji.array' => 'Format data gaji tidak valid',
            'id_gaji.min' => 'Minimal pilih satu data gaji',
            'id_gaji.*.required' => 'ID gaji tidak boleh kosong',
            'id_gaji.*.exists' => 'Data gaji tidak ditemukan',
            'bukti_transfer.required' => 'Bukti transfer harus diupload',
            'bukti_transfer.image' => 'File harus berupa gambar',
            'bukti_transfer.mimes' => 'Format file harus JPG, JPEG, atau PNG',
            'bukti_transfer.max' => 'Ukuran file maksimal 2MB',
        ]);

        try {
            DB::beginTransaction();

            // PERUBAHAN: Join dengan tukang_cukur untuk mendapatkan nama_bank
            $gajiData = Penggajian::join('tukang_cukur', 'penggajian.nama_barber', '=', 'tukang_cukur.nama')
                                  ->select('penggajian.*', 'tukang_cukur.nama_bank')
                                  ->whereIn('penggajian.id_gaji', $request->id_gaji)
                                  ->where('penggajian.status', '!=', 'lunas')
                                  ->get();

            if ($gajiData->count() !== count($request->id_gaji)) {
                DB::rollBack();
                return redirect()->route('penggajian.index')
                    ->with('error', 'Beberapa data gaji sudah lunas atau tidak ditemukan');
            }

            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');

            $updatedCount = 0;
            foreach ($request->id_gaji as $id) {
                $gaji = Penggajian::find($id);
                if ($gaji && $gaji->status !== 'lunas') {
                    $gaji->status = 'lunas';
                    $gaji->bukti_transfer = $path;
                    $gaji->save();
                    $updatedCount++;
                }
            }

            DB::commit();

            Log::info("Pembayaran gaji berhasil untuk {$updatedCount} data");

            return redirect()->route('penggajian.index')
                ->with('success', "Pembayaran berhasil dilakukan untuk {$updatedCount} data gaji!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during payment processing: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('penggajian.index')
                ->with('error', 'Pembayaran gagal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            // PERUBAHAN: Join dengan tukang_cukur untuk mendapatkan nama_bank
            $penggajian = Penggajian::join('tukang_cukur', 'penggajian.nama_barber', '=', 'tukang_cukur.nama')
                                   ->select('penggajian.*', 'tukang_cukur.nama_bank')
                                   ->where('penggajian.id_gaji', $id)
                                   ->firstOrFail();
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

        Log::info('Show Bayar Form - IDs received:', $ids);

        if (empty($ids) || $ids[0] === '' || $ids[0] === null) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Tidak ada data gaji yang dipilih.');
        }

        $validIds = array_filter($ids, function($id) {
            return is_numeric($id) && $id > 0;
        });

        if (empty($validIds)) {
            return redirect()->route('penggajian.index')
                ->with('error', 'ID gaji tidak valid.');
        }

        // PERUBAHAN: Join dengan tukang_cukur untuk mendapatkan nama_bank
        $selectedGaji = Penggajian::join('tukang_cukur', 'penggajian.nama_barber', '=', 'tukang_cukur.nama')
                                 ->select('penggajian.*', 'tukang_cukur.nama_bank')
                                 ->whereIn('penggajian.id_gaji', $validIds)
                                 ->where('penggajian.status', '!=', 'lunas')
                                 ->get();

        Log::info('Selected Gaji Count:', ['count' => $selectedGaji->count()]);

        if ($selectedGaji->isEmpty()) {
            return redirect()->route('penggajian.index')
                ->with('error', 'Data gaji tidak ditemukan atau sudah lunas.');
        }

        foreach ($selectedGaji as $gaji) {
            Log::info('Gaji Data:', [
                'id_gaji' => $gaji->id_gaji,
                'nama_barber' => $gaji->nama_barber,
                'nama_bank' => $gaji->nama_bank, // TAMBAHAN: Log nama_bank
                'status' => $gaji->status
            ]);
        }

        return view('penggajian.bayar_gaji', compact('selectedGaji'));
    }
}
