<?php

namespace App\Http\Controllers;

use App\Models\TukangCukur;
use App\Models\JadwalTukangCukur;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TukangCukurController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $tukangCukur = TukangCukur::latest()->paginate(10);
        return view('tukang_cukur.index', compact('tukangCukur'));
    }

    public function create()
    {
        // Dapatkan bulan dan tahun saat ini
        $bulanIni = Carbon::now();
        $tanggalAwal = $bulanIni->copy()->startOfMonth();
        $tanggalAkhir = $bulanIni->copy()->endOfMonth();

        // Buat array tanggal untuk bulan ini
        $tanggal = [];
        for ($i = $tanggalAwal; $i <= $tanggalAkhir; $i->addDay()) {
            $tanggal[] = $i->copy();
        }

        // Daftar jam kerja yang tersedia (contoh: 8 pagi - 8 malam)
        $jamKerja = [];
        for ($jam = 8; $jam <= 20; $jam++) {
            $jamKerja[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
        }

        return view('tukang_cukur.create', compact('tanggal', 'jamKerja'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:tukang_cukur',
            'telepon' => 'required|string|max:15',
            'spesialisasi' => 'nullable|string',
            'harga' => 'required|numeric|min:0', // Perubahan dari persentase_komisi
            'rekening_barber' => 'nullable|string|max:50', // Validasi untuk rekening barber
            'password' => 'required|string|min:6|confirmed',
            'jadwal' => 'nullable|array',
            'jadwal.*.tanggal' => 'nullable|date',
            'jadwal.*.jam' => 'nullable|array',
            'jadwal.*.jam.*' => 'nullable|date_format:H:i',
            'sertifikat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['jadwal', 'password_confirmation']);
        $data['password'] = bcrypt($request->password);

        // Upload sertifikat
        if ($request->hasFile('sertifikat')) {
            $path = $request->file('sertifikat')->store('public/sertifikat');
            $data['sertifikat'] = str_replace('public/', 'storage/', $path);
        }

        $tukangCukur = TukangCukur::create($data);

        // Simpan jadwal (hanya jika jadwal disediakan)
        if ($request->has('jadwal') && is_array($request->jadwal)) {
            foreach ($request->jadwal as $jadwal) {
                // Periksa apakah tanggal dan jam ada dan tidak kosong
                if (isset($jadwal['tanggal']) && !empty($jadwal['tanggal']) &&
                    isset($jadwal['jam']) && is_array($jadwal['jam']) && !empty($jadwal['jam'])) {

                    $tanggal = $jadwal['tanggal'];
                    foreach ($jadwal['jam'] as $jam) {
                        if (!empty($jam)) {
                            JadwalTukangCukur::create([
                                'tukang_cukur_id' => $tukangCukur->id,
                                'tanggal' => $tanggal,
                                'jam' => $jam,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('tukang_cukur.index')->with('success', 'Data tukang cukur berhasil dibuat.');
    }

    public function show(TukangCukur $tukangCukur)
    {
        $jadwal = $tukangCukur->jadwal()->orderBy('tanggal')->orderBy('jam')->get();
        return view('tukang_cukur.show', compact('tukangCukur', 'jadwal'));
    }

    public function edit(TukangCukur $tukangCukur)
    {
        // Dapatkan bulan dan tahun saat ini
        $bulanIni = Carbon::now();
        $tanggalAwal = $bulanIni->copy()->startOfMonth();
        $tanggalAkhir = $bulanIni->copy()->endOfMonth();

        // Buat array tanggal untuk bulan ini
        $tanggal = [];
        for ($i = $tanggalAwal; $i <= $tanggalAkhir; $i->addDay()) {
            $tanggal[] = $i->copy();
        }

        // Daftar jam kerja yang tersedia (contoh: 8 pagi - 8 malam)
        $jamKerja = [];
        for ($jam = 8; $jam <= 20; $jam++) {
            $jamKerja[] = str_pad($jam, 2, '0', STR_PAD_LEFT) . ":00";
        }

        // Ambil jadwal yang sudah ada
        $jadwalExisting = $tukangCukur->jadwal()
            ->get()
            ->groupBy(function($item) {
                return $item->tanggal->format('Y-m-d');
            });

        return view('tukang_cukur.edit', compact('tukangCukur', 'tanggal', 'jamKerja', 'jadwalExisting'));
    }

    public function update(Request $request, TukangCukur $tukangCukur)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:tukang_cukur,email,' . $tukangCukur->id,
            'telepon' => 'required|string|max:15',
            'spesialisasi' => 'nullable|string',
            'harga' => 'required|numeric|min:0', // Perubahan dari persentase_komisi
            'rekening_barber' => 'nullable|string|max:50', // Validasi untuk rekening barber
            'password' => 'nullable|string|min:6|confirmed',
            'jadwal' => 'nullable|array',
            'jadwal.*.tanggal' => 'nullable|date',
            'jadwal.*.jam' => 'nullable|array',
            'jadwal.*.jam.*' => 'nullable|date_format:H:i',
            'sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['jadwal', 'password', 'password_confirmation']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // Upload sertifikat baru (jika ada)
        if ($request->hasFile('sertifikat')) {
            $path = $request->file('sertifikat')->store('public/sertifikat');
            $data['sertifikat'] = str_replace('public/', 'storage/', $path);
        }

        $tukangCukur->update($data);

        // Reset jadwal
        $tukangCukur->jadwal()->delete();

        // Simpan jadwal baru (hanya jika jadwal disediakan)
        if ($request->has('jadwal') && is_array($request->jadwal)) {
            foreach ($request->jadwal as $jadwal) {
                // Periksa apakah tanggal dan jam ada dan tidak kosong
                if (isset($jadwal['tanggal']) && !empty($jadwal['tanggal']) &&
                    isset($jadwal['jam']) && is_array($jadwal['jam']) && !empty($jadwal['jam'])) {

                    $tanggal = $jadwal['tanggal'];
                    foreach ($jadwal['jam'] as $jam) {
                        if (!empty($jam)) {
                            JadwalTukangCukur::create([
                                'tukang_cukur_id' => $tukangCukur->id,
                                'tanggal' => $tanggal,
                                'jam' => $jam,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('tukang_cukur.index')->with('success', 'Data tukang cukur berhasil diperbarui.');
    }

    public function destroy(TukangCukur $tukangCukur)
    {
        // Jadwal akan terhapus secara otomatis karena kita menggunakan ON DELETE CASCADE
        $tukangCukur->delete();

        return redirect()->route('tukang_cukur.index')
            ->with('success', 'Data tukang cukur berhasil dihapus.');
    }
}
