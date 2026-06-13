<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termux Social Chat</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, sans-serif; background: #0f0f12; color: #e4e6eb; padding: 15px; display: flex; flex-direction: column; height: 100vh; }
        h2 { font-size: 1.2rem; text-align: center; padding: 10px 0; color: #0084ff; border-bottom: 1px solid #222; display: flex; justify-content: space-between; align-items: center; }
        #logoutBtn { font-size: 0.8rem; background: #3a3b3c; border-radius: 12px; padding: 4px 10px; }
        #chatbox { flex: 1; overflow-y: auto; padding: 15px; background: #16161a; border-radius: 8px; margin: 15px 0; display: flex; flex-direction: column; gap: 12px; }
        .bubble { max-width: 75%; padding: 10px 14px; border-radius: 18px; font-size: 0.95rem; line-height: 1.4; word-wrap: break-word; }
        .bubble.me { background: #0084ff; color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
        .bubble.them { background: #3a3b3c; color: #e4e6eb; align-self: flex-start; border-bottom-left-radius: 4px; }
        .meta { font-size: 0.7rem; opacity: 0.6; margin-top: 4px; display: block; }
        .me .meta { text-align: right; color: #d0e7ff; }
        .them .meta { text-align: left; color: #b0b3b8; }
        .input-area { display: flex; gap: 8px; padding-bottom: 10px; }
        input { flex: 1; padding: 12px; background: #242526; color: #fff; border: 1px solid #3a3b3c; border-radius: 20px; font-size: 1rem; outline: none; }
        button { background: #0084ff; color: white; border: none; border-radius: 20px; padding: 0 20px; font-size: 1rem; font-weight: bold; cursor: pointer; }
        
        /* Modal overlay popup layout for auth */
        #authOverlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #0f0f12; display: flex; flex-direction: column; justify-content: center; padding: 30px; z-index: 100; }
        .auth-box { background: #16161a; padding: 20px; border-radius: 12px; border: 1px solid #222; display: flex; flex-direction: column; gap: 15px; }
        .auth-box h3 { text-align: center; color: #0084ff; }
        .auth-box input { border-radius: 8px; }
        .auth-box button { padding: 12px; border-radius: 8px; }
        .toggle-text { text-align: center; font-size: 0.9rem; color: #aaa; cursor: pointer; text-decoration: underline; }
    </style>
</head>
<body>

    <!-- Auth screen form block overlay overlay -->
    <div id="authOverlay">
        <div class="auth-box">
            <h3 id="authTitle">Login to Chat</h3>
            <input type="text" id="authUsername" placeholder="Username">
            <input type="password" id="authPassword" placeholder="Password">
            <button id="authBtn" onclick="handleAuth()">Login</button>
            <div class="toggle-text" id="authToggle" onclick="toggleAuthMode()">Don't have an account? Register</div>
        </div>
    </div>

    <!-- Active Main Application interface container view -->
    <h2>
        <span>📱 Room: <span id="headerUser">Chat</span></span>
        <button id="logoutBtn" onclick="logout()">Logout</button>
    </h2>
    <div id="chatbox"></div>
    <div class="input-area">
        <input type="text" id="msgInput" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()">Send</button>
    </div>

    <script>
        let currentUser = null;
        let authMode = 'login';

        function toggleAuthMode() {
            const title = document.getElementById('authTitle');
            const btn = document.getElementById('authBtn');
            const toggle = document.getElementById('authToggle');
            
            if (authMode === 'login') {
                authMode = 'register';
                title.innerText = 'Create Account';
                btn.innerText = 'Register';
                toggle.innerText = 'Already have an account? Login';
            } else {
                authMode = 'login';
                title.innerText = 'Login to Chat';
                btn.innerText = 'Login';
                toggle.innerText = "Don't have an account? Register";
            }
        }

        async function checkSession() {
            try {
                const res = await fetch('auth-api.php?action=check');
                const data = await res.json();
                if (data.logged_in) {
                    currentUser = data;
                    document.getElementById('headerUser').innerText = currentUser.username;
                    document.getElementById('authOverlay').style.display = 'none';
                    fetchMessages();
                } else {
                    document.getElementById('authOverlay').style.display = 'flex';
                }
            } catch (err) { console.error(err); }
        }

        async function handleAuth() {
            const userIn = document.getElementById('authUsername').value.trim();
            const passIn = document.getElementById('authPassword').value.trim();
            if (!userIn || !passIn) return alert('Fill in all fields.');

            try {
                const res = await fetch(`auth-api.php?action=${authMode}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: userIn, password: passIn })
                });
                const data = await res.json();
                
                if (!res.ok) return alert(data.message);
                
                if (authMode === 'register') {
                    alert('Registration complete! Logging in...');
                    authMode = 'login';
                    toggleAuthMode();
                }
                
                checkSession();
            } catch (err) { console.error(err); }
        }

        async function logout() {
            await fetch('auth-api.php?action=logout');
            location.reload();
        }

        async function fetchMessages() {
            if (!currentUser) return;
            try {
                const res = await fetch('chat-api.php');
                if (!res.ok) return;
                const data = await res.json();
                const chatbox = document.getElementById('chatbox');
                const shouldScroll = chatbox.scrollTop + chatbox.clientHeight >= chatbox.scrollHeight - 50;

                chatbox.innerHTML = data.map(m => {
                    const isMe = (m.sender_id == currentUser.user_id);
                    return `
                        <div class="bubble ${isMe ? 'me' : 'them'}">
                            <div>${m.message}</div>
                            <span class="meta">${isMe ? 'You' : 'User ' + m.sender_id} • ${m.created_at.split(' ')[1]}</span>
                        </div>
                    `;
                }).join('');

                if (shouldScroll) chatbox.scrollTop = chatbox.scrollHeight;
            } catch (err) { console.error(err); }
        }

        async function sendMessage() {
            const input = document.getElementById('msgInput');
            const text = input.value.trim();
            if(!text) return;
            input.value = '';
            
            try {
                await fetch('chat-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                fetchMessages();
            } catch (err) { console.error(err); }
        }

        // Initialize active profile parameters verification instantly
        checkSession();
        setInterval(fetchMessages, 2000);
    </script>
</body>
</html>
