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

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Check if password is correct
        if (!Hash::check($request->password, $barber->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Check if barber is verified
        if (!$barber->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda belum diverifikasi. Silakan tunggu konfirmasi dari admin.',
                'is_verified' => false,
                'email' => $barber->email
            ], 403);
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
                'is_verified' => $barber->is_verified,
                'verified_at' => $barber->verified_at,
                'persentase_komisi' => $barber->persentase_komisi,
                'nama_bank' => $barber->nama_bank,
                'rekening_barber' => $barber->rekening_barber,
                'profile_photo' => $barber->profile_photo,
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
            'harga' => 'nullable|numeric|min:0',
            'nama_bank' => 'required|string|max:100',
            'rekening_barber' => 'required|string|max:50',
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
            'harga' => $harga,
            'nama_bank' => $request->nama_bank,
            'rekening_barber' => $request->rekening_barber,
            'sertifikat' => 'storage/sertifikat/' . $fileName,
            'persentase_komisi' => 0.05,
            'is_verified' => false, // Default not verified
            'verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan tunggu verifikasi dari admin sebelum dapat login.',
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'harga' => $barber->harga,
                'nama_bank' => $barber->nama_bank,
                'rekening_barber' => $barber->rekening_barber,
                'sertifikat' => $barber->sertifikat,
                'is_verified' => $barber->is_verified,
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

    public function checkVerificationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        $barber = TukangCukur::where('email', $request->email)->first();

        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Barber tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_verified' => $barber->is_verified,
            'verified_at' => $barber->verified_at,
            'message' => $barber->is_verified
                ? 'Akun sudah diverifikasi'
                : 'Akun belum diverifikasi. Silakan tunggu konfirmasi dari admin.'
        ]);
    }

    public function getProfile(Request $request)
    {
        $barber = $request->user();

        return response()->json([
            'success' => true,
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'sertifikat' => $barber->sertifikat,
                'harga' => $barber->harga,
                'is_verified' => $barber->is_verified,
                'verified_at' => $barber->verified_at,
                'persentase_komisi' => $barber->persentase_komisi,
                'nama_bank' => $barber->nama_bank,
                'rekening_barber' => $barber->rekening_barber,
                'profile_photo' => $barber->profile_photo,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $barber = $request->user();

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|string|max:255',
            'telepon' => 'sometimes|string|max:15',
            'spesialisasi' => 'sometimes|nullable|string',
            'harga' => 'sometimes|numeric|min:0',
            'nama_bank' => 'sometimes|string|max:100',
            'rekening_barber' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $barber->update($request->only([
            'nama', 'telepon', 'spesialisasi', 'harga', 'nama_bank', 'rekening_barber'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'sertifikat' => $barber->sertifikat,
                'harga' => $barber->harga,
                'is_verified' => $barber->is_verified,
                'verified_at' => $barber->verified_at,
                'persentase_komisi' => $barber->persentase_komisi,
                'nama_bank' => $barber->nama_bank,
                'rekening_barber' => $barber->rekening_barber,
                'profile_photo' => $barber->profile_photo,
            ]
        ]);
    }
}   
