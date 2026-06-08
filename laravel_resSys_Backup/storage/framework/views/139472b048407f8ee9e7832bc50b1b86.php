<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entwickler-Login - Elternsprechtag</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>">
    <link rel="shortcut icon" href="<?php echo e(asset('favicon.png')); ?>">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    <style>
        :root {
            --page-bg: #f3f5f7;
            --panel-bg: rgba(255, 255, 255, 0.9);
            --panel-border: #8ea3c1;
            --copy: #2a3a4e;
            --muted: #66758a;
            --student: #b6c5a0;
            --student-border: #9cad86;
            --teacher: #7f9bc2;
            --teacher-border: #6d88ae;
            --admin: #c88f84;
            --admin-border: #b67f75;
            --shadow: 0 24px 48px rgba(20, 56, 106, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(127, 155, 194, 0.16), transparent 28%),
                radial-gradient(circle at bottom right, rgba(182, 197, 160, 0.14), transparent 24%),
                linear-gradient(180deg, #fafdff 0%, var(--page-bg) 100%);
            color: var(--copy);
            font-family: 'Instrument Sans', sans-serif;
        }

        .page-shell {
            width: min(1080px, 100%);
            margin: 28px auto 0;
            display: grid;
            gap: 24px;
            padding: 0 18px 32px;
        }

        .portal-header {
            background: linear-gradient(180deg, #6e89b6 0%, #56709a 100%);
            color: white;
            box-shadow: 0 8px 24px rgba(18, 42, 82, 0.2);
        }

        .header-inner {
            position: relative;
            max-width: 1120px;
            margin: 0 auto;
            padding: 14px 18px 22px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .logo-link {
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-image {
            height: 60px;
            background: white;
            padding: 6px;
            border-radius: 4px;
        }

        .header-copy {
            display: grid;
            gap: 8px;
        }

        .eyebrow {
            display: inline-flex;
            width: fit-content;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.9rem, 4vw, 2.9rem);
            line-height: 1;
            font-weight: 800;
        }

        .hero-copy {
            margin: 0;
            max-width: 720px;
            color: rgba(255, 255, 255, 0.84);
            line-height: 1.6;
        }

        .initial-badge {
            margin-left: auto;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            border: 2px solid rgba(255, 255, 255, 0.18);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        .login-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .login-card {
            background: var(--panel-bg);
            border: 3px solid var(--panel-border);
            border-radius: 22px;
            padding: 22px;
            box-shadow: var(--shadow);
            display: grid;
            gap: 18px;
        }

        .login-card.student {
            border-color: var(--student-border);
            background: linear-gradient(180deg, rgba(207, 232, 122, 0.38), rgba(255, 255, 255, 0.92));
        }

        .login-card.teacher {
            border-color: var(--teacher-border);
            background: linear-gradient(180deg, rgba(63, 120, 226, 0.16), rgba(255, 255, 255, 0.94));
        }

        .login-card.admin {
            border-color: var(--admin-border);
            background: linear-gradient(180deg, rgba(255, 107, 96, 0.16), rgba(255, 255, 255, 0.94));
        }

        .card-head {
            display: grid;
            gap: 8px;
        }

        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 800;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .login-card.student .card-icon {
            background: var(--student);
            color: #21415f;
        }

        .login-card.teacher .card-icon {
            background: var(--teacher);
            color: white;
        }

        .login-card.admin .card-icon {
            background: var(--admin);
            color: white;
        }

        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: #173f7b;
        }

        .card-copy {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
            font-size: 0.95rem;
        }

        .field-stack,
        .actions {
            display: grid;
            gap: 10px;
        }

        input,
        select,
        button,
        .admin-link {
            width: 100%;
            min-height: 46px;
            border-radius: 12px;
            font: inherit;
        }

        input,
        select {
            border: 2px solid rgba(40, 83, 154, 0.24);
            background: rgba(255, 255, 255, 0.96);
            color: var(--copy);
            padding: 0 14px;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #3f78e2;
            box-shadow: 0 0 0 4px rgba(63, 120, 226, 0.12);
        }

        button,
        .admin-link {
            border: 2px solid transparent;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
        }

        button:hover,
        .admin-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(20, 56, 106, 0.14);
            filter: saturate(1.04);
        }

        .student button {
            background: #95c11f;
            color: #153964;
            border-color: #7fa91a;
        }

        .teacher button {
            background: var(--teacher);
            color: white;
            border-color: var(--teacher-border);
        }

        .admin-link {
            background: var(--admin);
            color: white;
            border-color: var(--admin-border);
        }

        .footer-row {
            display: flex;
            justify-content: center;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            padding: 0 18px;
            border-radius: 999px;
            border: 2px solid rgba(40, 83, 154, 0.18);
            background: rgba(255, 255, 255, 0.88);
            color: #2c5aa1;
            text-decoration: none;
            font-weight: 700;
        }

        .back-link:hover {
            box-shadow: 0 10px 20px rgba(20, 56, 106, 0.12);
        }

        @media (max-width: 960px) {
            .login-grid {
                grid-template-columns: 1fr;
            }

            .header-inner {
                align-items: flex-start;
                flex-wrap: wrap;
            }
        }

        @media (max-width: 560px) {
            .page-shell {
                margin-top: 20px;
                padding: 0 12px 24px;
            }

            .initial-badge {
                width: 48px;
                height: 48px;
            }

            .header-inner {
                gap: 12px;
                padding-bottom: 18px;
            }
        }
    </style>
</head>
<body>
    <header class="portal-header">
        <div class="header-inner">
            <a class="logo-link" href="<?php echo e(route('login')); ?>">
                <img src="<?php echo e(asset('images/Logo_HTLWaidhofen_std_fbg_rgb_web.png')); ?>" alt="HTL Waidhofen" class="logo-image">
            </a>

            <div class="header-copy">
                <span class="eyebrow">Entwicklung</span>
                <h1>Dev-Login</h1>
                <p class="hero-copy">
                    Teste Schüler-, Lehrer- und Admin-Flows direkt aus der lokalen Entwicklungsumgebung.
                </p>
            </div>

            <div class="initial-badge" id="student-initials">ST</div>
        </div>
    </header>

    <div class="page-shell">
        <section class="login-grid">
            <article class="login-card student">
                <div class="card-head">
                    <div class="card-icon">S</div>
                    <h2 class="card-title">Schüler-Login</h2>
                    <p class="card-copy">Simuliert einen Schüler mit frei wählbarem Namen und Klasse.</p>
                </div>

                <div class="field-stack">
                    <input type="text" id="student-firstname" placeholder="Vorname">
                    <input type="text" id="student-lastname" placeholder="Nachname">
                    <input type="text" id="student-class" placeholder="Klasse" value="3AHIT">
                </div>

                <form method="POST" action="<?php echo e(route('dev.login.student')); ?>" class="actions">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="firstname" id="input-firstname">
                    <input type="hidden" name="lastname" id="input-lastname">
                    <input type="hidden" name="class" id="input-class">
                    <button type="submit">Als Schüler einloggen</button>
                </form>
            </article>

            <article class="login-card teacher">
                <div class="card-head">
                    <div class="card-icon">L</div>
                    <h2 class="card-title">Lehrer-Login</h2>
                    <p class="card-copy">Wähle einen vorhandenen Lehrer aus und öffne direkt seine Ansicht.</p>
                </div>

                <div class="field-stack">
                    <select id="teacher-select">
                        <option value="">Lehrer wählen...</option>
                        <?php $__currentLoopData = \App\Models\Teacher::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($teacher->teacher_id); ?>"><?php echo e($teacher->full_name); ?> (<?php echo e($teacher->kuerzel); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <form method="POST" action="<?php echo e(route('dev.login.teacher')); ?>" class="actions">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="teacher_id" id="input-teacher-id">
                    <button type="submit">Als Lehrer einloggen</button>
                </form>
            </article>

            <article class="login-card admin">
                <div class="card-head">
                    <div class="card-icon">A</div>
                    <h2 class="card-title">Admin-Login</h2>
                    <p class="card-copy">Öffnet direkt die Verwaltungsansicht für Dashboard, Lehrer und Termine.</p>
                </div>

                <a href="<?php echo e(route('dev.login.admin')); ?>" class="admin-link">Als Admin einloggen</a>
            </article>
        </section>

        <div class="footer-row">
            <a href="<?php echo e(route('login')); ?>" class="back-link">Zurück zur normalen Anmeldung</a>
        </div>
    </div>

    <script>
        function updateStudentInputs() {
            const first = document.getElementById('student-firstname').value || '';
            const last = document.getElementById('student-lastname').value || '';
            const cls = document.getElementById('student-class').value || '3AHIT';
            document.getElementById('input-firstname').value = first;
            document.getElementById('input-lastname').value = last;
            document.getElementById('input-class').value = cls;

            const initials = (first.charAt(0) + last.charAt(0)).toUpperCase();
            document.getElementById('student-initials').textContent = initials || 'ST';
        }

        document.getElementById('teacher-select').addEventListener('change', function () {
            document.getElementById('input-teacher-id').value = this.value;
        });

        updateStudentInputs();
        document.getElementById('student-firstname').addEventListener('input', updateStudentInputs);
        document.getElementById('student-lastname').addEventListener('input', updateStudentInputs);
        document.getElementById('student-class').addEventListener('input', updateStudentInputs);
    </script>
</body>
</html>
<?php /**PATH /var/www/resources/views/dev-login.blade.php ENDPATH**/ ?>