<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ERP System</title>
    
    <!-- Favicon - Using Davao logo -->
    <link rel="icon" type="image/png" href="{{ asset('images/davao.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/davao.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-light: rgba(37, 99, 235, 0.1);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
            --bg-white: #ffffff;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
        }
        
        body {
            min-height: 100vh;
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-light);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .auth-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Left Panel - Login Form */
        .login-panel {
            flex: 0 0 42%;
            background: var(--bg-white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 5rem 6rem;
            position: relative;
            box-shadow: 8px 0 32px rgba(0, 0, 0, 0.06);
        }
        
        .login-content {
            max-width: 440px;
            margin: 0 auto;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Welcome Section */
        .welcome-section {
            margin-bottom: 3.5rem;
        }
        
        .welcome-section h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.03em;
            line-height: 1.2;
            background: linear-gradient(135deg, var(--text-primary) 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-section p {
            font-size: 1.0625rem;
            color: var(--text-secondary);
            margin: 0;
            font-weight: 400;
            line-height: 1.6;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            display: block;
            letter-spacing: 0.01em;
        }
        
        .input-group-custom {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1.125rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.125rem;
            pointer-events: none;
            transition: all 0.25s ease;
            z-index: 1;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 1rem 1.125rem 1rem 3.25rem;
            font-size: 0.9375rem;
            color: var(--text-primary);
            background: var(--bg-light);
            border: 2px solid var(--border-color);
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
            font-weight: 400;
        }
        
        .form-control-custom:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }
        
        .form-control-custom:focus {
            background: var(--bg-white);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px var(--primary-light), var(--shadow-md);
            transform: translateY(-1px);
        }
        
        .form-control-custom:focus + .input-icon {
            color: var(--primary-color);
            transform: translateY(-50%) scale(1.1);
        }
        
        .form-control-custom:not(:placeholder-shown) + .input-icon {
            color: var(--primary-color);
        }
        
        .form-control-custom::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        
        /* Password Toggle */
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.125rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.25s ease;
            z-index: 10;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            color: var(--primary-color);
            background: var(--primary-light);
        }
        
        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        .form-control-custom.password-field {
            padding-right: 3.5rem;
        }
        
        /* Buttons */
        .btn-primary-custom {
            width: 100%;
            padding: 1.125rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background: var(--primary-color);
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 0.75rem;
            margin-bottom: 2rem;
            letter-spacing: 0.01em;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
        }
        
        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transform: translate(-50%, -50%);
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), height 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-primary-custom:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
        }
        
        .btn-primary-custom:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn-primary-custom:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary-custom:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-primary-custom span {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary-custom.loading span::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Credentials Hint */
        .credentials-hint {
            margin-top: 2.5rem;
            padding: 1.125rem 1.5rem;
            background: linear-gradient(135deg, #f1f5f9 0%, #f8fafc 100%);
            border-radius: 14px;
            font-size: 0.8125rem;
            color: #475569;
            text-align: center;
            border: 1.5px solid var(--border-color);
            transition: all 0.25s ease;
        }
        
        .credentials-hint:hover {
            border-color: #cbd5e1;
            box-shadow: var(--shadow-sm);
        }
        
        .credentials-hint i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            font-size: 0.9375rem;
        }
        
        /* Error Alert */
        .alert-custom {
            padding: 1.125rem 1.5rem;
            border-radius: 14px;
            margin-bottom: 2rem;
            font-size: 0.875rem;
            background: #fef2f2;
            border: 2px solid #fecaca;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        
        .alert-custom i {
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        /* Right Panel - Visual */
        .visual-panel {
            flex: 0 0 58%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5rem;
        }
        
        .visual-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 12% 20%, rgba(37, 99, 235, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 88% 80%, rgba(59, 130, 246, 0.2) 0%, transparent 50%);
            animation: gradientShift 20s ease infinite;
            z-index: 0;
        }
        
        @keyframes gradientShift {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.85;
                transform: scale(1.05);
            }
        }
        
        .visual-content {
            position: relative;
            z-index: 1;
            text-align: center;
            max-width: 560px;
            animation: fadeInRight 0.8s ease-out;
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Logo on Right Panel */
        .logo-section-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 2rem 0;
        }
        
        .logo-wrapper-right {
            width: 180px;
            height: 180px;
            margin: 0 auto 2.5rem;
            border-radius: 32px;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5), 
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset,
                        0 8px 32px rgba(37, 99, 235, 0.3);
            backdrop-filter: blur(20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .logo-wrapper-right::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.4), rgba(59, 130, 246, 0.2));
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        
        .logo-wrapper-right:hover::before {
            opacity: 1;
        }
        
        .logo-wrapper-right:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6), 
                        0 0 0 1px rgba(255, 255, 255, 0.2) inset,
                        0 12px 40px rgba(37, 99, 235, 0.4);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .logo-wrapper-right img {
            width: 100%;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.5));
            transition: filter 0.3s ease;
        }
        
        .logo-wrapper-right:hover img {
            filter: drop-shadow(0 12px 28px rgba(0,0,0,0.6));
        }
        
        .company-info-right {
            text-align: center;
            max-width: 480px;
        }
        
        .company-info-right h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 0.75rem 0;
            line-height: 1.2;
            letter-spacing: -0.03em;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.5),
                         0 2px 8px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .company-info-right p {
            font-size: 1.125rem;
            color: rgba(255, 255, 255, 0.95);
            margin: 0 0 1.5rem 0;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }
        
        .company-tagline {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin: 0 auto;
            font-weight: 400;
            font-style: italic;
            letter-spacing: 0.04em;
            line-height: 1.7;
            max-width: 420px;
            padding: 1.5rem 2rem;
            border-top: 2px solid rgba(255, 255, 255, 0.25);
            border-bottom: 2px solid rgba(255, 255, 255, 0.25);
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: fadeInUp 0.8s ease-out 0.4s both;
            transition: all 0.3s ease;
        }
        
        .company-tagline:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3),
                        inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Floating Elements */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        
        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(37, 99, 235, 0.15);
            animation: float 30s infinite ease-in-out;
            filter: blur(50px);
        }
        
        .floating-circle:nth-child(1) {
            width: 350px;
            height: 350px;
            top: 5%;
            left: 5%;
            animation-delay: 0s;
        }
        
        .floating-circle:nth-child(2) {
            width: 280px;
            height: 280px;
            bottom: 8%;
            right: 8%;
            animation-delay: 10s;
            background: rgba(59, 130, 246, 0.12);
        }
        
        .floating-circle:nth-child(3) {
            width: 220px;
            height: 220px;
            top: 40%;
            right: 5%;
            animation-delay: 20s;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.5;
            }
            33% {
                transform: translate(50px, -50px) scale(1.15);
                opacity: 0.7;
            }
            66% {
                transform: translate(-40px, 40px) scale(0.9);
                opacity: 0.6;
            }
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .login-panel {
                flex: 0 0 45%;
                padding: 4rem 5rem;
            }
            
            .visual-panel {
                flex: 0 0 55%;
            }
        }
        
        @media (max-width: 992px) {
            .auth-container {
                flex-direction: column;
            }
            
            .login-panel {
                flex: 1;
                padding: 3.5rem 2.5rem;
                box-shadow: none;
            }
            
            .visual-panel {
                flex: 1;
                min-height: 400px;
                padding: 4rem 2.5rem;
            }
            
            .logo-wrapper-right {
                width: 140px;
                height: 140px;
                margin-bottom: 2rem;
            }
            
            .company-info-right h2 {
                font-size: 2rem;
            }
            
            .company-info-right p {
                font-size: 1rem;
            }
            
            .company-tagline {
                font-size: 0.9375rem;
                max-width: 380px;
                padding: 1.25rem 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-panel {
                padding: 2.5rem 1.5rem;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .logo-wrapper-right {
                width: 120px;
                height: 120px;
                margin-bottom: 1.75rem;
            }
            
            .company-info-right h2 {
                font-size: 1.75rem;
            }
            
            .company-info-right p {
                font-size: 0.9375rem;
            }
            
            .company-tagline {
                font-size: 0.875rem;
                max-width: 320px;
                padding: 1rem 1.25rem;
            }
            
            .visual-panel {
                padding: 3rem 1.5rem;
                min-height: 350px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Panel - Login Form -->
        <div class="login-panel">
            <div class="login-content">
                <!-- Welcome Section -->
                <div class="welcome-section">
                    <h1>Welcome!</h1>
                    <p>Please sign in to your account</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="alert-custom">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login.post') }}" id="loginForm">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Email address</label>
                        <div class="input-group-custom">
                            <input 
                                type="email" 
                                name="email" 
                                id="email"
                                class="form-control-custom" 
                                placeholder="Enter your email"
                                value="{{ old('email') }}"
                                required 
                                autofocus
                            >
                            <i class="bi bi-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-group-custom">
                            <input 
                                type="password" 
                                name="password" 
                                id="password"
                                class="form-control-custom password-field" 
                                placeholder="Enter your password"
                                required
                            >
                            <i class="bi bi-lock input-icon"></i>
                            <button 
                                type="button" 
                                class="password-toggle" 
                                id="passwordToggle"
                                aria-label="Toggle password visibility"
                            >
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-custom" id="submitBtn">
                        <span>Sign in</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Panel - Visual -->
        <div class="visual-panel">
            <div class="floating-elements">
                <div class="floating-circle"></div>
                <div class="floating-circle"></div>
                <div class="floating-circle"></div>
            </div>
            
            <div class="visual-content">
                <div class="logo-section-right">
                    <div class="logo-wrapper-right">
                        <img src="{{ asset('images/davao.png') }}" alt="Davao Modern Glass">
                    </div>
                    <div class="company-info-right">
                        <h2>Davao Modern Glass</h2>
                        <p>Aluminum Supply Corp.</p>
                        <div class="company-tagline">
                            Quality Glass & Aluminum Solutions for Modern Construction
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');
        
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    passwordIcon.classList.remove('bi-eye-slash');
                    passwordIcon.classList.add('bi-eye');
                } else {
                    passwordIcon.classList.remove('bi-eye');
                    passwordIcon.classList.add('bi-eye-slash');
                }
            });
        }
        
        // Form submission with better loading state
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        
        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function(e) {
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                submitBtn.querySelector('span').textContent = 'Signing in';
            });
        }
        
        // Input validation feedback
        const inputs = document.querySelectorAll('.form-control-custom');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && this.checkValidity()) {
                    this.style.borderColor = '#10b981';
                }
            });
            
            input.addEventListener('input', function() {
                if (this.style.borderColor === 'rgb(16, 185, 129)') {
                    this.style.borderColor = '';
                }
            });
        });
    </script>
</body>
</html>
