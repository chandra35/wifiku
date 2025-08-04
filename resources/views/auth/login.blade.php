<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - WIFIKU RTRW NET Management</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f0f23;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Sophisticated animated background */
        .background-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .gradient-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 25%, #2d2d5f 50%, #1a1a3e 75%, #0f0f23 100%);
            animation: gradientShift 8s ease-in-out infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Elegant floating orbs */
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.1), rgba(255, 215, 0, 0.1));
            animation: float 6s ease-in-out infinite;
            filter: blur(1px);
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
            animation-delay: 0s;
            background: linear-gradient(135deg, rgba(219, 39, 119, 0.1), rgba(147, 51, 234, 0.1));
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -200px;
            animation-delay: 2s;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1));
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            bottom: -125px;
            left: -125px;
            animation-delay: 4s;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(59, 130, 246, 0.1));
        }

        .orb-4 {
            width: 350px;
            height: 350px;
            bottom: -175px;
            right: -175px;
            animation-delay: 1s;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(219, 39, 119, 0.1));
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(30px, -30px) rotate(120deg); }
            66% { transform: translate(-20px, 20px) rotate(240deg); }
        }

        /* Geometric network lines */
        .network-lines {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
        }

        .line {
            position: absolute;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: lineMove 4s linear infinite;
        }

        .line-1 {
            width: 100%;
            height: 1px;
            top: 20%;
            animation-delay: 0s;
        }

        .line-2 {
            width: 1px;
            height: 100%;
            left: 70%;
            animation-delay: 1s;
            animation: lineMove2 4s linear infinite;
        }

        .line-3 {
            width: 100%;
            height: 1px;
            bottom: 30%;
            animation-delay: 2s;
        }

        @keyframes lineMove {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes lineMove2 {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(100%); }
        }

        /* Main container */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1000px;
            width: 100%;
            min-height: 600px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.4);
            position: relative;
        }

        /* Left panel - branding */
        .brand-panel {
            background: linear-gradient(135deg, rgba(147, 51, 234, 0.9), rgba(79, 70, 229, 0.9));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3Ccircle cx='30' cy='30' r='12'/%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            opacity: 0.3;
        }

        .brand-content {
            text-align: center;
            color: white;
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
            backdrop-filter: blur(10px);
        }

        .brand-logo::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.3), transparent);
            border-radius: 50%;
            z-index: -1;
        }

        .brand-logo i {
            font-size: 3.5rem;
            color: white;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
        }

        .brand-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ffffff, #e5e7eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .brand-subtitle {
            font-size: 1.1rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .brand-features {
            list-style: none;
            text-align: left;
        }

        .brand-features li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .brand-features i {
            margin-right: 0.75rem;
            width: 16px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Right panel - login form */
        .form-panel {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
        }

        .form-header {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 600;
            color: white;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            font-weight: 300;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(147, 51, 234, 0.5);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.1rem;
            margin-top: 12px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
        }

        .remember-me input {
            margin-right: 0.5rem;
            accent-color: #9333ea;
        }

        .forgot-link {
            color: #a855f7;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #c084fc;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #9333ea, #7c3aed);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(147, 51, 234, 0.4);
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
        }

        /* Responsive design */
        @media (max-width: 968px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
            
            .brand-panel {
                padding: 2rem;
                min-height: auto;
            }
            
            .brand-logo {
                width: 80px;
                height: 80px;
            }
            
            .brand-logo i {
                font-size: 2.5rem;
            }
            
            .brand-title {
                font-size: 2rem;
            }
            
            .brand-features {
                display: none;
            }
            
            .form-panel {
                padding: 2rem;
            }
        }

        @media (max-width: 480px) {
            .login-page {
                padding: 10px;
            }
            
            .login-container {
                border-radius: 16px;
            }
            
            .form-panel {
                padding: 1.5rem;
            }
            
            .brand-panel {
                padding: 1.5rem;
            }
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            margin: auto;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Entrance animation */
        .login-container {
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
</head>

<body>
    <!-- Sophisticated animated background -->
    <div class="background-container">
        <div class="gradient-bg"></div>
        <div class="floating-orb orb-1"></div>
        <div class="floating-orb orb-2"></div>
        <div class="floating-orb orb-3"></div>
        <div class="floating-orb orb-4"></div>
        <div class="network-lines">
            <div class="line line-1"></div>
            <div class="line line-2"></div>
            <div class="line line-3"></div>
        </div>
    </div>

    <div class="login-page">
        <div class="login-container">
            <!-- Brand Panel -->
            <div class="brand-panel">
                <div class="brand-content">
                    <div class="brand-logo">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h1 class="brand-title">WIFIKU</h1>
                    <p class="brand-subtitle">Advanced RTRW Network Management System</p>
                    <ul class="brand-features">
                        <li><i class="fas fa-shield-alt"></i> Secure Network Administration</li>
                        <li><i class="fas fa-chart-line"></i> Real-time Monitoring</li>
                        <li><i class="fas fa-users"></i> User Management</li>
                        <li><i class="fas fa-cog"></i> Advanced Configuration</li>
                    </ul>
                </div>
            </div>

            <!-- Form Panel -->
            <div class="form-panel">
                <div class="form-header">
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Please sign in to access your network dashboard</p>
                </div>

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Display validation errors -->
                    @if ($errors->any())
                        <div class="alert">
                            <strong>Authentication Failed:</strong>
                            <ul style="margin: 0.5rem 0 0 0; list-style: none;">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div style="position: relative;">
                            <input type="email" 
                                   name="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your email address" 
                                   autocomplete="email" 
                                   autofocus 
                                   required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div style="position: relative;">
                            <input type="password" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Enter your password" 
                                   autocomplete="current-password" 
                                   required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="form-options">
                        <div class="remember-me">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="btn-login" id="loginBtn">
                        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                        Access Network
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            
            loginForm.addEventListener('submit', function() {
                loginBtn.classList.add('loading');
                loginBtn.innerHTML = '<span>Connecting...</span>';
            });

            // Enhanced input interactions
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.form-group').style.transform = 'translateY(-2px)';
                    this.closest('.form-group').style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.closest('.form-group').style.transform = 'translateY(0)';
                });
            });

            // Parallax effect for floating orbs
            document.addEventListener('mousemove', function(e) {
                const orbs = document.querySelectorAll('.floating-orb');
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                
                orbs.forEach((orb, index) => {
                    const speed = (index + 1) * 0.5;
                    const xPos = x * speed * 10;
                    const yPos = y * speed * 10;
                    orb.style.transform = `translate(${xPos}px, ${yPos}px)`;
                });
            });
        });
    </script>
</body>
</html>
