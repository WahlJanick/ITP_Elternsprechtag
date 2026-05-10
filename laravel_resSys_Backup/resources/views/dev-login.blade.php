<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entwickler-Login - Elternsprechtag</title>
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

        .student-inputs {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
            text-align: left;
            width: 100%;
        }

        .student-inputs input {
            padding: 8px 12px;
            border: 2px solid #4d79ce;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .role-btn {
            flex-direction: column;
            padding: 20px;
        }

        .role-btn > *:not(.student-inputs):not(.role-student > .student-inputs) {
            width: 100%;
        }

        .role-btn select,
        .role-btn input[type="text"] {
            padding: 10px 14px;
            border: 2px solid #4d79ce;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            background: white;
            margin-bottom: 8px;
        }

        .role-btn button {
            margin-top: 10px;
            width: 100%;
        }

        .back-link {
            display: block;
            margin-top: 20px;
            color: #4d79ce;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="dev-login-box">
        <h1>Entwickler-Login</h1>
        <div style="position: absolute; top: 20px; right: 20px; font-weight: 700; font-size: 1.2rem; color: #173f7b;" id="student-initials"></div>

        <div class="role-grid">
            <div class="role-btn role-student">
                <span>Als Schüler einloggen</span>
                <div class="student-inputs">
                    <input type="text" id="student-firstname" placeholder="Vorname" />
                    <input type="text" id="student-lastname" placeholder="Nachname" />
                    <input type="text" id="student-class" placeholder="Klasse" value="3AHIT" />
                </div>
                <form method="POST" action="{{ route('dev.login.student') }}">
                    @csrf
                    <input type="hidden" name="firstname" id="input-firstname" />
                    <input type="hidden" name="lastname" id="input-lastname" />
                    <input type="hidden" name="class" id="input-class" />
                    <button type="submit">Einloggen</button>
                </form>
            </div>

            <div class="role-btn role-teacher">
                <span>Als Lehrer einloggen</span>
                <select id="teacher-select">
                    <option value="">Lehrer wählen...</option>
                    @foreach(\App\Models\Teacher::all() as $teacher)
                        <option value="{{ $teacher->teacher_id }}">{{ $teacher->full_name }} ({{ $teacher->kuerzel }})</option>
                    @endforeach
                </select>
                <form method="POST" action="{{ route('dev.login.teacher') }}">
                    @csrf
                    <input type="hidden" name="teacher_id" id="input-teacher-id" />
                    <button type="submit">Einloggen</button>
                </form>
            </div>

            <a href="{{ route('dev.login.admin') }}" class="role-btn role-admin">
                <span>Als Admin einloggen</span>
            </a>
        </div>

        <a href="{{ route('login') }}" class="back-link">Zurück zur normalen Anmeldung</a>

        <script>
            // Student form
            function updateStudentInputs() {
                const first = document.getElementById('student-firstname').value || '';
                const last = document.getElementById('student-lastname').value || '';
                const cls = document.getElementById('student-class').value || '3AHIT';
                document.getElementById('input-firstname').value = first;
                document.getElementById('input-lastname').value = last;
                document.getElementById('input-class').value = cls;

                // Update initials display
                const initials = (first.charAt(0) + last.charAt(0)).toUpperCase();
                document.getElementById('student-initials').textContent = initials;
            }

            // Teacher form
            document.getElementById('teacher-select').addEventListener('change', function() {
                document.getElementById('input-teacher-id').value = this.value;
            });

            // Initialize
            updateStudentInputs();
            document.getElementById('student-firstname').addEventListener('input', updateStudentInputs);
            document.getElementById('student-lastname').addEventListener('input', updateStudentInputs);
            document.getElementById('student-class').addEventListener('input', updateStudentInputs);
        </script>
    </div>
</body>
</html>
