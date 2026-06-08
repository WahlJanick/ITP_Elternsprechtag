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
                --header-start: #1d5cb5;
                --header-end: #15498f;
                --page-bg: #f7fbff;
                --panel-bg: #e7efff;
                --panel-strong: #d7e6ff;
                --panel-stroke: #4d79ce;
                --ink: #122a52;
                --muted: #31548a;
                --heading: #173f7b;
                --accent: #95c11f;
                --shadow: 0 14px 28px rgba(16, 50, 100, 0.14);
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
                color: var(--ink);
                font-family: 'Instrument Sans', sans-serif;
                background:
                    radial-gradient(circle at top, rgba(77, 121, 206, 0.18), transparent 36%),
                    linear-gradient(180deg, #fbfdff 0%, var(--page-bg) 100%);
            }

            .login-shell {
                width: min(100%, 720px);
                display: grid;
                justify-items: center;
                gap: 22px;
                padding: clamp(28px, 6vw, 52px);
                border: 3px solid var(--panel-stroke);
                border-radius: 12px;
                background: var(--panel-bg);
                box-shadow: var(--shadow);
                text-align: center;
            }

            .school-logo {
                height: 58px;
                max-width: 100%;
                padding: 6px;
                border: 1px solid rgba(23, 63, 123, 0.14);
                border-radius: 6px;
                background: white;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                min-height: 28px;
                margin: 0;
                padding: 3px 10px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 60%, white);
                border-radius: 999px;
                background: var(--panel-strong);
                color: var(--muted);
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .welcome {
                margin: 0;
                max-width: 620px;
                color: var(--heading);
                font-family: 'Cormorant Garamond', Georgia, serif;
                font-size: clamp(1.8rem, 4.2vw, 2.9rem);
                font-weight: 700;
                line-height: 1.08;
                letter-spacing: -0.015em;
            }

            .brand {
                margin: -4px 0 0;
                color: var(--header-start);
                font-family: 'Cormorant Garamond', Georgia, serif;
                font-size: clamp(4rem, 12vw, 7rem);
                font-weight: 700;
                font-style: italic;
                line-height: 0.88;
                letter-spacing: -0.055em;
            }

            .brand::after {
                content: '';
                display: block;
                width: 68%;
                height: 4px;
                margin: 14px auto 0;
                border-radius: 999px;
                background: linear-gradient(90deg, var(--accent), var(--header-start));
            }

            .login-area {
                width: min(100%, 410px);
                display: grid;
                gap: 10px;
                padding: 16px;
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 64%, white);
                border-radius: 10px;
                background: rgba(255, 255, 255, 0.48);
            }

            .login-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 12px;
                width: 100%;
                min-height: 50px;
                padding: 0 16px;
                border: 2px solid var(--header-end);
                border-radius: 8px;
                background: linear-gradient(180deg, var(--header-start), var(--header-end));
                color: white;
                text-decoration: none;
                font-size: 0.98rem;
                font-weight: 800;
                box-shadow: 0 8px 16px rgba(21, 73, 143, 0.2);
                transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            }

            .login-button:hover,
            .login-button:focus-visible {
                transform: translateY(-1px);
                box-shadow: 0 11px 20px rgba(21, 73, 143, 0.26);
                filter: saturate(1.08);
                outline: none;
            }

            .login-button .arrow {
                width: 18px;
                height: 18px;
                margin-left: auto;
            }

            .microsoft-logo {
                width: 22px;
                height: 22px;
                flex: 0 0 22px;
                padding: 2px;
                border-radius: 3px;
                background: white;
            }

            .login-hint {
                margin: 0;
                color: var(--muted);
                font-size: 0.82rem;
                line-height: 1.45;
            }

            .error {
                width: min(100%, 520px);
                padding: 12px 14px;
                border: 2px solid #ff6b60;
                border-radius: 8px;
                background: #fff0ee;
                color: #8a241c;
                font-weight: 700;
            }

            @media (max-width: 520px) {
                .login-shell {
                    padding: 28px 18px;
                }
            }
        </style>
    </head>
    <body>
        <main class="login-shell">
            <img
                src="<?php echo e(asset('images/Logo_HTLWaidhofen_std_fbg_rgb_web.png')); ?>"
                alt="HTL Waidhofen"
                class="school-logo"
            >

            <p class="eyebrow">Elternsprechtag digital</p>

            <h1 class="welcome">
                Willkommen auf der Plattform für Elternsprechtags-<br>
                Terminreservierung
            </h1>

            <p class="brand">PRESS.</p>

            <div class="login-area">
                <a href="<?php echo e(route('auth.azure')); ?>" class="login-button">
                    <svg class="microsoft-logo" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#f25022" d="M1 1h10v10H1z"/>
                        <path fill="#7fba00" d="M13 1h10v10H13z"/>
                        <path fill="#00a4ef" d="M1 13h10v10H1z"/>
                        <path fill="#ffb900" d="M13 13h10v10H13z"/>
                    </svg>
                    <span>Mit Azure anmelden</span>
                    <svg class="arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="m13 6 6 6-6 6"></path>
                    </svg>
                </a>
                <p class="login-hint">Anmeldung mit dem Microsoft-Schulkonto.</p>
            </div>

            <?php if(session('error')): ?>
                <div class="error"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
        </main>
    </body>
</html>
<?php /**PATH C:\3AHIT\ITP\PRESS_stand11052026\PRESS._Elternsprechtag_Projekt\laravel_resSys_Backup\resources\views/anmelden.blade.php ENDPATH**/ ?>