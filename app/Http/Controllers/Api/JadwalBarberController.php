<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalTukangCukur;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class JadwalBarberController extends Controller
{
    /**
     * Get barber's own schedules
     */
    public function getMySchedules(Request $request)
    {
        try {
            $barber = $request->user();
            $today = Carbon::now()->format('Y-m-d');

            // Get schedules from today onwards
            $schedules = JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                ->where('tanggal', '>=', $today)
                ->with(['pesanan']) // Load booking information
                ->orderBy('tanggal')
                ->orderBy('jam')
                ->get();

            // Group by date
            $groupedSchedules = $schedules->groupBy(function ($item) {
                return $item->tanggal->format('Y-m-d');
            });

            return response()->json([
                'success' => true,
                'schedules' => $groupedSchedules,
                'total' => $schedules->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barber's bookings (incoming orders)
     */
    public function getMyBookings(Request $request)
    {
        try {
            $barber = $request->user();
            $today = Carbon::now()->format('Y-m-d');

            // Get bookings from today onwards
            $bookings = Pesanan::where('id_barber', $barber->id)
                ->where('tgl_pesanan', '>=', $today)
                ->with(['pelanggan', 'jadwal'])
                ->orderBy('tgl_pesanan')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'bookings' => $bookings,
                'total' => $bookings->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new time slot
     */
    public function addTimeSlot(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date|after_or_equal:today',
                'jam' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $barber = $request->user();

            // Check if time slot already exists
            $existingSlot = JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                ->where('tanggal', $request->tanggal)
                ->where('jam', $request->jam)
                ->first();

            if ($existingSlot) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot already exists'
                ], 400);
            }

            // Create new time slot
            $schedule = JadwalTukangCukur::create([
                'tukang_cukur_id' => $barber->id,
                'tanggal' => $request->tanggal,
                'jam' => $request->jam,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time slot added successfully',
                'schedule' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding time slot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete time slot
     */
    public function deleteTimeSlot(Request $request, $id)
    {
        try {
            $barber = $request->user();

            $schedule = JadwalTukangCukur::where('id', $id)
                ->where('tukang_cukur_id', $barber->id)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot not found'
                ], 404);
            }

            // Check if there's an existing booking
            $hasBooking = Pesanan::where('jadwal_id', $schedule->id)->exists();

            if ($hasBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete time slot with existing booking'
                ], 400);
            }

            $schedule->delete();

            return response()->json([
                'success' => true,
                'message' => 'Time slot deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting time slot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available time slots for a specific date
     */
    public function getAvailableSlots(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tanggal' => 'required|date|after_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $barber = $request->user();

            // Get all time slots for the date
            $timeSlots = JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                ->where('tanggal', $request->tanggal)
                ->with(['pesanan'])
                ->orderBy('jam')
                ->get();

            // Mark which slots are booked
            $slots = $timeSlots->map(function ($slot) {
                return [
                    'id' => $slot->id,
                    'jam' => $slot->jam->format('H:i'),
                    'tanggal' => $slot->tanggal->format('Y-m-d'),
                    'is_booked' => !is_null($slot->pesanan),
                    'booking_info' => $slot->pesanan ? [
                        'id' => $slot->pesanan->id,
                        'pelanggan_nama' => $slot->pesanan->pelanggan->nama ?? 'Unknown',
                        'nominal' => $slot->pesanan->nominal,
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'slots' => $slots,
                'total' => $slots->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving time slots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk add time slots for multiple days
     */
    public function bulkAddTimeSlots(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'slots' => 'required|array',
                'slots.*.tanggal' => 'required|date|after_or_equal:today',
                'slots.*.jam' => 'required|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $barber = $request->user();
            $createdSlots = [];
            $errors = [];

            foreach ($request->slots as $index => $slot) {
                try {
                    // Check if time slot already exists
                    $existingSlot = JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                        ->where('tanggal', $slot['tanggal'])
                        ->where('jam', $slot['jam'])
                        ->first();

                    if (!$existingSlot) {
                        $schedule = JadwalTukangCukur::create([
                            'tukang_cukur_id' => $barber->id,
                            'tanggal' => $slot['tanggal'],
                            'jam' => $slot['jam'],
                        ]);
                        $createdSlots[] = $schedule;
                    } else {
                        $errors[] = "Slot {$slot['tanggal']} {$slot['jam']} already exists";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error creating slot {$index}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($createdSlots) . ' time slots created successfully',
                'created_slots' => $createdSlots,
                'errors' => $errors,
                'total_created' => count($createdSlots)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error bulk adding time slots: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMultiple(Request $request)
{
    JadwalTukangCukur::whereIn('id', $request->ids)->delete();
    return response()->json(['message' => 'Jadwal berhasil dihapus']);
}
}
