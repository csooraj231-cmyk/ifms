<?php
/**
 * IFMS - Login Page
 * Routes users to their role-based dashboard after authentication
 */
require_once __DIR__ . '/config/auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'admin')
        header('Location: /ifms/admin/');
    elseif ($role === 'employee')
        header('Location: /ifms/employee/');
    elseif ($role === 'client')
        header('Location: /ifms/client/');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IFMS � Login</title>
    <meta name="description" content="IFMS Infrastructure Management Software - Secure Login Portal">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .input-field {
            border: none;
            border-bottom: 2px solid #e0e0e0;
            padding: 12px 0;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-bottom-color: #667eea;
        }
        .input-field::placeholder {
            color: #999;
        }
    </style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo & Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">IFMS</h1>
            <p class="text-gray-600 text-sm mt-2">Infrastructure Management Software</p>
        </div>

        <!-- Login Form -->
        <div class="login-container p-8">
            <div id="login-section">
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-white">Welcome back</h2>
                    <p class="text-slate-400 text-sm mt-1 font-medium">Sign in to continue to your dashboard</p>
                </div>

                <!-- Error Alert -->
                <div id="login-error" class="hidden mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="login-error-text" class="font-semibold"></span>
                </div>

                <!-- Login Form -->
                <form id="login-form" class="space-y-6">
                    <div>
                        <label for="email" class="block text-xs font-black text-slate-300 uppercase tracking-widest mb-2 px-1">Email Address</label>
                        <div class="relative border-b-2 border-white/10 group focus-within:border-indigo-500 transition-all">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="email" id="email" name="email" required
                                class="w-full pl-8 pr-4 py-4 bg-white text-black font- placeholder-slate-500 focus:outline-none transition-all"
                                placeholder="you@company.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2 px-1">
                            <label for="password" class="block text-xs font-black text-slate-300 uppercase tracking-widest">Password</label>
                            <button type="button" onclick="showForgotPassword()" class="text-[10px] font-black text-indigo-400 hover:text-indigo-300 uppercase tracking-widest transition-colors">Forgot Password?</button>
                        </div>
                        <div class="relative border-b-2 border-white/10 group focus-within:border-indigo-500 transition-all">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none transition-colors group-focus-within:text-indigo-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-8 pr-12 py-4 bg-white text-black font- placeholder-slate-500 focus:outline-none transition-all"
                                placeholder="Enter your password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <svg id="eye-icon" class="w-5 h-5 text-slate-500 hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="login-btn"
                        class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-2xl font-black text-sm tracking-[0.1em] uppercase transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl hover:shadow-indigo-500/40 active:scale-[0.98] flex items-center justify-center gap-2">
                        <span>Authenticate</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- Demo Credentials -->
                <div class="mt-8 pt-8 border-t border-white/5">
                    <p class="text-[10px] font-black text-slate-500 text-center mb-4 tracking-[0.2em] uppercase">Quick Access Portal</p>
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="quickLogin('admin@ifms.com','admin123')" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-[10px] font-black text-slate-400 hover:text-white transition-all border border-white/5 hover:border-white/20 uppercase tracking-widest">
                            Admin
                        </button>
                        <button onclick="quickLogin('hr@ifms.com','emp123')" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-[10px] font-black text-slate-400 hover:text-white transition-all border border-white/5 hover:border-white/20 uppercase tracking-widest">
                            HR Dept
                        </button>
                        <button onclick="quickLogin('dev@ifms.com','emp123')" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-[10px] font-black text-slate-400 hover:text-white transition-all border border-white/5 hover:border-white/20 uppercase tracking-widest">
                            Developer
                        </button>
                        <button onclick="quickLogin('client@techcorp.com','client123')" class="px-4 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-[10px] font-black text-slate-400 hover:text-white transition-all border border-white/5 hover:border-white/20 uppercase tracking-widest">
                            Client Portal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Forgot Password Section (Hidden by default) -->
            <div id="forgot-password-section" class="hidden">
                <div class="mb-8">
                    <h2 class="text-2xl font-black text-white">Reset Password</h2>
                    <p class="text-slate-400 text-sm mt-1 font-medium">Enter your email for the reset link</p>
                </div>

                <div id="reset-msg" class="hidden mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-bold"></div>

                <form id="forgot-password-form" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-slate-300 uppercase tracking-widest mb-2 px-1">Registered Email</label>
                        <div class="relative border-b-2 border-white/10 group focus-within:border-indigo-500 transition-all">
                            <input type="email" name="email" required
                                class="w-full py-4 bg-transparent text-white font-bold placeholder-slate-500 focus:outline-none transition-all"
                                placeholder="name@company.com">
                        </div>
                    </div>

                    <button type="submit" id="reset-btn"
                        class="w-full py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-sm tracking-[0.1em] uppercase transition-all shadow-xl shadow-indigo-500/20 flex items-center justify-center gap-2">
                        <span>Send Token</span>
                    </button>
                    
                    <button type="button" onclick="showLogin()" class="w-full text-center text-[10px] font-black text-slate-500 hover:text-slate-300 uppercase tracking-widest pt-4">Back to Login</button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-600 text-[10px] font-black uppercase tracking-[0.2em] mt-8">� 2026 IFMS � Infrastructure Management</p>
    </div>

    <script src="/ifms/assets/js/app.js"></script>
    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
        }

        function showForgotPassword() {
            document.getElementById('login-section').classList.add('hidden');
            document.getElementById('forgot-password-section').classList.remove('hidden');
        }

        function showLogin() {
            document.getElementById('forgot-password-section').classList.add('hidden');
            document.getElementById('login-section').classList.remove('hidden');
        }

        function quickLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            document.getElementById('login-form').dispatchEvent(new Event('submit'));
        }

        // Login Handler
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('login-btn');
            const errBox = document.getElementById('login-error');
            const btnText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<svg class="inline-block animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> <span>Verifying...</span>';
            errBox.classList.add('hidden');

            try {
                const res = await fetch('/ifms/api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'login',
                        email: document.getElementById('email').value,
                        password: document.getElementById('password').value
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Logged in successfully!');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    errBox.classList.remove('hidden');
                    document.getElementById('login-error-text').textContent = data.message;
                    btn.disabled = false;
                    btn.innerHTML = btnText;
                }
            } catch (err) {
                errBox.classList.remove('hidden');
                document.getElementById('login-error-text').textContent = 'Connection error. Please try again.';
                btn.disabled = false;
                btn.innerHTML = btnText;
            }
        });

        // Forgot Password Handler
        document.getElementById('forgot-password-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('reset-btn');
            const msgBox = document.getElementById('reset-msg');
            const email = e.target.email.value;
            const btnText = btn.textContent;

            btn.disabled = true;
            btn.textContent = 'Sending...';

            try {
                const res = await fetch('/ifms/api/password-reset.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'send_token', email })
                });
                const data = await res.json();
                msgBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'border-red-200');
                msgBox.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                msgBox.innerHTML = '✓ ' + data.message;
                
                if(data.debug_token) {
                    const newPass = prompt("Enter new password:", "");
                    if(newPass) {
                        const resetRes = await fetch('/ifms/api/password-reset.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'reset_password', token: data.debug_token, password: newPass })
                        });
                        const resetData = await resetRes.json();
                        alert(resetData.message);
                        if(resetData.success) showLogin();
                    }
                }
            } catch(err) {
                msgBox.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgBox.innerHTML = '✗ Error processing request.';
            }
            btn.disabled = false;
            btn.textContent = btnText;
        });
    </script>
</body>
</html>