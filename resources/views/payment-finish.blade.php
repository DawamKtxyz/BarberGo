?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-size: 48px; margin: 20px 0; }
        .details { margin: 20px 0; color: #333; }
        .button {
            background: #007bff; color: white; padding: 12px 24px;
            text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;
        }
        .order-id { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success">✓</div>
        <h1>Payment Successful!</h1>
        <div class="details">
            <p>Your payment has been processed successfully.</p>
            <div class="order-id">
                <strong>Order ID:</strong> {{ $orderId ?? 'N/A' }}<br>
                <strong>Status:</strong> {{ $transactionStatus ?? 'Success' }}
            </div>
        </div>
        <p>You can now close this page and return to the app.</p>
        <a href="#" onclick="window.close();" class="button">Close Window</a>
    </div>
    <script>
        // Auto close after 10 seconds
        setTimeout(function() {
            window.close();
        }, 10000);

        // Try to communicate with mobile app if possible
        if (window.flutter_inappwebview) {
            window.flutter_inappwebview.callHandler('paymentSuccess', {
                orderId: '{{ $orderId }}',
                status: '{{ $transactionStatus }}'
            });
        }
    </script>
</body>
</html>
