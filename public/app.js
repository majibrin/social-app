let currentUser = null;
let authMode = 'login';

function setLoading(state) {
    const btn = document.getElementById('authBtn');
    if (state) {
        btn.disabled = true;
        btn.dataset.label = btn.innerText;
        btn.innerHTML = '<span class="btn-spinner"></span>' + btn.dataset.label;
    } else {
        btn.disabled = false;
        btn.innerText = btn.dataset.label;
    }
}

function getOtpValue() {
    return Array.from(document.querySelectorAll('.otp-digit')).map(i => i.value).join('');
}

function clearOtp() {
    document.querySelectorAll('.otp-digit').forEach(i => i.value = '');
}

function initOtpInputs() {
    const digits = document.querySelectorAll('.otp-digit');
    digits.forEach((input, idx) => {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(-1);
            if (input.value && idx < digits.length - 1) digits[idx + 1].focus();
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) digits[idx - 1].focus();
        });
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
            digits.forEach((d, i) => d.value = text[i] || '');
            const next = Math.min(text.length, digits.length - 1);
            digits[next].focus();
        });
    });
}

function toggleAuthMode() {
    const title = document.getElementById('authTitle');
    const btn = document.getElementById('authBtn');
    const toggle = document.getElementById('authToggle');
    const forgotToggle = document.getElementById('forgotToggle');
    const userField = document.getElementById('authUsername');
    const emailField = document.getElementById('authEmail');

    if (authMode === 'login') {
        authMode = 'register';
        title.innerText = 'Create Account';
        emailField.placeholder = 'Your Email Address';
        userField.style.display = 'block';
        btn.innerText = 'Register';
        toggle.innerText = 'Already have an account? Login';
        forgotToggle.style.display = 'none';
    } else {
        authMode = 'login';
        title.innerText = 'Login to Chat';
        emailField.placeholder = 'Email Address';
        userField.style.display = 'none';
        btn.innerText = 'Login';
        toggle.innerText = "Don't have an account? Register";
        forgotToggle.style.display = 'block';
    }
}

function switchToForgotMode() {
    authMode = 'forgot_request';
    document.getElementById('authTitle').innerText = 'Reset Password';
    document.getElementById('authEmail').placeholder = 'Enter Account Email';
    document.getElementById('authUsername').style.display = 'none';
    document.getElementById('authPassword').style.display = 'none';
    document.getElementById('otpWrapper').style.display = 'none';
    document.getElementById('authBtn').innerText = 'Send Reset Code';
    document.getElementById('authToggle').innerText = 'Back to Login';
    document.getElementById('authToggle').setAttribute('onclick', 'resetToLoginMode()');
    document.getElementById('forgotToggle').style.display = 'none';
}

function resetToLoginMode() {
    authMode = 'login';
    window._resetEmail = null;
    document.getElementById('authTitle').innerText = 'Login to Chat';
    document.getElementById('authEmail').style.display = 'block';
    document.getElementById('authEmail').placeholder = 'Email Address';
    document.getElementById('authEmail').value = '';
    document.getElementById('authUsername').style.display = 'none';
    document.getElementById('authPassword').style.display = 'block';
    document.getElementById('authPassword').value = '';
    document.getElementById('authPassword').placeholder = 'Password';
    document.getElementById('otpWrapper').style.display = 'none';
    clearOtp();
    document.getElementById('authBtn').innerText = 'Login';
    document.getElementById('authToggle').innerText = "Don't have an account? Register";
    document.getElementById('authToggle').setAttribute('onclick', 'toggleAuthMode()');
    document.getElementById('forgotToggle').style.display = 'block';
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
    const emailIn = document.getElementById('authEmail').value.trim();
    const userIn = document.getElementById('authUsername').value.trim();
    const passIn = document.getElementById('authPassword').value.trim();
    const codeIn = getOtpValue();

    if (authMode !== 'forgot_verify' && authMode !== 'forgot_new_password' && !emailIn) return alert('Email address is required.');
    if (authMode === 'register' && !userIn) return alert('Username is required.');
    if ((authMode === 'login' || authMode === 'register' || authMode === 'forgot_new_password') && !passIn) {
        return alert('Password is required.');
    }

    try {
        setLoading(true);

        if (authMode === 'forgot_request') {
            const res = await fetch('auth-api.php?action=forgot_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: emailIn })
            });
            const data = await res.json();
            alert(data.message);

            if (res.ok) {
                window._resetEmail = emailIn;
                authMode = 'forgot_verify';
                document.getElementById('authEmail').style.display = 'none';
                document.getElementById('otpWrapper').style.display = 'block';
                document.getElementById('authBtn').innerText = 'Verify Reset Code';
                document.querySelectorAll('.otp-digit')[0].focus();
            }
            setLoading(false);
            return;
        }

        if (authMode === 'forgot_verify') {
            if (codeIn.length < 6) { setLoading(false); return alert('Enter all 6 digits.'); }
            const res = await fetch('auth-api.php?action=verify_code', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: window._resetEmail, token: codeIn })
            });
            const data = await res.json();
            alert(data.message);

            if (res.ok) {
                authMode = 'forgot_new_password';
                document.getElementById('otpWrapper').style.display = 'none';
                clearOtp();
                document.getElementById('authPassword').style.display = 'block';
                document.getElementById('authPassword').placeholder = 'Enter New Password';
                document.getElementById('authBtn').innerText = 'Save Password';
            }
            setLoading(false);
            return;
        }

        if (authMode === 'forgot_new_password') {
            const res = await fetch('auth-api.php?action=reset_password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: window._resetEmail, password: passIn })
            });
            const data = await res.json();
            alert(data.message);

            if (res.ok) resetToLoginMode();
            setLoading(false);
            return;
        }

        const payload = { email: emailIn, password: passIn };
        if (authMode === 'register') payload.username = userIn;

        const res = await fetch(`auth-api.php?action=${authMode}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        setLoading(false);

        if (!res.ok) return alert(data.message);

        if (authMode === 'register') {
            alert('Registration complete! Logging in...');
            resetToLoginMode();
            return;
        }

        checkSession();
    } catch (err) {
        setLoading(false);
        console.error(err);
    }
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
            const displayName = isMe ? 'You' : (m.username || 'User ' + m.sender_id);
            const timestamp = m.created_at ? (m.created_at.split(' ')[1] || m.created_at) : '';
            return `
                <div class="bubble ${isMe ? 'me' : 'them'}">
                    <div>${m.message}</div>
                    <span class="meta">${displayName} • ${timestamp}</span>
                </div>
            `;
        }).join('');

        if (shouldScroll) chatbox.scrollTop = chatbox.scrollHeight;
    } catch (err) { console.error(err); }
}

async function sendMessage() {
    const input = document.getElementById('msgInput');
    const text = input.value.trim();
    if (!text) return;
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

initOtpInputs();
checkSession();
setInterval(fetchMessages, 2000);
