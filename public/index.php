<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termux Social Chat</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div id="authOverlay">
        <div class="auth-box">
            <h3 id="authTitle">Login to Chat</h3>
            <input type="email" id="authEmail" placeholder="Email Address">
            <input type="text" id="authUsername" placeholder="Choose Username" style="display: none;">
            <input type="password" id="authPassword" placeholder="Password">
            <div id="otpWrapper" style="display: none;">
                <div class="otp-boxes">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                    <input type="text" class="otp-digit" maxlength="1" inputmode="numeric">
                </div>
            </div>
            <button id="authBtn" onclick="handleAuth()">Login</button>
            <div class="toggle-text" id="authToggle" onclick="toggleAuthMode()">Don't have an account? Register</div>
            <div class="toggle-text" id="forgotToggle" style="color: #0084ff; font-size: 0.85rem; margin-top: -5px;" onclick="switchToForgotMode()">Forgot Password?</div>
        </div>
    </div>

    <h2>
        <span>📱 Room: <span id="headerUser">Chat</span></span>
        <button id="logoutBtn" onclick="logout()">Logout</button>
    </h2>
    <div id="chatbox"></div>
    <div class="input-area">
        <input type="text" id="msgInput" placeholder="Type a message..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()">Send</button>
    </div>

    <script src="app.js"></script>
</body>
</html>
