<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TukangCukur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class BarberAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $barber = TukangCukur::where('email', $request->email)->first();

        if (!$barber || !Hash::check($request->password, $barber->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $barber->createToken('barber-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'sertifikat' => $barber->sertifikat,
                'harga' => $barber->harga,
            ]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tukang_cukur,email',
            'password' => ['required', 'string', Password::min(6)],
            'telepon' => 'required|string|max:15',
            'spesialisasi' => 'nullable|string',
            'harga' => 'nullable|numeric|min:0', // Tambahkan validasi untuk harga
            'sertifikat' => 'required|file|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload file sertifikat
if ($request->hasFile('sertifikat')) {
    $file = $request->file('sertifikat');
    $fileName = time() . '_' . $file->getClientOriginalName();
    $filePath = $file->storeAs('public/sertifikat', $fileName);
} else {
    return response()->json([
        'success' => false,
        'message' => 'Sertifikat harus diupload'
    ], 422);
}

 $harga = $request->has('harga') ? $request->harga : 20000;

        $barber = TukangCukur::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telepon' => $request->telepon,
            'spesialisasi' => $request->spesialisasi,
            'harga' => $harga, // default harga awal misalnya
            'sertifikat' => 'storage/sertifikat/' . $fileName,
            'persentase_komisi' => 0.05, // default komisi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'harga' => $barber->harga,
                'sertifikat' => $barber->sertifikat,
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }
}
