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

// finish
// PembayaranController.php
public function finish(Request $request, $order_id = null)
{
    $orderId = $request->get('order_id');
        if (!$orderId) {
        return redirect()->route('pesanan.index')->with('error', 'Order ID tidak ditemukan');
    }
    $statusCode = $request->get('status_code');
    $transactionStatus = $request->get('transaction_status');

    if (!$orderId) {
        return redirect()->route('pesanan.index')->with('error', 'Order ID tidak ditemukan');
    }

    $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

    if (!$pesanan) {
        return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
    }

    // Optional: Update status berdasarkan callback
    if ($transactionStatus === 'settlement') {
        $pesanan->update([
            'status_pembayaran' => 'paid',
            'paid_at' => now(),
        ]);
    }

    return view('pembayaran.finish', compact('pesanan'));
}

// unfinish
public function unfinish(Request $request)
{
    $orderId = $request->order_id;
    $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

    if (!$pesanan) {
        return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
    }

    return view('pembayaran.unfinish', compact('pesanan'));
}

// error
public function error(Request $request)
{
    $orderId = $request->order_id;
    $pesanan = Pesanan::where('id_transaksi', $orderId)->first();

    if (!$pesanan) {
        return redirect()->route('pesanan.index')->with('error', 'Pesanan tidak ditemukan');
    }

    $message = $request->get('message', 'Terjadi kesalahan saat memproses pembayaran.');

    return view('pembayaran.error', compact('pesanan', 'message'));
}

}
