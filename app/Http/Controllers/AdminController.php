<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $admin = User::where('peran', 'admin')->latest()->paginate(10);
        return view('admin.index', compact('admin'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'peran' => 'admin',
        ]);

        return redirect()->route('admin.index')
            ->with('success', 'Data admin berhasil dibuat.');
    }

    public function show(User $admin)
    {
        return view('admin.show', compact('admin'));
    }

    public function edit(User $admin)
    {
        return view('admin.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pengguna,email,' . $admin->id,
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);
        return redirect()->route('admin.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }

    public function destroy(User $admin)
    {
        if (auth()->id() == $admin->id) {
            return redirect()->route('admin.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $admin->delete();
        return redirect()->route('admin.index')
            ->with('success', 'Data admin berhasil dihapus.');
    }
}
