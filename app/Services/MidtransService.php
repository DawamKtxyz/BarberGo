<?php
// File: app/Services/MidtransService.php
namespace App\Services;

use App\Models\Pesanan;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createTransaction(Pesanan $pesanan)
    {
        try {
            // Calculate total amount
            $serviceFee = (float) $pesanan->nominal;
            $deliveryFee = (float) $pesanan->ongkos_kirim;
            $totalAmount = $serviceFee + $deliveryFee;

            $params = [
                'transaction_details' => [
                    'order_id' => $pesanan->id_transaksi,
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $pesanan->pelanggan->nama ?? 'Customer',
                    'email' => $pesanan->email ?? $pesanan->pelanggan->email,
                    'phone' => $pesanan->telepon ?? $pesanan->pelanggan->telepon,
                ],
                'item_details' => [
                    [
                        'id' => 'service-' . $pesanan->id,
                        'price' => $serviceFee,
                        'quantity' => 1,
                        'name' => 'Layanan Barbershop - ' . $pesanan->barber->nama,
                    ],
                    [
                        'id' => 'delivery-' . $pesanan->id,
                        'price' => $deliveryFee,
                        'quantity' => 1,
                        'name' => 'Ongkos Kirim',
                    ],
                ],
                'callbacks' => [
                    'finish' => url('/payment/finish'),
                    'unfinish' => url('/payment/unfinish'),
                    'error' => url('/payment/error'),
                ],
            ];

            $snapToken = Snap::getSnapToken($params);
            $paymentUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;

            // Update pesanan with payment info
            $pesanan->update([
                'payment_token' => $snapToken,
                'payment_url' => $paymentUrl,
                'status_pembayaran' => 'pending',
            ]);

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'payment_url' => $paymentUrl,
                'redirect_url' => $paymentUrl,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleNotification(Notification $notification)
    {
        try {
            $orderId = $notification->order_id;
            $transactionStatus = $notification->transaction_status;
            $paymentType = $notification->payment_type;
            $fraudStatus = $notification->fraud_status ?? null;

            $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

            if (!$pesanan) {
                return [
                    'success' => false,
                    'message' => 'Order not found',
                ];
            }

            // Update payment status based on Midtrans notification
            switch ($transactionStatus) {
                case 'capture':
                    if ($paymentType == 'credit_card') {
                        if ($fraudStatus == 'challenge') {
                            $pesanan->update([
                                'status_pembayaran' => 'pending',
                            ]);
                        } else {
                            $pesanan->update([
                                'status_pembayaran' => 'paid',
                                'payment_method' => $paymentType,
                                'paid_at' => now(),
                            ]);
                        }
                    }
                    break;

                case 'settlement':
                    $pesanan->update([
                        'status_pembayaran' => 'paid',
                        'payment_method' => $paymentType,
                        'paid_at' => now(),
                    ]);
                    break;

                case 'pending':
                    $pesanan->update([
                        'status_pembayaran' => 'pending',
                    ]);
                    break;

                case 'deny':
                case 'expire':
                case 'cancel':
                    $pesanan->update([
                        'status_pembayaran' => $transactionStatus,
                    ]);
                    break;
            }

            return [
                'success' => true,
                'message' => 'Notification processed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkPaymentStatus($orderId)
    {
        try {
            $status = \Midtrans\Transaction::status($orderId);

            return [
                'success' => true,
                'data' => $status,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
