@extends('layouts.app')

@section('content')
<div class="container">
    <div id="chat-messages" class="chat-box">
        <!-- Pesan akan muncul di sini -->
    </div>

    <form id="message-form">
        <input type="text" id="message-input" placeholder="Ketik pesan...">
        <button type="submit">Kirim</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen untuk pesan baru
    Echo.channel('chat')
        .listen('MessageSent', (e) => {
            const chatBox = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';
            messageDiv.innerHTML = `
                <strong>${e.user_name}:</strong>
                ${e.message}
                <small>(${new Date(e.timestamp).toLocaleTimeString()})</small>
            `;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto scroll
        });

    // Kirim pesan
    document.getElementById('message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value;

        if (message.trim()) {
            // Kirim ke server via AJAX
            fetch('/send-message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: message })
            });

            messageInput.value = '';
        }
    });
});
</script>
@endsection
