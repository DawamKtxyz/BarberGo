<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TukangCukur;
use App\Models\JadwalTukangCukur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BarberController extends Controller
{
    /**
     * Get list of all barbers (for customer search)
     */
    public function index(Request $request)
    {
        try {
            $query = TukangCukur::query();

            // Apply search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('spesialisasi', 'like', "%{$search}%");
                });
            }

            // Apply specialization filter
            if ($request->has('spesialisasi') && !empty($request->spesialisasi)) {
                $query->where('spesialisasi', 'like', "%{$request->spesialisasi}%");
            }

            // Apply price range filter
            if ($request->has('harga_min')) {
                $query->where('harga', '>=', $request->harga_min);
            }
            if ($request->has('harga_max')) {
                $query->where('harga', '<=', $request->harga_max);
            }

            // Sort options
            switch ($request->get('sort_by', 'nama')) {
                case 'harga_asc':
                    $query->orderBy('harga', 'asc');
                    break;
                case 'harga_desc':
                    $query->orderBy('harga', 'desc');
                    break;
                case 'nama':
                default:
                    $query->orderBy('nama', 'asc');
                    break;
            }

            // Add ratings calculation (you can implement this later with reviews table)
            $barbers = $query->select([
                'id',
                'nama',
                'spesialisasi',
                'harga',
                'created_at'
            ])->paginate($request->get('per_page', 10));

            // Add mock ratings for now (you can replace this with actual reviews system)
            $barbersWithStats = $barbers->getCollection()->map(function ($barber) {
                $barber->rating = rand(40, 50) / 10; // Mock rating between 4.0-5.0
                $barber->total_reviews = rand(25, 200); // Mock review count
                $barber->formatted_harga = 'Rp ' . number_format($barber->harga, 0, ',', '.');
                return $barber;
            });

            $barbers->setCollection($barbersWithStats);

            return response()->json([
                'success' => true,
                'data' => $barbers->items(),
                'pagination' => [
                    'current_page' => $barbers->currentPage(),
                    'last_page' => $barbers->lastPage(),
                    'per_page' => $barbers->perPage(),
                    'total' => $barbers->total(),
                    'has_more' => $barbers->hasMorePages()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving barbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barber details with available schedules
     */
    public function show(Request $request, $id)
    {
        try {
            $barber = TukangCukur::select([
                'id',
                'nama',
                'email',
                'telepon',
                'spesialisasi',
                'harga',
                'created_at'
            ])->find($id);

            if (!$barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barber not found'
                ], 404);
            }

            // Get available schedules for the next 7 days
            $today = Carbon::now();
            $nextWeek = $today->copy()->addDays(7);

            $availableSchedules = JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                ->whereBetween('tanggal', [$today->format('Y-m-d'), $nextWeek->format('Y-m-d')])
                ->whereDoesntHave('pesanan') // Only show unbooked slots
                ->select(['id', 'tanggal', 'jam'])
                ->orderBy('tanggal')
                ->orderBy('jam')
                ->get()
                ->groupBy(function ($item) {
                    return $item->tanggal->format('Y-m-d');
                });

            // Add mock statistics
            $barber->rating = rand(40, 50) / 10; // Mock rating between 4.0-5.0
            $barber->total_reviews = rand(25, 200); // Mock review count
            $barber->completed_orders = rand(100, 500); // Mock completed orders
            $barber->formatted_harga = 'Rp ' . number_format($barber->harga, 0, ',', '.');

            return response()->json([
                'success' => true,
                'barber' => $barber,
                'available_schedules' => $availableSchedules,
                'total_available_slots' => JadwalTukangCukur::where('tukang_cukur_id', $barber->id)
                    ->whereBetween('tanggal', [$today->format('Y-m-d'), $nextWeek->format('Y-m-d')])
                    ->whereDoesntHave('pesanan')
                    ->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving barber details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barber's available slots for a specific date
     */
    public function getAvailableSlots(Request $request, $barberId)
    {
        try {
            $date = $request->get('date', Carbon::now()->format('Y-m-d'));

            // Validate date
            if (Carbon::parse($date)->isPast() && !Carbon::parse($date)->isToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot book for past dates'
                ], 400);
            }

            $barber = TukangCukur::find($barberId);
            if (!$barber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barber not found'
                ], 404);
            }

            $availableSlots = JadwalTukangCukur::where('tukang_cukur_id', $barberId)
                ->where('tanggal', $date)
                ->whereDoesntHave('pesanan') // Only show unbooked slots
                ->select(['id', 'jam'])
                ->orderBy('jam')
                ->get()
                ->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'jam' => $slot->jam->format('H:i'),
                        'display_time' => $slot->jam->format('H:i')
                    ];
                });

            return response()->json([
                'success' => true,
                'date' => $date,
                'barber' => [
                    'id' => $barber->id,
                    'nama' => $barber->nama,
                    'harga' => $barber->harga,
                    'formatted_harga' => 'Rp ' . number_format($barber->harga, 0, ',', '.')
                ],
                'available_slots' => $availableSlots,
                'total_slots' => $availableSlots->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available slots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get popular specializations for filtering
     */
    public function getSpecializations()
    {
        try {
            $specializations = TukangCukur::whereNotNull('spesialisasi')
                ->where('spesialisasi', '!=', '')
                ->groupBy('spesialisasi')
                ->select('spesialisasi', DB::raw('count(*) as total'))
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'specializations' => $specializations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving specializations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get barber statistics for dashboard
     */
   public function getStats(Request $request, $barberId)
{
    try {
        $barber = TukangCukur::find($barberId);
        if (!$barber) {
            return response()->json([
                'success' => false,
                'message' => 'Barber not found'
            ], 404);
        }

        $currentMonth = Carbon::now()->format('Y-m');
        $today = Carbon::now()->format('Y-m-d');

        // Get various statistics
        $stats = [
            'total_bookings_today' => DB::table('pesanan')
                ->where('id_barber', $barberId)
                ->where('tgl_pesanan', $today)
                ->count(),

            'total_bookings_this_month' => DB::table('pesanan')
                ->where('id_barber', $barberId)
                ->where('tgl_pesanan', 'like', $currentMonth . '%')
                ->count(),

            'total_revenue_this_month' => DB::table('pesanan')
                ->where('id_barber', $barberId)
                ->where('tgl_pesanan', 'like', $currentMonth . '%')
                ->sum(DB::raw('CAST(nominal as DECIMAL(10,2))')),

            'available_slots_today' => JadwalTukangCukur::where('tukang_cukur_id', $barberId)
                ->where('tanggal', $today)
                ->whereDoesntHave('pesanan')
                ->count(),

            'total_slots_this_week' => JadwalTukangCukur::where('tukang_cukur_id', $barberId)
                ->whereBetween('tanggal', [
                    Carbon::now()->startOfWeek()->format('Y-m-d'),
                    Carbon::now()->endOfWeek()->format('Y-m-d')
                ])
                ->count()
        ];

        // Format revenue
        $stats['formatted_revenue_this_month'] = 'Rp ' . number_format($stats['total_revenue_this_month'], 0, ',', '.');

        // Add this null check before returning the response
        if (empty($stats)) {
            $stats = [
                'total_bookings_today' => 0,
                'available_slots_today' => 0,
                'total_bookings_this_month' => 0,
                'formatted_revenue_this_month' => 'Rp 0'
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving barber stats: ' . $e->getMessage()
        ], 500);
    }
}
}
