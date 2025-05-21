<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BarberProfileController extends Controller
{
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
                'harga' => $barber->harga,
                'persentase_komisi' => $barber->persentase_komisi,
                'sertifikat' => $barber->sertifikat,
                'profile_photo' => $barber->profile_photo,
                'created_at' => $barber->created_at,
                'updated_at' => $barber->updated_at,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $barber = $request->user();

        // Log received data for debugging
        Log::info('Received update data: ', $request->all());

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telepon' => 'required|string|max:15',
            'spesialisasi' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            'harga' => 'nullable|numeric|min:5000|max:1000000', // Add validation for harga
            'profile_photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'nama' => $request->nama,
            'telepon' => $request->telepon,
            'spesialisasi' => $request->spesialisasi,
        ];

        // Include harga in update data if it's provided
        if ($request->has('harga')) {
            $updateData['harga'] = $request->harga;
        }

        // Include profile_photo if provided
        if ($request->has('profile_photo')) {
            $updateData['profile_photo'] = $request->profile_photo;
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Log update data before applying
        Log::info('Updating barber with data: ', $updateData);

        $barber->update($updateData);

        // Return complete and updated barber data
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate',
            'barber' => [
                'id' => $barber->id,
                'nama' => $barber->nama,
                'email' => $barber->email,
                'telepon' => $barber->telepon,
                'spesialisasi' => $barber->spesialisasi,
                'harga' => $barber->harga,
                'persentase_komisi' => $barber->persentase_komisi,
                'sertifikat' => $barber->sertifikat,
                'profile_photo' => $barber->profile_photo,
                'created_at' => $barber->created_at,
                'updated_at' => $barber->updated_at,
            ]
        ]);
    }

    /**
     * Upload profile photo for barber
     */
    public function uploadProfilePhoto(Request $request)
    {
        try {
            // Get the authenticated barber
            $barber = $request->user();

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
            if ($barber->profile_photo && Storage::disk('public')->exists($barber->profile_photo)) {
                Storage::disk('public')->delete($barber->profile_photo);
            }

            // Update barber profile
            $barber->profile_photo = $path;
            $barber->save();

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
