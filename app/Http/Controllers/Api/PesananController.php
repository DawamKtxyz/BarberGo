<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\TukangCukur;
use App\Models\JadwalTukangCukur;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PesananController extends Controller
{
    /**
     * Create a new booking (for customers)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_barber' => 'required|exists:tukang_cukur,id',
                'jadwal_id' => 'required|exists:jadwal_tukang_cukur,id',
                'alamat_lengkap' => 'required|string',
                'email' => 'required|email',
                'telepon' => 'required|string',
                'ongkos_kirim' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the selected schedule
            $jadwal = JadwalTukangCukur::with('tukangCukur')->find($request->jadwal_id);

            // Verify that the schedule belongs to the specified barber
            if ($jadwal->tukang_cukur_id != $request->id_barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule does not belong to the specified barber'
                ], 400);
            }

            // Check if the time slot is still available
            $existingBooking = Pesanan::where('jadwal_id', $request->jadwal_id)->first();
            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot is already booked'
                ], 400);
            }

            // Check if the schedule is not in the past
            $scheduleDateTime = Carbon::parse($jadwal->tanggal->format('Y-m-d') . ' ' . $jadwal->jam->format('H:i:s'));
            if ($scheduleDateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book for past schedules'
                ], 400);
            }

            // Get customer (user) information
            $pelanggan = $request->user();

            // Calculate total cost
            $hargaLayanan = $jadwal->tukangCukur->harga;
            $ongkosKirim = $request->ongkos_kirim ?? 10000; // Default 10k
            $totalNominal = $hargaLayanan + $ongkosKirim;

            // Generate transaction ID
            $idTransaksi = 'TRX-' . strtoupper(Str::random(8)) . '-' . time();

            // Create booking
            $pesanan = Pesanan::create([
                'id_barber' => $request->id_barber,
                'id_pelanggan' => $pelanggan->id,
                'jadwal_id' => $request->jadwal_id,
                'tgl_pesanan' => $jadwal->tanggal,
                'nominal' => $totalNominal,
                'id_transaksi' => $idTransaksi,
                'alamat_lengkap' => $request->alamat_lengkap,
                'ongkos_kirim' => $ongkosKirim,
                'email' => $request->email,
                'telepon' => $request->telepon,
            ]);

            // Load relationships for response
            $pesanan->load(['barber', 'pelanggan', 'jadwal']);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => [
                    'id' => $pesanan->id,
                    'id_transaksi' => $pesanan->id_transaksi,
                    'barber_name' => $pesanan->barber->nama,
                    'schedule_date' => $pesanan->jadwal->tanggal->format('Y-m-d'),
                    'schedule_time' => $pesanan->jadwal->jam->format('H:i'),
                    'total_amount' => $pesanan->nominal,
                    'service_fee' => $hargaLayanan,
                    'delivery_fee' => $ongkosKirim,
                    'status' => 'pending',
                    'created_at' => $pesanan->created_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer's bookings
     */
    public function getMyBookings(Request $request)
    {
        try {
            $pelanggan = $request->user();
            $status = $request->get('status', 'all'); // 'all', 'active', 'history'

            $query = Pesanan::where('id_pelanggan', $pelanggan->id)
                ->with(['barber', 'jadwal']);

            if ($status === 'active') {
                // Show upcoming bookings only
                $query->where('tgl_pesanan', '>=', Carbon::now()->format('Y-m-d'));
            } elseif ($status === 'history') {
                // Show past bookings only
                $query->where('tgl_pesanan', '<', Carbon::now()->format('Y-m-d'));
            }

            $bookings = $query->orderBy('tgl_pesanan', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($booking) {
                    $scheduleDateTime = Carbon::parse($booking->jadwal->tanggal->format('Y-m-d') . ' ' . $booking->jadwal->jam->format('H:i:s'));

                    return [
                        'id' => $booking->id,
                        'id_transaksi' => $booking->id_transaksi,
                        'barber' => [
                            'id' => $booking->barber->id,
                            'nama' => $booking->barber->nama,
                            'spesialisasi' => $booking->barber->spesialisasi,
                            'telepon' => $booking->barber->telepon,
                        ],
                        'schedule' => [
                            'date' => $booking->jadwal->tanggal->format('Y-m-d'),
                            'time' => $booking->jadwal->jam->format('H:i'),
                            'day_name' => $booking->jadwal->tanggal->format('l'),
                            'formatted_date' => $booking->jadwal->tanggal->format('d M Y'),
                        ],
                        'booking_details' => [
                            'alamat' => $booking->alamat_lengkap,
                            'total_amount' => $booking->nominal,
                            'ongkos_kirim' => $booking->ongkos_kirim,
                            'service_fee' => $booking->nominal - $booking->ongkos_kirim,
                            'formatted_amount' => 'Rp ' . number_format($booking->nominal, 0, ',', '.'),
                        ],
                        'status' => $scheduleDateTime->isPast() ? 'completed' : 'upcoming',
                        'can_cancel' => $scheduleDateTime->diffInHours(Carbon::now()) > 2, // Can cancel if more than 2 hours before
                        'created_at' => $booking->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'bookings' => $bookings,
                'total' => $bookings->count(),
                'active_count' => $bookings->where('status', 'upcoming')->count(),
                'history_count' => $bookings->where('status', 'completed')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details
     */
    public function show(Request $request, $id)
    {
        try {
            $pelanggan = $request->user();

            $booking = Pesanan::where('id', $id)
                ->where('id_pelanggan', $pelanggan->id)
                ->with(['barber', 'jadwal'])
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $scheduleDateTime = Carbon::parse($booking->jadwal->tanggal->format('Y-m-d') . ' ' . $booking->jadwal->jam->format('H:i:s'));

            $bookingDetails = [
                'id' => $booking->id,
                'id_transaksi' => $booking->id_transaksi,
                'barber' => [
                    'id' => $booking->barber->id,
                    'nama' => $booking->barber->nama,
                    'spesialisasi' => $booking->barber->spesialisasi,
                    'telepon' => $booking->barber->telepon,
                    'email' => $booking->barber->email,
                ],
                'schedule' => [
                    'date' => $booking->jadwal->tanggal->format('Y-m-d'),
                    'time' => $booking->jadwal->jam->format('H:i'),
                    'day_name' => $booking->jadwal->tanggal->format('l'),
                    'formatted_date' => $booking->jadwal->tanggal->format('d M Y'),
                    'formatted_datetime' => $scheduleDateTime->format('d M Y, H:i'),
                ],
                'booking_info' => [
                    'alamat_lengkap' => $booking->alamat_lengkap,
                    'email' => $booking->email,
                    'telepon' => $booking->telepon,
                    'total_amount' => $booking->nominal,
                    'ongkos_kirim' => $booking->ongkos_kirim,
                    'service_fee' => $booking->nominal - $booking->ongkos_kirim,
                    'formatted_amount' => 'Rp ' . number_format($booking->nominal, 0, ',', '.'),
                    'formatted_service_fee' => 'Rp ' . number_format($booking->nominal - $booking->ongkos_kirim, 0, ',', '.'),
                    'formatted_delivery_fee' => 'Rp ' . number_format($booking->ongkos_kirim, 0, ',', '.'),
                ],
                'status' => $scheduleDateTime->isPast() ? 'completed' : 'upcoming',
                'can_cancel' => $scheduleDateTime->diffInHours(Carbon::now()) > 2,
                'created_at' => $booking->created_at,
                'time_until_appointment' => $scheduleDateTime->diffForHumans(),
            ];

            return response()->json([
                'success' => true,
                'booking' => $bookingDetails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving booking details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, $id)
    {
        try {
            $pelanggan = $request->user();

            $booking = Pesanan::where('id', $id)
                ->where('id_pelanggan', $pelanggan->id)
                ->with(['jadwal'])
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $scheduleDateTime = Carbon::parse($booking->jadwal->tanggal->format('Y-m-d') . ' ' . $booking->jadwal->jam->format('H:i:s'));

            // Check if booking can be cancelled (more than 2 hours before)
            if ($scheduleDateTime->diffInHours(Carbon::now()) <= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel booking less than 2 hours before the appointment'
                ], 400);
            }

            // Check if booking is not in the past
            if ($scheduleDateTime->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel past bookings'
                ], 400);
            }

            // Delete the booking (this will make the time slot available again)
            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking statistics for customer
     */
    public function getMyStats(Request $request)
    {
        try {
            $pelanggan = $request->user();
            $currentYear = Carbon::now()->year;

            $stats = [
                'total_bookings' => Pesanan::where('id_pelanggan', $pelanggan->id)->count(),
                'total_bookings_this_year' => Pesanan::where('id_pelanggan', $pelanggan->id)
                    ->whereYear('tgl_pesanan', $currentYear)
                    ->count(),
                'total_spent_this_year' => Pesanan::where('id_pelanggan', $pelanggan->id)
                    ->whereYear('tgl_pesanan', $currentYear)
                    ->sum('nominal'),
                'upcoming_bookings' => Pesanan::where('id_pelanggan', $pelanggan->id)
                    ->where('tgl_pesanan', '>=', Carbon::now()->format('Y-m-d'))
                    ->count(),
                'favorite_barbers' => Pesanan::where('id_pelanggan', $pelanggan->id)
                    ->with('barber:id,nama')
                    ->select('id_barber', DB::raw('count(*) as bookings_count'))
                    ->groupBy('id_barber')
                    ->orderBy('bookings_count', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'barber_name' => $item->barber->nama,
                            'bookings_count' => $item->bookings_count
                        ];
                    })
            ];

            // Format currency
            $stats['formatted_total_spent_this_year'] = 'Rp ' . number_format($stats['total_spent_this_year'], 0, ',', '.');

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving booking stats: ' . $e->getMessage()
            ], 500);
        }
    }
}
