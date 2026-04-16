<?php
/**
 * ADMIN LOGIN PAGE
 * Biblioteca Obscura - The Keeper's Chamber
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_authenticated'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$correct_password = 'biblioteca2024'; // Change this to your desired password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === $correct_password) {
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['login_time'] = time();
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Incorrect password. Access denied.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Keeper's Chamber - Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            height: 100%;
            font-family: 'Cormorant Garamond', serif;
            overflow: hidden;
        }

        body {
            background: linear-gradient(135deg, #1C1B1A 0%, #2A2926 50%, #1C1B1A 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Ambient particles */
        .particles {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 1px;
            height: 1px;
            background: rgba(194, 163, 93, 0.3);
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        /* Login container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 60px 40px;
            background: rgba(60, 47, 47, 0.8);
            border: 2px solid #C2A35D;
            border-radius: 8px;
            text-align: center;
            box-shadow: 
                0 0 60px rgba(194, 163, 93, 0.2),
                inset 0 0 60px rgba(194, 163, 93, 0.1);
            backdrop-filter: blur(10px);
        }

        .chamber-seal {
            font-size: 60px;
            margin-bottom: 20px;
            animation: sealPulse 3s ease-in-out infinite;
        }

        @keyframes sealPulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.8;
            }
            50% {
                transform: scale(1.1);
                opacity: 1;
            }
        }

        .chamber-title {
            font-size: 2.5rem;
            color: #C2A35D;
            letter-spacing: 4px;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
            font-weight: 300;
        }

        .chamber-subtitle {
            font-size: 0.95rem;
            color: #8B7D75;
            letter-spacing: 2px;
            margin-bottom: 40px;
            font-style: italic;
        }

        .divider {
            width: 80px;
            height: 1px;
            background: linear-gradient(90deg, transparent, #C2A35D, transparent);
            margin: 30px auto;
        }

        /* Form */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            text-align: left;
        }

        label {
            display: block;
            color: #C2A35D;
            font-size: 0.95rem;
            letter-spacing: 2px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        input[type="password"] {
            width: 100%;
            padding: 15px 15px;
            background: rgba(28, 27, 26, 0.8);
            border: 1px solid #C2A35D;
            border-radius: 4px;
            color: #E2D3B7;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1rem;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            outline: none;
        }

        input[type="password"]:focus {
            background: rgba(28, 27, 26, 0.95);
            box-shadow: 0 0 20px rgba(194, 163, 93, 0.3);
            border-color: #E2D3B7;
        }

        input[type="password"]::placeholder {
            color: #8B7D75;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 15px;
            background: linear-gradient(135deg, #C2A35D, #8B7D75);
            color: #1C1B1A;
            border: none;
            border-radius: 4px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(194, 163, 93, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .back-link {
            margin-top: 25px;
        }

        .back-link a {
            color: #8B7D75;
            text-decoration: none;
            font-size: 0.9rem;
            letter-spacing: 1px;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #C2A35D;
        }

        /* Error message */
        .error-message {
            background: rgba(255, 70, 70, 0.1);
            border: 1px solid #ff4646;
            color: #ff9999;
            padding: 15px;
            border-radius: 4px;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-bottom: 20px;
            animation: errorShake 0.5s ease;
        }

        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Glow background */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(
                circle at 50% 30%,
                rgba(194, 163, 93, 0.08),
                transparent 70%
            );
            pointer-events: none;
            z-index: 0;
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="chamber-seal">◆</div>
        
        <h1 class="chamber-title">The Keeper's Chamber</h1>
        <p class="chamber-subtitle">Portal to the Archive Management System</p>
        
        <div class="divider"></div>

        <?php if ($error): ?>
            <div class="error-message">
                ◆ <?= htmlspecialchars($error) ?> ◆
            </div>
        <?php endif; ?>

        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="password">Secret Phrase</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter the password to proceed..."
                    required
                    autofocus
                >
            </div>

            <div class="button-group">
                <button type="submit">Access Chamber</button>
            </div>
        </form>

        <div class="back-link">
            <a href="../index.php">← Return to Library</a>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 5 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                container.appendChild(particle);
            }
        }

        createParticles();

        // Focus effect
        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('focus', () => {
            passwordInput.style.boxShadow = '0 0 30px rgba(194, 163, 93, 0.5)';
        });
        passwordInput.addEventListener('blur', () => {
            passwordInput.style.boxShadow = 'none';
        });
    </script>
</body>
</html>
