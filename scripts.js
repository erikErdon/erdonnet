document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const searchIcon = document.getElementById('search-icon');
    const searchBar = document.getElementById('search-bar');
    const searchResults = document.getElementById('search-results');
    const sideNav = document.getElementById('side-nav');
    const hamburgerMenu = document.getElementById('hamburger-menu');
    const header = document.querySelector('.header');
    const mainContainer = document.querySelector('.main-container');
    const unreadCountSpan = document.getElementById('unread-count');

    // **1. Toggle Dark/Light Theme**
    themeToggle?.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });

    // Load theme from localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    // **2. Expand/Collapse Search Bar**
    searchIcon?.addEventListener('click', (event) => {
        event.stopPropagation(); // Prevent click from propagating to document
        searchBar.classList.toggle('active');
        if (searchBar.classList.contains('active')) {
            searchBar.focus(); // Focus the search bar when it's expanded
        }
    });

    // Hide the search bar when clicking outside of it on mobile devices
    document.addEventListener('click', (event) => {
        if (!searchBar.contains(event.target) && !searchIcon.contains(event.target)) {
            searchBar.classList.remove('active');
        }
    });

    // **3. Toggle Side Navigation**
    hamburgerMenu?.addEventListener('click', () => {
        sideNav.classList.toggle('open');
        mainContainer.classList.toggle('shifted');
    });

    // Close side nav if clicking outside of it
    document.addEventListener('click', (event) => {
        if (!sideNav.contains(event.target) && !hamburgerMenu.contains(event.target)) {
            sideNav.classList.remove('open');
            mainContainer.classList.remove('shifted');
        }
    });

    // Adjust search bar visibility based on screen size
    const handleResize = () => {
        if (window.innerWidth > 768) {
            searchBar.classList.remove('active'); // Reset width on large screens
        }
    };
    window.addEventListener('resize', handleResize);

    // **4. Real-time Search**
    searchBar?.addEventListener('input', function() {
        const query = this.value;

        if (query.length > 2) { // Start searching after 3 characters
            fetch('./search.php?query=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';

                    data.results.forEach(profile => {
                        const resultItem = document.createElement('div');
                        resultItem.className = 'search-result';
                        resultItem.textContent = profile.name;
                        resultItem.onclick = () => {
                            window.location.href = `./profile.php?id=${profile.id}`;
                        };
                        searchResults.appendChild(resultItem);
                    });

                    searchResults.style.display = 'block';
                })
                .catch(error => console.error('Error during search:', error));
        } else {
            searchResults.style.display = 'none';
        }
    });

    // **5. Chat Functionality**
    if (window.location.pathname.includes('chat.php')) {
        const messagesContainer = document.querySelector('.messages');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');
        const receiverId = new URLSearchParams(window.location.search).get('receiver_id');

        // Function to send a message
        function sendMessage() {
            const message = messageInput.value.trim();
            if (message === '') return;
    
            fetch('functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'send_message',
                    message: message,
                    receiver_id: receiverId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    messageInput.value = '';
                    fetchMessages(); // Refresh messages after sending
                } else {
                    alert('Message sending failed!');
                }
            })
            .catch(error => console.error('Error sending message:', error));
        }

        // Function to fetch messages
        function fetchMessages() {
            fetch('functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'get_messages',
                    receiver_id: receiverId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    messagesContainer.innerHTML = '';
                    result.messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = msg.sender_id == receiverId ? 'message received' : 'message sent';
                        messageDiv.innerHTML = `
                            <strong>${msg.username}</strong>
                            <p>${msg.message}</p>
                            <div class="message-time">${new Date(msg.timestamp).toLocaleTimeString()}</div>
                        `;
                        messagesContainer.appendChild(messageDiv);
                    });
                    scrollToBottom(); // Scroll to the bottom after loading messages
                } else {
                    console.error('Failed to fetch messages:', result.message);
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
        }

        function markMessagesAsRead() {
            fetch('functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'mark_messages_as_read',
                    receiver_id: receiverId
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    console.log('Messages marked as read.');
                } else {
                    console.error('Failed to mark messages as read:', result.message);
                }
            })
            .catch(error => console.error('Error marking messages as read:', error));
        }

        function updateUnreadCount() {
            fetch('functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'get_unread_count'
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    unreadCountSpan.textContent = result.unread_count;
                } else {
                    console.error('Failed to fetch unread count:', result.message);
                }
            })
            .catch(error => console.error('Error fetching unread count:', error));
        }

        // Function to scroll to the bottom of the messages container
        function scrollToBottom() {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        // Initialize chat view
        function initializeChat() {
            messagesContainer.innerHTML = ''; // Clear existing messages
            fetchMessages(); // Fetch and display messages
        }

        if (window.location.pathname.includes('chat.php')) {
            initializeChat();
            markMessagesAsRead(); // Mark all messages as read when entering the chat
    
            setInterval(fetchMessages, 5000); // Refresh every 5 seconds
            setInterval(updateUnreadCount, 5000); // Update unread count every 5 seconds
        }

        // Add event listener to send button
        sendButton?.addEventListener('click', sendMessage);

        // Initialize the chat
        initializeChat();

        // Optionally, you can set up an interval to periodically refresh messages
        setInterval(fetchMessages, 5000); // Refresh every 5 seconds
        setInterval(updateUnreadCount, 5000); //
        // Periodically update unread count every 5 seconds
    }

});
