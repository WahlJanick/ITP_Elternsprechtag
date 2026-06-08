<?php
    $displayName = auth()->user()?->name ?? 'Portal-Nutzer';
    $initials = collect(preg_split('/\s+/', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo e($pageTitle ?? 'Reservierungssystem'); ?></title>
        <link rel="icon" type="image/png" href="<?php echo e(asset('favicon.png')); ?>">
        <link rel="shortcut icon" href="<?php echo e(asset('favicon.png')); ?>">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                color-scheme: light;
                --header-start: #1d5cb5;
                --header-end: #15498f;
                --header-text: #d9e8ff;
                --page-bg: #f7fbff;
                --panel-bg: #e7efff;
                --panel-strong: #d7e6ff;
                --panel-stroke: #4d79ce;
                --copy: #122a52;
                --muted: #31548a;
                --heading: #173f7b;
                --eyebrow-color: #2d5797;
                --accent: #95c11f;
                --accent-copy: #12345f;
                --header-link-bg: #d9e5fb;
                --header-link-border: #87a7dd;
                --header-link-copy: #123b74;
                --header-link-active-bg: #ffffff;
                --user-badge-bg: #ffffff;
                --user-badge-copy: #365fa7;
                --danger: #ff5c56;
                --danger-soft: #ffe4e2;
                --warning: #ffd866;
                --warning-soft: #fff0b3;
                --shadow: 0 14px 28px rgba(16, 50, 100, 0.14);
            }

            .theme-student {
                --header-start: #8fa86a;
                --header-end: #738b55;
                --header-text: #f4f8ea;
                --page-bg: #f6f8f1;
                --panel-bg: #edf2e4;
                --panel-strong: #e4ead8;
                --panel-stroke: #9fb186;
                --copy: #31402a;
                --muted: #66775b;
                --heading: #44563a;
                --eyebrow-color: #6c7f5c;
                --accent: #a7ba88;
                --accent-copy: #273421;
                --header-link-bg: #ebf0e1;
                --header-link-border: #bcc8aa;
                --header-link-copy: #43563a;
                --header-link-active-bg: #ffffff;
                --user-badge-bg: #f7faf1;
                --user-badge-copy: #526449;
            }

            .theme-teacher {
                --header-start: #6e89b6;
                --header-end: #56709a;
                --header-text: #edf3fb;
                --page-bg: #f3f6fa;
                --panel-bg: #e7edf6;
                --panel-strong: #dde6f1;
                --panel-stroke: #8ea3c1;
                --copy: #25364d;
                --muted: #5a6d88;
                --heading: #364b67;
                --eyebrow-color: #667b97;
                --accent: #7f9bc2;
                --accent-copy: #ffffff;
                --header-link-bg: #e8eef7;
                --header-link-border: #b5c3d8;
                --header-link-copy: #405572;
                --header-link-active-bg: #ffffff;
                --user-badge-bg: #ffffff;
                --user-badge-copy: #5b7392;
            }

            .theme-admin {
                --header-start: #b78a7f;
                --header-end: #966a60;
                --header-text: #fdf1ee;
                --page-bg: #fbf4f2;
                --panel-bg: #f3e5e1;
                --panel-strong: #ebdad5;
                --panel-stroke: #c7a095;
                --copy: #4d302b;
                --muted: #7a5b55;
                --heading: #6a433c;
                --eyebrow-color: #936e66;
                --accent: #c88f84;
                --accent-copy: #ffffff;
                --header-link-bg: #f7ebe8;
                --header-link-border: #d9bbb4;
                --header-link-copy: #6d4b45;
                --header-link-active-bg: #fff9f8;
                --user-badge-bg: #fff8f6;
                --user-badge-copy: #8a625a;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background:
                    radial-gradient(circle at top, color-mix(in srgb, var(--panel-stroke) 24%, transparent), transparent 36%),
                    linear-gradient(180deg, #fbfdff 0%, var(--page-bg) 100%);
                color: var(--copy);
                font-family: 'Instrument Sans', sans-serif;
            }

            a {
                color: inherit;
            }

            button {
                font: inherit;
                cursor: pointer;
            }

            form {
                margin: 0;
            }

            .portal-header {
                position: sticky;
                top: 0;
                z-index: 950;
                background: linear-gradient(180deg, var(--header-start) 0%, var(--header-end) 100%);
                color: white;
                box-shadow: 0 8px 24px rgba(18, 42, 82, 0.2);
                backdrop-filter: blur(10px);
            }

            .header-inner {
                position: relative;
                max-width: 1120px;
                margin: 0 auto;
                padding: 12px 18px 18px;
                display: flex;
                align-items: center;
                gap: 18px;
            }

            .logo-link {
                text-decoration: none;
            }

            .logo-card {
                width: 62px;
                min-width: 62px;
                border-radius: 6px;
                background: white;
                color: #0d3771;
                padding: 6px 6px 4px;
                box-shadow: inset 0 0 0 1px rgba(13, 55, 113, 0.12);
            }

            .logo-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 3px;
                margin-bottom: 4px;
            }

            .logo-square {
                aspect-ratio: 1;
                border-radius: 2px;
            }

            .logo-square:nth-child(1) { background: #1d5cb5; }
            .logo-square:nth-child(2) { background: #ffcf32; }
            .logo-square:nth-child(3) { background: #27a9e1; }
            .logo-square:nth-child(4) { background: #ff5c56; }

            .logo-text {
                font-size: 9px;
                line-height: 1.05;
                font-weight: 800;
                letter-spacing: 0.03em;
                text-transform: uppercase;
            }

            .header-actions {
                margin-left: auto;
                padding-right: 76px;
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .parent-day-select {
                display: grid;
                grid-template-columns: auto minmax(160px, 1fr) auto;
                align-items: center;
                gap: 8px 10px;
                padding: 6px 10px;
                border-radius: 10px;
                border: 2px solid var(--header-link-border);
                background: rgba(255, 255, 255, 0.16);
                color: var(--header-text);
                font-weight: 600;
            }

            .parent-day-select .parent-day-label {
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }

            .parent-day-select select {
                width: 100%;
                min-width: 160px;
                padding: 6px 10px;
                border-radius: 8px;
                border: 2px solid color-mix(in srgb, var(--header-link-border) 70%, white);
                background: var(--header-link-active-bg);
                color: var(--header-link-copy);
                font-weight: 700;
                appearance: auto;
            }

            .parent-day-status {
                min-height: 18px;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.8rem;
                font-weight: 700;
                color: var(--header-text);
                opacity: 0;
                transform: translateY(-2px);
                transition: opacity 0.18s ease, transform 0.18s ease;
                pointer-events: none;
            }

            .parent-day-select.is-loading .parent-day-status,
            .parent-day-select.is-done .parent-day-status {
                opacity: 1;
                transform: translateY(0);
            }

            .parent-day-spinner,
            .parent-day-check {
                display: none;
                width: 14px;
                height: 14px;
                flex: 0 0 14px;
            }

            .parent-day-select.is-loading .parent-day-spinner {
                display: inline-block;
                border-radius: 999px;
                border: 2px solid rgba(255, 255, 255, 0.35);
                border-top-color: #ffffff;
                animation: parent-day-spin 0.8s linear infinite;
            }

            .parent-day-select.is-done .parent-day-check {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 0.95rem;
                color: #ffffff;
            }

            .parent-day-status-text-loading,
            .parent-day-status-text-done {
                display: none;
            }

            .parent-day-select.is-loading .parent-day-status-text-loading,
            .parent-day-select.is-done .parent-day-status-text-done {
                display: inline;
            }

            @keyframes parent-day-spin {
                from {
                    transform: rotate(0deg);
                }

                to {
                    transform: rotate(360deg);
                }
            }

            .header-link,
            .logout-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                border-radius: 8px;
                border: 2px solid var(--header-link-border);
                background: var(--header-link-bg);
                color: var(--header-link-copy);
                text-decoration: none;
                font-weight: 700;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
            }

            .header-link {
                padding: 0 16px;
                gap: 8px;
            }

            .header-link.is-active {
                background: var(--header-link-active-bg);
            }

            .header-link-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 24px;
                min-height: 24px;
                padding: 0 6px;
                border-radius: 999px;
                background: color-mix(in srgb, var(--accent) 18%, white);
                color: var(--header-link-copy);
                border: 2px solid color-mix(in srgb, var(--accent) 28%, rgba(23, 63, 123, 0.1));
                font-size: 0.76rem;
                font-weight: 800;
                line-height: 1;
            }

            .logout-link {
                width: 42px;
                min-width: 42px;
            }

            .logout-link svg {
                width: 22px;
                height: 22px;
            }

            .user-badge {
                position: absolute;
                right: 18px;
                bottom: -19px;
                width: 56px;
                height: 56px;
                border-radius: 999px;
                border: 3px solid var(--panel-stroke);
                background: var(--user-badge-bg);
                color: var(--user-badge-copy);
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: var(--shadow);
            }

            .page-content {
                max-width: 1120px;
                margin: 34px auto 64px;
                padding: 0 18px;
            }

            .flash-stack {
                display: grid;
                gap: 12px;
                margin-bottom: 18px;
            }

            .flash {
                border-radius: 12px;
                padding: 14px 16px;
                border: 3px solid var(--panel-stroke);
                background: rgba(255, 255, 255, 0.82);
                box-shadow: var(--shadow);
                font-weight: 700;
            }

            .flash-success {
                border-color: #8fc23d;
                color: #305300;
                background: #eef8d8;
            }

            .flash-error {
                border-color: #ff6b60;
                color: #8b150e;
                background: #ffe7e4;
            }

            .stack {
                display: grid;
                gap: 18px;
            }

            .panel {
                background: var(--panel-bg);
                border: 3px solid var(--panel-stroke);
                border-radius: 12px;
                padding: 18px;
                box-shadow: var(--shadow);
            }

            .panel-strong {
                background: var(--panel-strong);
            }

            .panel-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 16px;
                margin-bottom: 14px;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                margin-bottom: 8px;
                color: var(--eyebrow-color);
                font-size: 0.82rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .panel-title,
            .hero-title {
                margin: 0;
                color: var(--heading);
                font-size: clamp(1.5rem, 3vw, 2.2rem);
                font-weight: 800;
            }

            .panel-subtitle,
            .hero-copy,
            .hint,
            .meta-copy {
                margin: 0;
                color: var(--muted);
                line-height: 1.6;
            }

            .hero-grid,
            .summary-grid,
            .teacher-grid,
            .stats-grid,
            .slot-grid,
            .list-stack {
                display: grid;
                gap: 16px;
            }

            .hero-grid {
                grid-template-columns: minmax(0, 1.4fr) minmax(260px, 0.9fr);
                align-items: stretch;
            }

            .summary-grid,
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }

            .teacher-grid {
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            }

            .slot-grid {
                grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            }

            .tile,
            .stat-card {
                background: rgba(255, 255, 255, 0.38);
                border: 3px solid var(--panel-stroke);
                border-radius: 10px;
                padding: 16px;
            }

            .tile {
                min-height: 132px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                gap: 8px;
                text-align: center;
                text-decoration: none;
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }

            .tile:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 22px rgba(38, 73, 128, 0.16);
            }

            .tile-code {
                font-size: 1.15rem;
                font-weight: 800;
                color: var(--heading);
                font-style: italic;
            }

            .tile-title {
                font-size: 1rem;
                font-weight: 700;
            }

            .tile-meta,
            .mini-label {
                color: var(--muted);
                font-size: 0.92rem;
            }

            .badge,
            .status-chip {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 28px;
                padding: 0 10px;
                border-radius: 999px;
                border: 2px solid rgba(23, 63, 123, 0.12);
                background: rgba(255, 255, 255, 0.72);
                color: #1b3f75;
                font-size: 0.8rem;
                font-weight: 700;
            }

            .status-chip.is-free {
                background: #edf8d0;
                border-color: #b4d948;
            }

            .status-chip.is-booked {
                background: #ffe3e1;
                border-color: #ff827a;
            }

            .number {
                display: block;
                color: var(--heading);
                font-size: clamp(1.7rem, 4vw, 2.4rem);
                font-weight: 800;
                line-height: 1;
                margin-bottom: 6px;
            }

            .button-row,
            .hero-actions,
            .inline-actions,
            .list-row-actions {
                display: flex;
                gap: 10px;
                align-items: center;
                flex-wrap: wrap;
            }

            .actions-on-hover {
                opacity: 0;
                transform: translateY(4px);
                pointer-events: none;
                transition: opacity 0.18s ease, transform 0.18s ease;
            }

            .list-row.is-interactive:hover .actions-on-hover,
            .list-row.is-interactive:focus-within .actions-on-hover,
            .list-row.is-interactive.is-selected .actions-on-hover,
            .teacher-mini-card:hover .actions-on-hover,
            .teacher-mini-card:focus-within .actions-on-hover,
            .teacher-activity-panel:hover .actions-on-hover,
            .teacher-activity-panel:focus-within .actions-on-hover {
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;
            }

            .button,
            .print-button,
            .danger-button,
            .ghost-button,
            .slot-button,
            .close-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                border-radius: 8px;
                border: 2px solid var(--panel-stroke);
                background: white;
                color: #14386a;
                text-decoration: none;
                font-weight: 700;
                padding: 0 16px;
            }

            .button svg,
            .print-button svg,
            .danger-button svg,
            .ghost-button svg {
                width: 16px;
                height: 16px;
                margin-right: 6px;
                flex-shrink: 0;
            }

            .button {
                background: var(--accent);
                color: var(--accent-copy);
                border-color: color-mix(in srgb, var(--accent) 72%, #446fb9);
            }

            .ghost-button,
            .print-button {
                background: rgba(255, 255, 255, 0.76);
            }

            .danger-button {
                background: var(--danger-soft);
                color: #b61814;
                border-color: var(--danger);
            }

            .close-button {
                width: 46px;
                min-width: 46px;
                padding: 0;
                font-size: 1.75rem;
                line-height: 1;
            }

            .slot-button {
                width: 100%;
                min-height: 64px;
                background: rgba(255, 255, 255, 0.64);
                font-weight: 600;
                flex-direction: column;
                gap: 3px;
            }

            .slot-button small {
                color: var(--muted);
                font-size: 0.76rem;
                font-weight: 600;
            }

            .slot-button:disabled {
                opacity: 0.55;
                cursor: not-allowed;
            }

            .list-stack {
                gap: 14px;
            }

            .list-row {
                background: rgba(255, 255, 255, 0.48);
                border: 3px solid var(--panel-stroke);
                border-radius: 10px;
                padding: 18px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: wrap;
            }

            .list-row.is-interactive {
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            }

            .list-row.is-interactive:hover,
            .list-row.is-interactive:focus-within,
            .list-row.is-interactive.is-selected {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(38, 73, 128, 0.14);
                border-color: #2f64bf;
            }

            .list-row.is-highlighted {
                background: #fff2d7;
                border-color: #d89b20;
            }

            .list-row-copy {
                display: grid;
                gap: 4px;
                min-width: 0;
            }

            .list-row-title {
                margin: 0;
                font-size: 1.04rem;
                font-weight: 800;
                color: var(--heading);
            }

            .filters {
                display: grid;
                grid-template-columns: minmax(0, 1.15fr) repeat(4, minmax(110px, 1fr));
                gap: 12px;
                align-items: end;
            }

            .field {
                display: grid;
                gap: 6px;
            }

            .field label {
                color: var(--muted);
                font-size: 0.85rem;
                font-weight: 700;
            }

            .field input,
            .field select,
            .field textarea {
                width: 100%;
                min-height: 42px;
                border-radius: 8px;
                border: 2px solid var(--panel-stroke);
                background: white;
                padding: 0 12px;
                color: var(--copy);
                font: inherit;
            }

            .field textarea {
                padding: 10px 12px;
                resize: vertical;
            }

            .field input[type="file"] {
                padding: 8px 10px;
                border-style: dashed;
                background: rgba(255, 255, 255, 0.85);
            }

            .field input[type="file"]::file-selector-button {
                border: 2px solid var(--panel-stroke);
                background: #d9e5fb;
                color: #123b74;
                border-radius: 8px;
                padding: 6px 12px;
                font-weight: 700;
                margin-right: 12px;
                cursor: pointer;
            }

            .field-inline {
                gap: 10px;
            }

            .field-inline-row {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
                align-items: center;
            }

            .class-create-row {
                flex-wrap: nowrap;
            }

            .class-grid-compact {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 8px;
                max-height: 240px;
                overflow: auto;
                padding: 6px;
                border-radius: 10px;
                border: 2px dashed rgba(23, 63, 123, 0.2);
                background: rgba(255, 255, 255, 0.38);
            }

            .class-chip {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 10px;
                border: 2px solid rgba(23, 63, 123, 0.12);
                background: rgba(255, 255, 255, 0.76);
                font-weight: 700;
                font-size: 0.9rem;
                cursor: pointer;
            }

            .class-chip input {
                margin: 0;
            }

            .wizard-steps {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 6px;
            }

            .wizard-step-indicator {
                padding: 6px 12px;
                border-radius: 999px;
                border: 2px solid rgba(23, 63, 123, 0.2);
                background: rgba(255, 255, 255, 0.76);
                font-weight: 700;
                font-size: 0.85rem;
                color: var(--muted);
            }

            .wizard-step-indicator.is-active {
                color: #173f7b;
                border-color: var(--panel-stroke);
                box-shadow: 0 6px 14px rgba(23, 63, 123, 0.12);
            }

            .wizard-step {
                display: grid;
                gap: 16px;
                padding: 14px;
                border-radius: 12px;
                border: 2px dashed rgba(23, 63, 123, 0.2);
                background: rgba(255, 255, 255, 0.55);
            }

            .class-list {
                display: grid;
                gap: 10px;
                margin-top: 12px;
            }

            .class-list.compact {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 8px;
            }

            .class-list.compact-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 6px;
                max-height: 240px;
                overflow: auto;
            }

            .class-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                border-radius: 10px;
                border: 2px solid rgba(23, 63, 123, 0.12);
                background: rgba(255, 255, 255, 0.65);
            }

            .class-row.compact {
                padding: 6px 8px;
                gap: 6px;
            }

            .class-row.compact-grid {
                padding: 4px 6px;
                gap: 4px;
            }

            .class-row-main {
                display: flex;
                align-items: center;
                gap: 6px;
                width: 100%;
            }

            .class-row-form {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1 1 240px;
            }

            .class-row-delete {
                display: flex;
                align-items: center;
            }

            .class-row-actions {
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .class-input {
                min-height: 40px;
                border-radius: 8px;
                border: 2px solid var(--panel-stroke);
                padding: 0 10px;
                font-weight: 700;
                min-width: 140px;
            }

            .class-input.compact {
                min-height: 34px;
                font-size: 0.9rem;
            }


            .button-compact {
                min-height: 34px;
                padding: 0 10px;
                font-size: 0.85rem;
            }

            .button-icon {
                min-height: 34px;
                width: 34px;
                padding: 0;
                font-size: 1.1rem;
                line-height: 1;
            }

            .panel-body.is-collapsed {
                display: none;
            }

            .modal {
                position: fixed;
                inset: 0;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
                z-index: 1000;
            }

            .modal.is-open {
                display: flex;
            }

            .modal-overlay {
                position: absolute;
                inset: 0;
                background: rgba(10, 22, 40, 0.45);
            }

            .modal-content {
                position: relative;
                z-index: 1;
                width: min(520px, 92vw);
                background: var(--panel-bg);
                border: 3px solid var(--panel-stroke);
                border-radius: 12px;
                padding: 18px;
                box-shadow: var(--shadow);
                display: grid;
                gap: 12px;
            }

            .empty-copy {
                color: var(--muted);
                font-weight: 600;
            }

            .section-anchor {
                scroll-margin-top: 18px;
            }

            .teacher-summary-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }

            .teacher-mini-card {
                background: rgba(255, 255, 255, 0.48);
                border: 3px solid var(--panel-stroke);
                border-radius: 10px;
                padding: 16px;
                display: grid;
                gap: 8px;
                transition: transform 0.18s ease, box-shadow 0.18s ease;
            }

            .teacher-mini-card.is-highlighted {
                background: #fff2d7;
                border-color: #d89b20;
            }

            .teacher-mini-card:hover,
            .teacher-mini-card:focus-within {
                transform: translateY(-1px);
                box-shadow: 0 10px 22px rgba(38, 73, 128, 0.14);
            }

            .teacher-mini-top {
                display: flex;
                justify-content: space-between;
                gap: 10px;
                align-items: flex-start;
            }

            .teacher-mini-actions {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            }

            .teacher-activity-toolbar {
                justify-content: flex-end;
            }

            .compact-field {
                max-width: 180px;
            }

            .compact-field.compact-field-short {
                max-width: 90px;
            }

            .admin-teacher-form-grid {
                grid-template-columns: repeat(auto-fit, minmax(90px, max-content));
                align-items: end;
                gap: 12px;
            }

            .notification-banner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: wrap;
                padding: 14px 16px;
                border-radius: 12px;
                border: 3px solid #d89b20;
                background: #fff2d7;
                box-shadow: var(--shadow);
            }

            .notification-banner-copy {
                display: grid;
                gap: 4px;
            }

            .appointments-list {
                display: grid;
                gap: 10px;
            }

            .appointment-row {
                display: grid;
                grid-template-columns: 88px minmax(0, 1fr) auto;
                gap: 14px;
                align-items: center;
                padding: 12px 14px;
                border-radius: 10px;
                border: 2px solid rgba(23, 63, 123, 0.14);
                background: rgba(255, 255, 255, 0.62);
            }

            .appointment-time {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 42px;
                border-radius: 10px;
                background: #ffffff;
                border: 2px solid var(--panel-stroke);
                color: #173f7b;
                font-weight: 800;
            }

            .appointment-main {
                display: grid;
                gap: 3px;
                min-width: 0;
            }

            .appointment-title {
                margin: 0;
                font-size: 1rem;
                font-weight: 800;
                color: #173f7b;
            }

            .appointment-meta {
                margin: 0;
                color: var(--muted);
                line-height: 1.45;
            }

            .appointment-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 8px;
                flex-wrap: wrap;
            }

            @media (max-width: 860px) {
                .hero-grid,
                .filters {
                    grid-template-columns: 1fr;
                }

                .appointment-row {
                    grid-template-columns: 1fr;
                    align-items: start;
                }

                .appointment-actions {
                    justify-content: flex-start;
                }

                .header-actions {
                    padding-right: 76px;
                }
            }

            @media (max-width: 640px) {
                .header-inner {
                    gap: 12px;
                }

                .header-actions {
                    width: 100%;
                    margin-left: 0;
                    padding-right: 0;
                    padding-bottom: 4px;
                }

                .user-badge {
                    right: 16px;
                }

                .page-content {
                    margin-top: 30px;
                }

                .panel,
                .tile,
                .list-row,
                .stat-card {
                    padding: 14px;
                }

                .class-create-row {
                    flex-wrap: wrap;
                }

                .parent-day-select {
                    grid-template-columns: 1fr;
                }

                .parent-day-status {
                    grid-column: 1;
                }
            }
        </style>
    </head>
    <body class="theme-<?php echo e($theme ?? 'student'); ?>">
        <header class="portal-header">
            <div class="header-inner">
                <a class="logo-link" href="<?php echo e($homeRoute ? route($homeRoute) : route('login')); ?>">
                    <img src="<?php echo e(asset('images/Logo_HTLWaidhofen_std_fbg_rgb_web.png')); ?>" alt="HTL Waidhofen" style="height: 60px; background: white; padding: 6px; border-radius: 4px;">
                </a>

                <nav class="header-actions" aria-label="Seitennavigation">
                    <?php if(isset($parentDays) && $parentDays->isNotEmpty()): ?>
                        <form
                            method="POST"
                            action="<?php echo e(route('parent-days.select')); ?>"
                            class="parent-day-select <?php echo e(session('parent_day_switched') ? 'is-done' : ''); ?>"
                            data-parent-day-form
                        >
                            <?php echo csrf_field(); ?>
                            <span class="parent-day-label">Tag</span>
                            <select name="parent_day_id" data-parent-day-select onchange="this.form.submit()">
                                <?php $__currentLoopData = $parentDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($day->id); ?>"
                                        <?php if($activeParentDay && $activeParentDay->id === $day->id): ?> selected <?php endif; ?>
                                    >
                                        <?php echo e($day->date->format('d/m/Y')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <div class="parent-day-status" aria-live="polite">
                                <span class="parent-day-spinner" aria-hidden="true"></span>
                                <span class="parent-day-check" aria-hidden="true">✓</span>
                                <span class="parent-day-status-text-loading">Lädt...</span>
                                <span class="parent-day-status-text-done">Fertig geladen</span>
                            </div>
                        </form>
                    <?php endif; ?>
                    <?php $__currentLoopData = ($navLinks ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $href = $link['href'] ?? route($link['route'], $link['params'] ?? []);
                            $activePattern = $link['active'] ?? ($link['route'] ?? null);
                            $isActive = isset($link['active_exact'])
                                ? request()->routeIs($link['active_exact'])
                                : ($activePattern ? request()->routeIs($activePattern) : false);
                        ?>
                        <a
                            href="<?php echo e($href); ?>"
                            class="header-link <?php echo e($isActive ? 'is-active' : ''); ?>"
                        >
                            <?php echo e($link['label']); ?>

                            <?php if(! empty($link['badge'])): ?>
                                <span class="header-link-badge"><?php echo e($link['badge']); ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <a class="logout-link" href="<?php echo e(route('logout')); ?>" aria-label="Abmelden">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 16l4-4-4-4"></path>
                            <path d="M8 12h10"></path>
                            <path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"></path>
                        </svg>
                    </a>
                </nav>

                <div class="user-badge"><?php echo e($initials ?: 'JW'); ?></div>
            </div>
        </header>

        <main class="page-content">
            <?php if(session('success') || session('error')): ?>
                <div class="flash-stack">
                    <?php if(session('success')): ?>
                        <div class="flash flash-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="flash flash-error"><?php echo e(session('error')); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>

        <script>
            (() => {
                const parentDayForm = document.querySelector('[data-parent-day-form]');

                if (!parentDayForm) {
                    return;
                }

                const select = parentDayForm.querySelector('[data-parent-day-select]');

                select?.addEventListener('change', () => {
                    parentDayForm.classList.remove('is-done');
                    parentDayForm.classList.add('is-loading');
                });

                if (parentDayForm.classList.contains('is-done')) {
                    window.setTimeout(() => {
                        parentDayForm.classList.remove('is-done');
                    }, 1800);
                }
            })();
        </script>
    </body>
</html>
<?php /**PATH /var/www/resources/views/layouts/portal.blade.php ENDPATH**/ ?>