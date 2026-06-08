<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PRESS. Elternsprechtag</title>
        <link rel="icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>">
        <link rel="shortcut icon" href="<?php echo e(asset('favicon.png')); ?>">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:500,600,700|instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root {
                color-scheme: light;
                --blue: #1558a6;
                --blue-dark: #0e376d;
                --ink: #102d55;
                --muted: #61738c;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                padding: 24px;
                overflow-x: hidden;
                color: var(--ink);
                font-family: 'Instrument Sans', sans-serif;
                background:
                    radial-gradient(circle at 12% 12%, rgba(149, 193, 31, 0.2), transparent 27%),
                    radial-gradient(circle at 88% 82%, rgba(21, 88, 166, 0.2), transparent 30%),
                    linear-gradient(145deg, #fdfefe 0%, #edf4fc 52%, #f6f9ee 100%);
            }

            body::before,
            body::after {
                content: '';
                position: fixed;
                width: 320px;
                height: 320px;
                border: 1px solid rgba(21, 88, 166, 0.12);
                border-radius: 50%;
                pointer-events: none;
            }

            body::before {
                left: -150px;
                bottom: -120px;
            }

            body::after {
                right: -170px;
                top: -130px;
            }

            .login-shell {
                width: min(100%, 760px);
                position: relative;
                display: grid;
                justify-items: center;
                gap: 28px;
                padding: clamp(34px, 7vw, 70px);
                border: 1px solid rgba(255, 255, 255, 0.82);
                border-radius: 30px;
                background: rgba(255, 255, 255, 0.72);
                box-shadow: 0 30px 80px rgba(16, 45, 85, 0.16);
                backdrop-filter: blur(18px);
                text-align: center;
            }

            .welcome {
                margin: 0;
                max-width: 650px;
                color: var(--blue-dark);
                font-family: 'Cormorant Garamond', Georgia, serif;
                font-size: clamp(1.8rem, 4.5vw, 3.2rem);
                font-weight: 600;
                line-height: 1.05;
                letter-spacing: -0.02em;
            }

            .brand {
                margin: -8px 0 0;
                color: var(--blue);
                font-family: 'Cormorant Garamond', Georgia, serif;
                font-size: clamp(4.4rem, 13vw, 8rem);
                font-weight: 700;
                font-style: italic;
                line-height: 0.85;
                letter-spacing: -0.055em;
            }

            .brand::after {
                content: '';
                display: block;
                width: 74%;
                height: 5px;
                margin: 16px auto 0;
                border-radius: 999px;
                background: linear-gradient(90deg, #95c11f, #1558a6);
            }

            .login-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 14px;
                width: min(100%, 340px);
                min-height: 58px;
                padding: 0 24px;
                border: 2px solid #0f4f96;
                border-radius: 12px;
                background: linear-gradient(180deg, #1765bd, #124f96);
                color: white;
                text-decoration: none;
                font-size: 1.02rem;
                font-weight: 700;
                box-shadow: 0 14px 28px rgba(21, 88, 166, 0.22);
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }

            .login-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 18px 34px rgba(21, 88, 166, 0.28);
            }

            .microsoft-logo {
                width: 24px;
                height: 24px;
                flex: 0 0 24px;
            }

            .error {
                width: min(100%, 520px);
                padding: 12px 14px;
                border: 2px solid #df6d63;
                border-radius: 10px;
                background: #fff0ee;
                color: #8a241c;
                font-weight: 700;
            }

            @media (max-width: 520px) {
                .login-shell {
                    padding: 38px 22px;
                    border-radius: 22px;
                }
            }
        </style>
    </head>
    <body>
        <main class="login-shell">
            <h1 class="welcome">
                Willkommen auf der Plattform für Elternsprechtags-<br>
                Terminreservierung
            </h1>

            <p class="brand">PRESS.</p>

            <a href="<?php echo e(url('/auth/azure')); ?>" class="login-button">
                <svg class="microsoft-logo" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#f25022" d="M1 1h10v10H1z"/>
                    <path fill="#7fba00" d="M13 1h10v10H13z"/>
                    <path fill="#00a4ef" d="M1 13h10v10H1z"/>
                    <path fill="#ffb900" d="M13 13h10v10H13z"/>
                </svg>
                Mit Azure anmelden
            </a>

            <?php if(session('error')): ?>
                <div class="error"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
        </main>
    </body>
</html>
<?php /**PATH /var/www/resources/views/anmelden.blade.php ENDPATH**/ ?>