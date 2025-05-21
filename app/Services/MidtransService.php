<?php
namespace App\Services;

use App\Models\Pesanan;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Pesanan $pesanan)
    {
        // Use the correct relationship name 'barber' instead of 'tukangCukur'
        $barber = $pesanan->barber;
        $pelanggan = $pesanan->pelanggan;

        if (!$barber) {
            throw new \Exception('Barber not found for this order');
        }

        if (!$pelanggan) {
            throw new \Exception('Customer not found for this order');
        }

        $orderId = $pesanan->id_transaksi;
        $grossAmount = $pesanan->getTotalAmount();

        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
        ];

        $customerDetails = [
            'first_name' => $pelanggan->nama,
            'email' => $pesanan->email,
            'phone' => $pesanan->telepon,
            'billing_address' => [
                'address' => $pesanan->alamat_lengkap,
            ],
        ];

        $itemDetails = [
            [
                'id' => 'barber-' . $barber->id,
                'price' => $pesanan->nominal,
                'quantity' => 1,
                'name' => 'Jasa Tukang Cukur: ' . $barber->nama,
            ],
            [
                'id' => 'shipping',
                'price' => $pesanan->ongkos_kirim,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ],
        ];

        $transactionData = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails,
        ];

        try {
            // Create Snap payment page
            $snapToken = Snap::getSnapToken($transactionData);
            $snapUrl = Snap::getSnapUrl($transactionData);

            // Update pesanan with payment information
            $pesanan->update([
                'payment_token' => $snapToken,
                'payment_url' => $snapUrl,
            ]);

            return [
                'token' => $snapToken,
                'redirect_url' => $snapUrl,
            ];
        } catch (\Exception $e) {
            // Handle exceptions
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function handleNotification($notification)
    {
        $orderId = $notification->order_id;
        $statusCode = $notification->status_code;
        $paymentType = $notification->payment_type;

        $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

        if (!$pesanan) {
            return [
                'success' => false,
                'message' => 'Order not found'
            ];
        }

        // Update pesanan based on notification status
        switch ($statusCode) {
            case '200':
                $pesanan->update([
                    'status_pembayaran' => 'paid',
                    'payment_method' => $paymentType,
                    'paid_at' => now(),
                ]);
                break;
            case '201':
                $pesanan->update(['status_pembayaran' => 'pending']);
                break;
            case '202':
                $pesanan->update(['status_pembayaran' => 'failed']);
                break;
            default:
                $pesanan->update(['status_pembayaran' => 'failed']);
                break;
        }

        return [
            'success' => true,
            'message' => 'Notification processed'
        ];
    }
}
