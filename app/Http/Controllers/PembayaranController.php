<?php
namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Midtrans\Notification;

class PembayaranController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
        // Apply middleware except for notification route
        $this->middleware('admin')->except(['notification', 'finish', 'unfinish', 'error']);
    }

    // Method untuk menampilkan halaman pembayaran
    public function show(Pesanan $pesanan)
    {
        return view('pembayaran.show', compact('pesanan'));
    }

    // Method untuk memproses pembayaran
    public function process(Pesanan $pesanan)
    {
        // Generate Midtrans payment
        $result = $this->midtransService->createTransaction($pesanan);

        if (isset($result['error'])) {
            return redirect()->back()->with('error', 'Gagal membuat transaksi pembayaran: ' . $result['message']);
        }

        // Redirect to payment page
        return redirect($result['redirect_url']);
    }

    // Method untuk menerima notifikasi pembayaran dari Midtrans
    public function notification(Request $request)
    {
        try {
            $notification = new Notification();
            $result = $this->midtransService->handleNotification($notification);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method untuk handling saat pembayaran selesai
    public function finish(Request $request)
    {
        $orderId = $request->order_id;
        $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
        }

        return redirect()->route('pesanan.show', $pesanan->id)->with('success', 'Pembayaran berhasil diproses');
    }

    // Method untuk handling saat pembayaran tidak selesai
    public function unfinish(Request $request)
    {
        $orderId = $request->order_id;
        $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
        }

        return redirect()->route('pesanan.show', $pesanan->id)->with('warning', 'Pembayaran belum selesai');
    }

    // Method untuk handling saat pembayaran error
    public function error(Request $request)
    {
        $orderId = $request->order_id;
        $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
        }

        return redirect()->route('pesanan.show', $pesanan->id)->with('error', 'Pembayaran gagal: ' . $request->get('message', 'Terjadi kesalahan'));
    }
}
