<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $pelanggan = Pelanggan::latest()->paginate(10);
        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggan',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'required|string|min:6|confirmed', // tambah validasi password
        ]);

        // Simpan password sudah di-hash
        $data = $request->except('password_confirmation');
        $data['password'] = bcrypt($request->password);

        Pelanggan::create($data);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dibuat.');
    }

    public function show(Pelanggan $pelanggan)
    {
        return view('pelanggan.show', compact('pelanggan'));
    }

    public function edit(Pelanggan $pelanggan)
    {
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, Pelanggan $pelanggan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggan,email,' . $pelanggan->id,
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6|confirmed', // tambah validasi password opsional
        ]);

        $data = $request->except('password_confirmation');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        $pelanggan->update($data);

        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(Pelanggan $pelanggan)
    {
        $pelanggan->delete();
        return redirect()->route('pelanggan.index')
            ->with('success', 'Data pelanggan berhasil dihapus.');
    }
}
