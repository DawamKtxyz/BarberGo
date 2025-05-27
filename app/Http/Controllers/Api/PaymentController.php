<?php
// File: app/Http/Controllers/Api/PaymentController.php - Update dengan real Midtrans
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Notification;

class PaymentController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Create payment for booking
     */
    public function createPayment(Request $request, $bookingId)
    {
        try {
            Log::info('Real Midtrans payment creation requested for booking: ' . $bookingId);

            $user = $request->user();

            // Get booking
            $pesanan = Pesanan::where('id', $bookingId)
                ->where('id_pelanggan', $user->id)
                ->with(['barber', 'jadwal', 'pelanggan'])
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found or access denied',
                ], 404);
            }

            // Check if already paid
            if ($pesanan->status_pembayaran === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking already paid',
                ], 400);
            }

            // Create real Midtrans transaction
            $result = $this->midtransService->createTransaction($pesanan);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => [
                    'snap_token' => $result['snap_token'],
                    'payment_url' => $result['payment_url'],
                    'booking_id' => $pesanan->id,
                    'order_id' => $pesanan->id_transaksi,
                    'gross_amount' => (float) $pesanan->nominal + (float) $pesanan->ongkos_kirim,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Real Midtrans payment creation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating payment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification webhook
     */
    public function notification(Request $request)
    {
        try {
            Log::info('Midtrans notification received', $request->all());

            $notification = new Notification();
            $result = $this->midtransService->handleNotification($notification);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request, $orderId)
    {
        try {
            $user = $request->user();

            $pesanan = Pesanan::where('id_transaksi', $orderId)
                ->where('id_pelanggan', $user->id)
                ->first();

            if (!$pesanan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied',
                ], 404);
            }

            // Check with Midtrans
            $midtransResult = $this->midtransService->checkPaymentStatus($orderId);

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $orderId,
                    'payment_status' => $pesanan->status_pembayaran,
                    'midtrans_status' => $midtransResult['success'] ? $midtransResult['data'] : null,
                    'created_at' => $pesanan->created_at,
                    'updated_at' => $pesanan->updated_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment history for user
     */
    public function getPaymentHistory(Request $request)
    {
        try {
            $user = $request->user();

            $payments = Pesanan::where('id_pelanggan', $user->id)
                ->with(['barber', 'jadwal'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedPayments = $payments->map(function ($pesanan) {
                return [
                    'id' => $pesanan->id,
                    'order_id' => $pesanan->id_transaksi,
                    'barber_name' => $pesanan->barber->nama,
                    'service_fee' => (float) $pesanan->nominal,
                    'delivery_fee' => (float) $pesanan->ongkos_kirim,
                    'total_amount' => (float) $pesanan->nominal + (float) $pesanan->ongkos_kirim,
                    'payment_status' => $pesanan->status_pembayaran,
                    'payment_method' => $pesanan->payment_method,
                    'paid_at' => $pesanan->paid_at,
                    'booking_date' => $pesanan->tgl_pesanan,
                    'created_at' => $pesanan->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'payments' => $formattedPayments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting payment history: ' . $e->getMessage(),
            ], 500);
        }
    }
}
