<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dev Login - Elternsprechtag</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(29, 92, 181, 0.16), transparent 35%),
                linear-gradient(180deg, #fbfdff 0%, #eef4ff 100%);
            font-family: 'Instrument Sans', sans-serif;
            color: #163869;
        }

        .dev-login-box {
            width: min(100%, 400px);
            background: rgba(255, 255, 255, 0.9);
            border: 3px solid #4d79ce;
            border-radius: 18px;
            padding: 32px;
            box-shadow: 0 18px 40px rgba(20, 56, 106, 0.14);
            text-align: center;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.8rem;
            color: #173f7b;
        }

        .subtitle {
            color: #4a6693;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }

        .warning {
            background: #fff0b3;
            border: 2px solid #ffd866;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 24px;
            font-size: 0.85rem;
            color: #8a6d00;
            font-weight: 600;
        }

        .role-grid {
            display: grid;
            gap: 12px;
        }

        .role-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 20px;
            border-radius: 10px;
            border: 2px solid;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .role-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        }

        .role-student {
            background: #a8d62b;
            color: #153964;
            border-color: #8fc21f;
        }

        .role-teacher {
            background: #3f78e2;
            color: white;
            border-color: #2d5fc4;
        }

        .role-admin {
            background: #ff6b60;
            color: white;
            border-color: #e55a4e;
        }

        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: #4d79ce;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .icon {
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <div class="dev-login-box">
        <h1>Dev Login</h1>
        <p class="subtitle">Schneller Login für Entwicklung & Demos</p>

        <div class="warning">
            ⚠️ Nur für lokale Entwicklung!
        </div>

        <div class="role-grid">
            <a href="{{ route('dev.login.student') }}" class="role-btn role-student">
                <span class="icon">🎓</span>
                Als Student einloggen
            </a>

            <a href="{{ route('dev.login.teacher') }}" class="role-btn role-teacher">
                <span class="icon">👨‍🏫</span>
                Als Lehrer einloggen
            </a>

            <a href="{{ route('dev.login.admin') }}" class="role-btn role-admin">
                <span class="icon">⚙️</span>
                Als Admin einloggen
            </a>
        </div>

        <a href="{{ route('login') }}" class="back-link">Zurück zur normalen Anmeldung</a>
    </div>
</body>
</html>
