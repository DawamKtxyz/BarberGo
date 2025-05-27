// Listen untuk chat messages
Echo.channel('chat')
    .listen('MessageSent', (e) => {
        console.log('Pesan baru:', e.message);

        // Tambahkan pesan ke chat box
        const chatBox = document.getElementById('chat-messages');
        const newMessage = document.createElement('div');
        newMessage.innerHTML = `<p><strong>${e.user}:</strong> ${e.message}</p>`;
        chatBox.appendChild(newMessage);
    });
