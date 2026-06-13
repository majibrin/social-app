<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termux Social Chat</title>
    <!-- Connect External Modular CSS Layout Canvas -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Auth screen form block overlay overlay -->
    <div id="authOverlay">
        <div class="auth-box">
            <h3 id="authTitle">Login to Chat</h3>
            <input type="email" id="authEmail" placeholder="Email Address">
            <input type="text" id="authUsername" placeholder="Choose Username" style="display: none;">
            <input type="password" id="authPassword" placeholder="Password">
            <input type="text" id="authCode" placeholder="6-Digit Reset Code" style="display: none;">
            <button id="authBtn" onclick="handleAuth()">Login</button>
            <div class="toggle-text" id="authToggle" onclick="toggleAuthMode()">Don't have an account? Register</div>
            <div class="toggle-text" id="forgotToggle" style="color: #0084ff; font-size: 0.85rem; margin-top: -5px;" onclick="switchToForgotMode()">Forgot Password?</div>
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

    <!-- Connect External Modular Javascript Engine -->
    <script src="app.js"></script>
</body>
</html>
