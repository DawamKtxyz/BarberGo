<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PelangganController extends Controller
{
    /**
     * Register a new customer
     */
    public function register(Request $request)
    {
        Log::info('Received registration request', $request->all());

        // Validate input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:pelanggan',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create customer
            $pelanggan = Pelanggan::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'alamat' => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
                'password' => bcrypt($request->password),
            ]);

            // Generate token
            $token = Str::random(80);
            $pelanggan->api_token = $token;
            $pelanggan->save();

            Log::info('Customer registered successfully', ['id' => $pelanggan->id]);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi pelanggan berhasil',
                'pelanggan' => $pelanggan,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error registering customer', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mendaftar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login customer
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find customer by email
        $pelanggan = Pelanggan::where('email', $request->email)->first();

        // Check if customer exists and password is correct
        if (!$pelanggan || !Hash::check($request->password, $pelanggan->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Generate token
        $token = Str::random(80);
        $pelanggan->api_token = $token;
        $pelanggan->save();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'pelanggan' => $pelanggan,
            'token' => $token
        ]);
    }

    /**
     * Get customer profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'pelanggan' => $request->user()
        ]);
    }

    /**
     * Update customer profile
     */
      public function updateProfile(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'nama' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:pelanggan,email,' . $request->user()->id,
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'password' => 'nullable|string|min:6',
            'profile_photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $pelanggan = $request->user();

        // Update fields if provided
        if ($request->has('nama')) $pelanggan->nama = $request->nama;
        if ($request->has('email')) $pelanggan->email = $request->email;
        if ($request->has('telepon')) $pelanggan->telepon = $request->telepon;
        if ($request->has('alamat')) $pelanggan->alamat = $request->alamat;
        if ($request->has('tanggal_lahir')) $pelanggan->tanggal_lahir = $request->tanggal_lahir;
        if ($request->has('password')) $pelanggan->password = bcrypt($request->password);
        if ($request->has('profile_photo')) $pelanggan->profile_photo = $request->profile_photo;

        $pelanggan->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'pelanggan' => $pelanggan
        ]);
    }

    /**
     * Upload profile photo for customer
     */
    public function uploadProfilePhoto(Request $request)
    {
        try {
            // Get the authenticated customer
            $pelanggan = $request->user();

            // Check if we're handling a base64 image (from web) or a file upload (from mobile)
            $path = '';

            if ($request->has('profile_photo') && is_string($request->profile_photo)) {
                // Handle base64 image from web
                $base64Image = $request->profile_photo;

                // Remove data:image/png;base64, part if it exists
                if (strpos($base64Image, ';base64,') !== false) {
                    list(, $base64Image) = explode(';base64,', $base64Image);
                }

                $imageData = base64_decode($base64Image);

                if (!$imageData) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid base64 image data',
                    ], 400);
                }

                // Generate unique filename
                $filename = time() . '_' . Str::random(10) . '.jpg';
                $path = 'profile_photos/' . $filename;

                // Store the file
                Storage::disk('public')->put($path, $imageData);
            } else if ($request->hasFile('profile_photo')) {
                // Handle file upload from mobile
                $file = $request->file('profile_photo');

                // Validate file is an image
                $validator = Validator::make($request->all(), [
                    'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File harus berupa gambar (JPG, PNG) dan maksimal 2MB',
                    ], 422);
                }

                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('profile_photos', $filename, 'public');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile photo provided',
                ], 400);
            }

            // Delete old photo if exists
            if ($pelanggan->profile_photo && Storage::disk('public')->exists($pelanggan->profile_photo)) {
                Storage::disk('public')->delete($pelanggan->profile_photo);
            }

            // Update customer profile
            $pelanggan->profile_photo = $path;
            $pelanggan->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'image_path' => $path,
                'image_url' => asset('storage/' . $path),
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading profile photo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
