<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HtlWY-Elternsprechtag</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 24px;
                padding-top: max(24px, 10vh);
                background:
                    radial-gradient(circle at top, rgba(29, 92, 181, 0.16), transparent 35%),
                    linear-gradient(180deg, #fbfdff 0%, #eef4ff 100%);
                font-family: 'Instrument Sans', sans-serif;
                color: #163869;
            }

            .login-box {
                width: min(100%, 460px);
                background: rgba(255, 255, 255, 0.9);
                border: 3px solid #4d79ce;
                border-radius: 18px;
                padding: 28px;
                box-shadow: 0 18px 40px rgba(20, 56, 106, 0.14);
            }

            h1 {
                margin: 0 0 10px;
                font-size: 2rem;
                text-align: center;
            }

            p {
                margin: 0 0 14px;
                color: #4a6693;
                line-height: 1.6;
            }

            .login-btn {
                display: inline-flex;
                width: 100%;
                justify-content: center;
                align-items: center;
                min-height: 48px;
                margin-top: 10px;
                border-radius: 10px;
                background: #0078d4;
                color: white;
                text-decoration: none;
                font-weight: 700;
            }

            .hint-box {
                margin-top: 18px;
                padding: 14px;
                border-radius: 12px;
                background: #edf4ff;
                border: 2px solid #b9cdf0;
            }

            .hint-title {
                display: block;
                margin-bottom: 8px;
                font-weight: 700;
                color: #173f7b;
            }

            .error {
                margin-top: 14px;
                padding: 12px 14px;
                border-radius: 10px;
                background: #ffe7e4;
                border: 2px solid #ff847b;
                color: #8a160f;
                font-weight: 700;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>Anmeldung</h1>

            <a href="{{ url('/auth/azure') }}" class="login-btn">
                Mit Microsoft anmelden
            </a>


            @if(session('error'))
                <div class="error">{{ session('error') }}</div>
            @endif
        </div>
    </body>
</html>
