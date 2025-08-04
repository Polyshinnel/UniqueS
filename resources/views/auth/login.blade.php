<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация - Unique S</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 50px;
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo {
            margin-bottom: 40px;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 700;
            color: #133E71;
            margin-bottom: 10px;
        }

        .logo-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .login-form {
            text-align: left;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #133E71;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus {
            outline: none;
            border-color: #133E71;
            background: white;
            box-shadow: 0 0 0 3px rgba(19, 62, 113, 0.1);
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 5px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #133E71;
            background: rgba(19, 62, 113, 0.1);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .login-button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #133E71, #1C5BA4);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(19, 62, 113, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: #c53030;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c53030;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .remember-me input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
            accent-color: #133E71;
        }

        .remember-me label {
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px;
            }
            
            .logo-text {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-text">Unique S</div>
            <div class="logo-subtitle">Система управления</div>
        </div>

        <form class="login-form" method="POST" action="{{ route('login') }}">
            @csrf

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-input" 
                    value="{{ old('email') }}" 
                    required 
                    autocomplete="email"
                    placeholder="Введите ваш email"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <div class="password-field">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required 
                        autocomplete="current-password"
                        placeholder="Введите ваш пароль"
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg id="eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" checked>
                <label for="remember">Запомнить меня на полгода</label>
            </div>

            <button type="submit" class="login-button">
                Войти в систему
            </button>
        </form>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }
    </script>
</body>
</html> 