@php
    $displayName = auth()->user()?->name ?? 'Portal-Nutzer';
    $initials = collect(preg_split('/\s+/', trim($displayName)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle ?? 'Reservierungssystem' }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
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

            .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
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
                grid-template-columns: auto minmax(175px, 1fr);
                align-items: center;
                gap: 5px 10px;
                padding: 7px 9px 7px 11px;
                border-radius: 13px;
                border: 1px solid rgba(255, 255, 255, 0.42);
                background: rgba(255, 255, 255, 0.2);
                color: var(--header-text);
                font-weight: 600;
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.28),
                    0 7px 18px rgba(14, 44, 86, 0.14);
                transition: background 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .parent-day-select:hover,
            .parent-day-select:focus-within {
                border-color: rgba(255, 255, 255, 0.76);
                background: rgba(255, 255, 255, 0.27);
                box-shadow:
                    inset 0 1px 0 rgba(255, 255, 255, 0.35),
                    0 9px 22px rgba(14, 44, 86, 0.2);
            }

            .parent-day-select .parent-day-label {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                font-size: 0.85rem;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                white-space: nowrap;
            }

            .parent-day-label svg {
                width: 18px;
                height: 18px;
                flex: 0 0 18px;
            }

            .parent-day-control {
                position: relative;
                min-width: 175px;
            }

            .parent-day-select select {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            .parent-day-trigger {
                position: relative;
                width: 100%;
                min-height: 38px;
                padding: 6px 38px 6px 12px;
                border-radius: 9px;
                border: 1px solid color-mix(in srgb, var(--header-link-border) 62%, white);
                background: var(--header-link-active-bg);
                color: var(--header-link-copy);
                font-weight: 700;
                cursor: pointer;
                outline: none;
                text-align: left;
                box-shadow: 0 4px 10px color-mix(in srgb, var(--header-link-border) 16%, transparent);
                transition: border-color 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
            }

            .parent-day-trigger::after {
                content: '';
                position: absolute;
                top: 50%;
                right: 13px;
                width: 8px;
                height: 8px;
                border-right: 2px solid currentColor;
                border-bottom: 2px solid currentColor;
                transform: translateY(-70%) rotate(45deg);
                transition: transform 0.16s ease;
            }

            .parent-day-control.is-open .parent-day-trigger::after {
                transform: translateY(-20%) rotate(225deg);
            }

            .parent-day-trigger:hover,
            .parent-day-trigger:focus-visible,
            .parent-day-control.is-open .parent-day-trigger {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 28%, transparent);
            }

            .parent-day-menu {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                z-index: 1100;
                display: grid;
                gap: 5px;
                width: max(100%, 190px);
                max-height: min(320px, 55vh);
                padding: 7px;
                overflow-y: auto;
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 70%, white);
                border-radius: 12px;
                background: color-mix(in srgb, var(--panel-bg) 28%, white);
                color: var(--copy);
                box-shadow: 0 18px 38px rgba(16, 36, 70, 0.24);
                transform-origin: top right;
                animation: parent-day-menu-in 0.14s ease-out;
            }

            .parent-day-menu[hidden] {
                display: none;
            }

            .parent-day-option {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                width: 100%;
                min-height: 42px;
                padding: 8px 11px;
                border: 2px solid transparent;
                border-radius: 9px;
                background: transparent;
                color: var(--copy);
                font-weight: 700;
                text-align: left;
                white-space: nowrap;
            }

            .parent-day-option:hover,
            .parent-day-option:focus-visible {
                border-color: color-mix(in srgb, var(--accent) 55%, var(--panel-stroke));
                background: color-mix(in srgb, var(--accent) 18%, white);
                outline: none;
            }

            .parent-day-option.is-selected {
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 28%, white);
                color: var(--heading);
            }

            .parent-day-option.is-selected::after {
                content: '✓';
                font-size: 0.95rem;
                font-weight: 800;
            }

            @keyframes parent-day-menu-in {
                from {
                    opacity: 0;
                    transform: translateY(-4px) scale(0.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .parent-day-status {
                grid-column: 1 / -1;
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

            .parent-day-select:not(.is-loading):not(.is-done) .parent-day-status {
                display: none;
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

            .header-link.is-disabled,
            .mobile-nav-link.is-disabled,
            .button.is-disabled,
            .ghost-button.is-disabled {
                opacity: 0.5;
                cursor: not-allowed;
                pointer-events: none;
                filter: saturate(0.55);
            }

            .header-link:hover,
            .header-link:focus-visible,
            .logout-link:hover,
            .logout-link:focus-visible {
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 22%, var(--header-link-active-bg));
                color: var(--header-link-copy);
                outline: none;
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
                border: 2px solid color-mix(in srgb, var(--accent) 35%, var(--header-link-border));
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

            .eyebrow-heading {
                margin-top: 0;
                margin-bottom: 0;
            }

            .panel-title,
            .hero-title {
                margin: 0;
                color: var(--heading);
                font-size: clamp(1.5rem, 3vw, 2.2rem);
                font-weight: 800;
            }

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
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
            }

            .tile:hover {
                transform: translateY(-2px);
                border-color: var(--accent);
                background: color-mix(in srgb, var(--panel-strong) 55%, white);
                box-shadow: 0 12px 22px color-mix(in srgb, var(--panel-stroke) 20%, transparent);
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
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 28%, transparent);
                background: rgba(255, 255, 255, 0.72);
                color: var(--heading);
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
                color: var(--heading);
                text-decoration: none;
                font-weight: 700;
                padding: 0 16px;
                transition: transform 0.16s ease, border-color 0.16s ease, background 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
            }

            .button svg,
            .print-button svg,
            .danger-button svg,
            .ghost-button svg,
            .filter-button svg,
            .close-button svg,
            .header-link svg {
                width: 16px;
                height: 16px;
                margin-right: 6px;
                flex-shrink: 0;
            }

            .close-button svg {
                width: 22px;
                height: 22px;
                margin-right: 0;
            }

            .button {
                background: var(--accent);
                color: var(--accent-copy);
                border-color: color-mix(in srgb, var(--accent) 72%, var(--heading));
            }

            .ghost-button,
            .print-button {
                background: rgba(255, 255, 255, 0.76);
            }

            .button:hover,
            .button:focus-visible {
                transform: translateY(-1px);
                border-color: color-mix(in srgb, var(--accent) 55%, var(--heading));
                background: color-mix(in srgb, var(--accent) 84%, var(--heading));
                color: var(--accent-copy);
                box-shadow: 0 7px 15px color-mix(in srgb, var(--accent) 28%, transparent);
                outline: none;
            }

            .ghost-button:hover,
            .ghost-button:focus-visible,
            .print-button:hover,
            .print-button:focus-visible,
            .close-button:hover,
            .close-button:focus-visible {
                transform: translateY(-1px);
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 20%, white);
                color: var(--heading);
                box-shadow: 0 7px 15px color-mix(in srgb, var(--panel-stroke) 20%, transparent);
                outline: none;
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
                transition: border-color 0.16s ease, background 0.16s ease, color 0.16s ease, box-shadow 0.16s ease;
            }

            .slot-button:not(:disabled):hover,
            .slot-button:focus-within,
            .slot-button.is-selected {
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 22%, white);
                color: var(--heading);
                box-shadow: 0 6px 14px color-mix(in srgb, var(--accent) 20%, transparent);
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

            .appointment-filter {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
                margin-bottom: 12px;
            }

            .filter-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 34px;
                padding: 0 12px;
                border: 2px solid var(--panel-stroke);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.72);
                color: var(--copy);
                font-weight: 700;
                transition: border-color 0.16s ease, background 0.16s ease, color 0.16s ease;
            }

            .filter-button:hover,
            .filter-button:focus-visible,
            .filter-button.is-active {
                border-color: var(--accent);
                background: var(--accent);
                color: var(--accent-copy);
                outline: none;
            }

            .filter-button.is-muted {
                opacity: 0.68;
            }

            .filter-button.is-muted:hover,
            .filter-button.is-muted:focus-visible,
            .filter-button.is-muted.is-active {
                opacity: 1;
            }

            [data-appointment-status][hidden] {
                display: none;
            }

            .compact-settings-panel {
                padding: 12px 16px;
            }

            .compact-settings-form {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .compact-settings-form input {
                width: 76px;
                min-height: 34px;
                padding: 0 8px;
                border: 2px solid var(--panel-stroke);
                border-radius: 8px;
                background: white;
                color: var(--copy);
                font: inherit;
                font-weight: 700;
            }

            .teacher-navigation {
                padding: 12px;
            }

            .teacher-navigation .tile {
                min-height: 90px;
                padding: 10px;
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
                box-shadow: 0 10px 22px color-mix(in srgb, var(--panel-stroke) 22%, transparent);
                border-color: var(--accent);
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

            .teacher-meta-row {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 6px 10px;
                min-width: 0;
            }

            .teacher-class-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                min-width: 0;
            }

            .teacher-class-badge {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                min-height: 25px;
                padding: 2px 8px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 55%, white);
                border-radius: 999px;
                background: color-mix(in srgb, var(--panel-strong) 72%, white);
                color: var(--heading);
                font-size: 0.78rem;
                font-weight: 800;
                white-space: nowrap;
            }

            .teacher-class-badge svg {
                width: 14px;
                height: 14px;
                flex: 0 0 auto;
            }

            .teacher-class-badge.is-all {
                background: color-mix(in srgb, var(--panel-bg) 45%, white);
                color: var(--muted);
            }

            .teacher-room-display {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                width: fit-content;
                margin-top: 4px;
                color: var(--heading);
                font-size: 0.84rem;
                font-weight: 800;
            }

            .teacher-room-display svg {
                width: 16px;
                height: 16px;
                flex: 0 0 auto;
            }

            .teacher-room-display.is-unassigned {
                color: var(--muted);
                font-weight: 700;
            }

            .teacher-room-form {
                display: flex;
                align-items: center;
                min-width: 0;
            }

            .teacher-room-edit-trigger {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                min-height: 38px;
                padding: 0 10px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 60%, white);
                border-radius: 8px;
                background: color-mix(in srgb, var(--panel-strong) 68%, white);
                color: var(--heading);
                font: inherit;
                font-size: 0.85rem;
                font-weight: 800;
            }

            .teacher-room-edit-trigger:hover,
            .teacher-room-edit-trigger:focus-visible {
                border-color: var(--panel-stroke);
                background: var(--panel-strong);
                outline: none;
            }

            .teacher-room-edit-trigger svg {
                width: 16px;
                height: 16px;
                flex: 0 0 auto;
            }

            .teacher-room-form input {
                width: clamp(130px, 16vw, 190px);
                min-height: 38px;
                min-width: 0;
                padding: 0 11px 0 34px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 72%, white);
                border-radius: 8px;
                background:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23936e66' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 21h18'/%3E%3Cpath d='M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16'/%3E%3Cpath d='M9 9h.01M15 9h.01M9 13h.01M15 13h.01'/%3E%3C/svg%3E") no-repeat 10px center / 16px,
                    color-mix(in srgb, var(--panel-bg) 24%, white);
                color: var(--copy);
                font: inherit;
                font-weight: 700;
                box-shadow: inset 0 1px 2px rgba(77, 48, 43, 0.06);
                transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
            }

            .teacher-room-form input:focus {
                border-color: var(--accent);
                background-color: white;
                outline: none;
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
            }

            .teacher-settings-line {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
            }

            .teacher-page-room {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                min-height: 30px;
                padding: 3px 9px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 58%, white);
                border-radius: 999px;
                background: color-mix(in srgb, var(--panel-strong) 60%, white);
                color: var(--heading);
                font-size: 0.82rem;
                font-weight: 800;
            }

            .teacher-page-room svg {
                width: 15px;
                height: 15px;
            }

            .teacher-page-room.is-unassigned {
                color: var(--muted);
                font-weight: 700;
            }

            .admin-teacher-row {
                display: grid;
                gap: 10px;
                align-items: initial;
                background: color-mix(in srgb, var(--panel-bg) 88%, white);
            }

            .admin-teacher-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                flex-wrap: nowrap;
            }

            .admin-teacher-head .list-row-actions {
                justify-content: flex-end;
                margin-left: auto;
                flex: 0 0 auto;
            }

            .admin-teacher-room-row {
                width: fit-content;
            }

            .teacher-room-inline-display {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                min-height: 28px;
                padding: 0;
                border: 0;
                background: transparent;
                color: var(--heading);
                font: inherit;
                font-size: 0.86rem;
                font-weight: 800;
                text-align: left;
            }

            .teacher-room-inline-display .room-icon,
            .teacher-room-inline-display .edit-icon {
                width: 15px;
                height: 15px;
                flex: 0 0 auto;
            }

            .teacher-room-inline-display .edit-icon {
                color: var(--muted);
                opacity: 0.8;
            }

            .admin-teacher-room-row.is-unassigned .teacher-room-inline-display {
                color: var(--muted);
                font-weight: 700;
            }

            .admin-teacher-slot-counts {
                display: flex;
                align-items: center;
                gap: 7px;
                flex-wrap: wrap;
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
                background: var(--panel-strong);
                color: var(--heading);
                border-radius: 8px;
                padding: 6px 12px;
                font-weight: 700;
                margin-right: 12px;
                cursor: pointer;
            }

            .field input[type="file"]::file-selector-button:hover {
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 20%, white);
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

            .parent-day-create-row {
                gap: 0;
            }

            .parent-day-create-button {
                margin-inline-start: clamp(0.5rem, 1.2vw, 0.9rem);
            }

            .class-create-row {
                flex-wrap: nowrap;
            }

            .class-create-row input {
                flex: 1 1 240px;
                min-width: 0;
            }

            .class-grid-compact {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 8px;
                max-height: 240px;
                overflow: auto;
                padding: 6px;
                border-radius: 10px;
                border: 2px dashed color-mix(in srgb, var(--panel-stroke) 45%, transparent);
                background: rgba(255, 255, 255, 0.38);
            }

            .class-chip {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 10px;
                border-radius: 10px;
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 28%, transparent);
                background: rgba(255, 255, 255, 0.76);
                font-weight: 700;
                font-size: 0.9rem;
                cursor: pointer;
            }

            .class-chip input {
                margin: 0;
            }

            .import-days-toolbar {
                gap: 6px;
                margin-bottom: 6px;
            }

            .import-parent-days {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                max-height: 116px;
                overflow-y: auto;
                padding: 6px;
                border: 2px dashed color-mix(in srgb, var(--panel-stroke) 42%, transparent);
                border-radius: 9px;
                background: rgba(255, 255, 255, 0.34);
            }

            .import-parent-days .class-chip {
                min-height: 30px;
                padding: 3px 8px;
                border-width: 1px;
                border-radius: 999px;
                gap: 5px;
                font-size: 0.8rem;
                white-space: nowrap;
            }

            .import-parent-days .class-chip input {
                width: 14px;
                height: 14px;
            }

            .import-parent-day-add {
                display: inline-flex;
                align-items: center;
                gap: 5px;
                padding-left: 2px;
            }

            .import-parent-day-add::before {
                content: "+";
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: var(--accent);
                color: var(--accent-copy);
                font-size: 1.15rem;
                font-weight: 800;
                line-height: 1;
                box-shadow: 0 4px 10px color-mix(in srgb, var(--accent) 24%, transparent);
            }

            .import-parent-day-add input {
                width: 154px;
                min-height: 34px;
                padding: 2px 8px;
                border: 1px dashed color-mix(in srgb, var(--panel-stroke) 78%, white);
                border-radius: 999px;
                background: color-mix(in srgb, var(--panel-bg) 28%, white);
                color: var(--copy);
                font: inherit;
                font-size: 0.8rem;
                font-weight: 700;
                box-shadow: inset 0 1px 2px rgba(77, 48, 43, 0.05);
                transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            }

            .import-parent-day-add input:focus {
                border-style: solid;
                border-color: var(--accent);
                background: white;
                outline: none;
                box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
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
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 45%, transparent);
                background: rgba(255, 255, 255, 0.76);
                font-weight: 700;
                font-size: 0.85rem;
                color: var(--muted);
            }

            .wizard-step-indicator.is-active {
                color: var(--heading);
                border-color: var(--accent);
                background: color-mix(in srgb, var(--accent) 18%, white);
                box-shadow: 0 6px 14px color-mix(in srgb, var(--accent) 18%, transparent);
            }

            .wizard-step {
                display: grid;
                gap: 16px;
                padding: 14px;
                border-radius: 12px;
                border: 2px dashed color-mix(in srgb, var(--panel-stroke) 45%, transparent);
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
                grid-template-columns: repeat(auto-fit, minmax(min(100%, 180px), 1fr));
                gap: 8px;
                max-height: 240px;
                overflow: auto;
                padding: 2px;
            }

            .class-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
                justify-content: space-between;
                padding: 10px;
                border-radius: 10px;
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 28%, transparent);
                background: rgba(255, 255, 255, 0.65);
            }

            .class-row.compact {
                padding: 6px 8px;
                gap: 6px;
            }

            .class-row.compact-grid {
                padding: 4px 6px;
                gap: 4px;
                min-width: 0;
                overflow: hidden;
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
                width: 100%;
                min-width: 0;
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
                width: 100%;
                min-height: 40px;
                border-radius: 8px;
                border: 2px solid var(--panel-stroke);
                padding: 0 10px;
                font-weight: 700;
                min-width: 0;
                box-sizing: border-box;
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

            .modal-content.is-wide {
                width: min(760px, 92vw);
                max-height: min(760px, 88vh);
                overflow: auto;
            }

            .import-header-actions {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .overflow-menu {
                position: relative;
            }

            .overflow-menu-trigger {
                width: 42px;
                min-height: 42px;
                padding: 0;
                justify-content: center;
            }

            .overflow-menu-trigger svg {
                width: 20px;
                height: 20px;
                margin: 0;
            }

            .overflow-menu-popover {
                position: absolute;
                top: calc(100% + 8px);
                right: 0;
                z-index: 20;
                min-width: 210px;
                padding: 6px;
                background: var(--panel-bg);
                border: 3px solid var(--panel-stroke);
                border-radius: 10px;
                box-shadow: var(--shadow);
            }

            .overflow-menu-popover[hidden] {
                display: none;
            }

            .overflow-menu-item {
                width: 100%;
                min-height: 40px;
                padding: 8px 10px;
                border: 0;
                border-radius: 7px;
                background: transparent;
                color: var(--heading);
                cursor: pointer;
                font: inherit;
                font-weight: 750;
                text-align: left;
            }

            .overflow-menu-item:hover,
            .overflow-menu-item:focus-visible {
                background: color-mix(in srgb, var(--accent) 16%, transparent);
                outline: none;
            }

            .import-log-list {
                display: grid;
                gap: 10px;
                overflow-wrap: anywhere;
            }

            .import-log-meta,
            .import-log-stats {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 6px 12px;
                color: var(--muted);
                font-size: 0.9rem;
                font-weight: 650;
            }

            .import-log-stats {
                margin-top: 4px;
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
                border-color: var(--accent);
                box-shadow: 0 10px 22px color-mix(in srgb, var(--panel-stroke) 22%, transparent);
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
                border: 2px solid color-mix(in srgb, var(--panel-stroke) 32%, transparent);
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
                color: var(--heading);
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
                color: var(--heading);
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

            .teacher-appointments-grid {
                grid-template-columns: repeat(auto-fill, minmax(138px, 1fr));
                gap: 12px;
            }

            .teacher-appointment-row {
                display: grid;
                grid-template-columns: 1fr;
                align-content: start;
                gap: 8px;
                min-height: 150px;
                padding: 12px;
                border-color: #d9c36a;
                background: #fff1a8;
            }

            .teacher-appointment-row[data-appointment-status="free"] {
                border-color: color-mix(in srgb, var(--panel-stroke) 55%, white);
                background: #ffffff;
                box-shadow: 0 7px 16px color-mix(in srgb, var(--panel-stroke) 14%, transparent);
            }

            .teacher-appointment-row .appointment-time {
                width: 100%;
                min-height: 36px;
                padding: 0 8px;
                border-radius: 8px;
                font-size: 1.05rem;
            }

            .teacher-appointment-row .status-chip {
                width: 100%;
                min-height: 28px;
            }

            .teacher-appointment-row .status-chip.is-booked {
                border-color: #c99c22;
                background: #f2c94c;
                color: #5f4500;
            }

            .teacher-appointment-row .appointment-main {
                gap: 2px;
                padding-top: 2px;
                text-align: center;
            }

            .teacher-appointment-row .appointment-title,
            .teacher-appointment-row .appointment-meta {
                font-size: 0.88rem;
                line-height: 1.25;
                overflow-wrap: anywhere;
            }

            .teacher-appointment-row .appointment-meta {
                font-weight: 700;
            }

            .appointment-empty-copy {
                visibility: hidden;
                user-select: none;
            }

            .admin-menu-launcher {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 10px;
            }

            .admin-menu-button {
                min-height: 82px;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
                text-align: center;
            }

            .admin-menu-button svg {
                width: 22px;
                height: 22px;
                margin-right: 0;
            }

            .admin-menu-button.is-active {
                background: var(--accent);
                color: var(--accent-copy);
            }

            [data-admin-menu][hidden] {
                display: none;
            }

            [data-admin-menu] [data-collapsible-toggle] {
                display: none;
            }

            .mobile-nav {
                display: none;
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
                    min-height: 72px;
                    padding: 8px 12px 10px;
                    gap: 10px;
                    flex-wrap: wrap;
                }

                .logo-link {
                    display: flex;
                    width: calc(100% - 108px);
                }

                .logo-link img {
                    height: 48px !important;
                    max-width: 100%;
                    object-fit: contain;
                }

                .header-actions {
                    display: contents;
                }

                .header-actions .header-link {
                    display: none;
                }

                .logout-link {
                    position: absolute;
                    right: 68px;
                    top: 16px;
                    width: 40px;
                    min-width: 40px;
                    min-height: 40px;
                }

                .logout-link svg {
                    width: 20px;
                    height: 20px;
                }

                .user-badge {
                    right: 12px;
                    top: 13px;
                    bottom: auto;
                    width: 46px;
                    height: 46px;
                    border-width: 2px;
                    font-size: 0.95rem;
                    box-shadow: none;
                }

                .parent-day-select {
                    order: 3;
                    width: 100%;
                    grid-template-columns: auto minmax(0, 1fr);
                }

                .parent-day-select select {
                    min-width: 0;
                }

                .parent-day-control {
                    min-width: 0;
                }

                .parent-day-menu {
                    right: auto;
                    left: 0;
                    width: 100%;
                    transform-origin: top left;
                }

                .parent-day-status {
                    grid-column: 1 / -1;
                }

                .mobile-nav {
                    position: fixed;
                    right: 0;
                    bottom: 0;
                    left: 0;
                    z-index: 975;
                    display: flex;
                    gap: 8px;
                    padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
                    overflow-x: auto;
                    background: linear-gradient(180deg, var(--header-start) 0%, var(--header-end) 100%);
                    box-shadow: 0 -6px 20px rgba(16, 50, 100, 0.2);
                    scrollbar-width: none;
                }

                .mobile-nav::-webkit-scrollbar {
                    display: none;
                }

                .mobile-nav-link {
                    flex: 1 0 auto;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 6px;
                    min-height: 46px;
                    padding: 0 14px;
                    border: 2px solid var(--header-link-border);
                    border-radius: 10px;
                    background: var(--header-link-bg);
                    color: var(--header-link-copy);
                    font-size: 0.92rem;
                    font-weight: 700;
                    text-decoration: none;
                    white-space: nowrap;
                    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
                }

                .mobile-nav-link:hover,
                .mobile-nav-link:focus-visible,
                .mobile-nav-link.is-active {
                    border-color: var(--accent);
                    background: var(--header-link-active-bg);
                    color: var(--header-link-copy);
                    outline: none;
                }

                .mobile-nav-link .header-link-badge {
                    min-width: 22px;
                    min-height: 22px;
                }

                .page-content {
                    margin-top: 16px;
                    padding-bottom: calc(82px + env(safe-area-inset-bottom));
                }

                .hero-actions {
                    flex-direction: column;
                    align-items: stretch;
                    width: 100%;
                }

                .hero-actions .button,
                .hero-actions .ghost-button {
                    width: 100%;
                    min-height: 46px;
                }

                .panel-header {
                    flex-wrap: wrap;
                    gap: 12px;
                }

                .panel-header > .ghost-button,
                .panel-header > .button {
                    width: 100%;
                    min-height: 44px;
                    justify-content: center;
                }

                .panel-header > .import-header-actions {
                    width: auto;
                    margin-left: auto;
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

                .hide-mobile {
                    display: none !important;
                }

                .actions-on-hover {
                    opacity: 1;
                    transform: none;
                    pointer-events: auto;
                }

                .list-row-actions {
                    width: 100%;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .list-row-actions .button,
                .list-row-actions .ghost-button,
                .list-row-actions .danger-button {
                    flex: 1 1 auto;
                    min-width: 0;
                    justify-content: center;
                }

                .field-inline-row {
                    flex-direction: column;
                    align-items: stretch;
                }

                .field-inline-row input,
                .field-inline-row select,
                .field-inline-row .button,
                .field-inline-row .ghost-button {
                    width: 100%;
                }

                .parent-day-create-button {
                    margin-block-start: 0.5rem;
                    margin-inline-start: 0;
                }

                .class-create-row .button,
                .class-create-row .ghost-button {
                    flex: 1 1 170px;
                }

                .admin-teacher-head {
                    align-items: flex-start;
                    flex-wrap: wrap;
                }

                .admin-teacher-head .list-row-actions {
                    width: 100%;
                    margin-left: 0;
                }

                .admin-teacher-head .list-row-actions > * {
                    flex: 1 1 150px;
                }

            }
        </style>
    </head>
    <body class="theme-{{ $theme ?? 'student' }}">
        <header class="portal-header">
            <div class="header-inner">
                <a class="logo-link" href="{{ $homeRoute ? route($homeRoute) : route('login') }}">
                    <img src="{{ asset('images/Logo_HTLWaidhofen_std_fbg_rgb_web.png') }}" alt="HTL Waidhofen" style="height: 60px; background: white; padding: 6px; border-radius: 4px;">
                </a>

                <nav class="header-actions" aria-label="Seitennavigation">
                    @if(isset($parentDays) && $parentDays->isNotEmpty())
                        <form
                            method="POST"
                            action="{{ route('parent-days.select') }}"
                            class="parent-day-select {{ session('parent_day_switched') ? 'is-done' : '' }}"
                            data-parent-day-form
                        >
                            @csrf
                            <span class="parent-day-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                    <path d="M16 3v4M8 3v4M3 10h18"></path>
                                </svg>
                                Datum
                            </span>
                            <div class="parent-day-control" data-parent-day-control>
                                <select id="parent_day_id" name="parent_day_id" aria-label="Elternsprechtag auswählen" data-parent-day-select>
                                    @foreach($parentDays as $day)
                                        <option
                                            value="{{ $day->id }}"
                                            @if($activeParentDay && $activeParentDay->id === $day->id) selected @endif
                                        >
                                            {{ $day->date->format('d/m/Y') }}
                                        </option>
                                    @endforeach
                                </select>
                                <button
                                    type="button"
                                    class="parent-day-trigger"
                                    aria-haspopup="listbox"
                                    aria-expanded="false"
                                    aria-controls="parent-day-menu"
                                    data-parent-day-trigger
                                    data-no-default-icon
                                >
                                    {{ $activeParentDay?->date?->format('d/m/Y') ?? $parentDays->first()?->date?->format('d/m/Y') }}
                                </button>
                                <div
                                    class="parent-day-menu"
                                    id="parent-day-menu"
                                    role="listbox"
                                    aria-label="Datum auswählen"
                                    data-parent-day-menu
                                    hidden
                                >
                                    @foreach($parentDays as $day)
                                        @php
                                            $isSelectedDay = $activeParentDay
                                                && $activeParentDay->id === $day->id;
                                        @endphp
                                        <button
                                            type="button"
                                            class="parent-day-option {{ $isSelectedDay ? 'is-selected' : '' }}"
                                            role="option"
                                            aria-selected="{{ $isSelectedDay ? 'true' : 'false' }}"
                                            data-parent-day-option
                                            data-value="{{ $day->id }}"
                                            data-no-default-icon
                                        >
                                            {{ $day->date->format('d/m/Y') }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <div class="parent-day-status" aria-live="polite">
                                <span class="parent-day-spinner" aria-hidden="true"></span>
                                <span class="parent-day-check" aria-hidden="true">✓</span>
                                <span class="parent-day-status-text-loading">Lädt...</span>
                                <span class="parent-day-status-text-done">Fertig geladen</span>
                            </div>
                        </form>
                    @endif
                    @foreach(($navLinks ?? []) as $link)
                        @php
                            $isDisabled = (bool) ($link['disabled'] ?? false);
                            $href = $link['href'] ?? route($link['route'], $link['params'] ?? []);
                            $activePattern = $link['active'] ?? ($link['route'] ?? null);
                            $isActive = ! $isDisabled && (isset($link['active_exact'])
                                ? request()->routeIs($link['active_exact'])
                                : ($activePattern ? request()->routeIs($activePattern) : false));
                        @endphp
                        @if($isDisabled)
                            <span class="header-link is-disabled" aria-disabled="true">
                                {{ $link['label'] }}
                            </span>
                        @else
                            <a
                                href="{{ $href }}"
                                class="header-link {{ $isActive ? 'is-active' : '' }}"
                            >
                                {{ $link['label'] }}
                                @if(! empty($link['badge']))
                                    <span class="header-link-badge">{{ $link['badge'] }}</span>
                                @endif
                            </a>
                        @endif
                    @endforeach

                    <a class="logout-link" href="{{ route('logout') }}" aria-label="Abmelden">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M14 16l4-4-4-4"></path>
                            <path d="M8 12h10"></path>
                            <path d="M10 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"></path>
                        </svg>
                    </a>
                </nav>

                <div class="user-badge">{{ $initials ?: 'JW' }}</div>
            </div>
        </header>

        @if(!empty($navLinks ?? []))
            <nav class="mobile-nav" aria-label="Mobile Navigation">
                @foreach(($navLinks ?? []) as $link)
                    @php
                        $mobileDisabled = (bool) ($link['disabled'] ?? false);
                        $mobileHref = $link['href'] ?? route($link['route'], $link['params'] ?? []);
                        $mobilePattern = $link['active'] ?? ($link['route'] ?? null);
                        $mobileActive = ! $mobileDisabled && (isset($link['active_exact'])
                            ? request()->routeIs($link['active_exact'])
                            : ($mobilePattern ? request()->routeIs($mobilePattern) : false));
                    @endphp
                    @if($mobileDisabled)
                        <span class="mobile-nav-link is-disabled" aria-disabled="true">
                            {{ $link['label'] }}
                        </span>
                    @else
                        <a
                            href="{{ $mobileHref }}"
                            class="mobile-nav-link {{ $mobileActive ? 'is-active' : '' }}"
                            @if($mobileActive) aria-current="page" @endif
                        >
                            {{ $link['label'] }}
                            @if(! empty($link['badge']))
                                <span class="header-link-badge">{{ $link['badge'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </nav>
        @endif

        <main class="page-content">
            @if(session('success') || session('error'))
                <div class="flash-stack">
                    @if(session('success'))
                        <div class="flash flash-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="flash flash-error">{{ session('error') }}</div>
                    @endif
                </div>
            @endif

            @yield('content')
        </main>

        @if(session('booking_conflict'))
            @php($bookingConflict = session('booking_conflict'))
            <div class="modal is-open" role="dialog" aria-modal="true" aria-labelledby="booking-conflict-title">
                <div class="modal-overlay" aria-hidden="true"></div>
                <div class="modal-content">
                    <div class="panel-header" style="margin-bottom: 0;">
                        <div>
                            <span class="eyebrow">Terminüberschneidung</span>
                            <h2 class="panel-title" id="booking-conflict-title">Welchen Termin möchtest du behalten?</h2>
                        </div>
                    </div>

                    <p class="meta-copy">
                        Du kannst während dieses Zeitraums nur bei einem Termin gleichzeitig sein.
                    </p>

                    <div class="list-stack">
                        @foreach($bookingConflict['existing'] as $existingAppointment)
                            <article class="list-row" style="padding: 12px;">
                                <div class="list-row-copy">
                                    <strong>Bestehend: {{ $existingAppointment['teacher_name'] }}</strong>
                                    <span class="meta-copy">
                                        {{ $existingAppointment['date_label'] }},
                                        {{ $existingAppointment['time_label'] }} Uhr,
                                        Raum {{ $existingAppointment['room'] }}
                                    </span>
                                </div>
                            </article>
                        @endforeach

                        <article class="list-row is-highlighted" style="padding: 12px;">
                            <div class="list-row-copy">
                                <strong>Neu: {{ $bookingConflict['new']['teacher_name'] }}</strong>
                                <span class="meta-copy">
                                    {{ $bookingConflict['new']['date_label'] }},
                                    {{ $bookingConflict['new']['time_label'] }} Uhr,
                                    Raum {{ $bookingConflict['new']['room'] }}
                                </span>
                            </div>
                        </article>
                    </div>

                    <div class="button-row">
                        <form method="POST" action="{{ route('student.timeslots.book') }}">
                            @csrf
                            <input type="hidden" name="timeslot_id" value="{{ $bookingConflict['timeslot_id'] }}">
                            <input type="hidden" name="conflict_resolution" value="keep_existing">
                            <button type="submit" class="ghost-button">Bestehenden behalten</button>
                        </form>

                        <form method="POST" action="{{ route('student.timeslots.book') }}">
                            @csrf
                            <input type="hidden" name="timeslot_id" value="{{ $bookingConflict['timeslot_id'] }}">
                            <input type="hidden" name="conflict_resolution" value="keep_new">
                            <button type="submit" class="button">Neuen Termin behalten</button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <script>
            (() => {
                const parentDayForm = document.querySelector('[data-parent-day-form]');

                if (parentDayForm) {
                    const select = parentDayForm.querySelector('[data-parent-day-select]');
                    const control = parentDayForm.querySelector('[data-parent-day-control]');
                    const trigger = parentDayForm.querySelector('[data-parent-day-trigger]');
                    const menu = parentDayForm.querySelector('[data-parent-day-menu]');
                    const options = Array.from(parentDayForm.querySelectorAll('[data-parent-day-option]'));

                    const closeParentDayMenu = (returnFocus = false) => {
                        menu.hidden = true;
                        control.classList.remove('is-open');
                        trigger.setAttribute('aria-expanded', 'false');

                        if (returnFocus) {
                            trigger.focus();
                        }
                    };

                    const openParentDayMenu = () => {
                        menu.hidden = false;
                        control.classList.add('is-open');
                        trigger.setAttribute('aria-expanded', 'true');

                        const selectedOption = options.find((option) => option.classList.contains('is-selected'));
                        (selectedOption ?? options[0])?.focus();
                    };

                    trigger?.addEventListener('click', () => {
                        if (menu.hidden) {
                            openParentDayMenu();
                        } else {
                            closeParentDayMenu(true);
                        }
                    });

                    options.forEach((option, index) => {
                        option.addEventListener('click', () => {
                            if (select.value === option.dataset.value) {
                                closeParentDayMenu(true);
                                return;
                            }

                            select.value = option.dataset.value;
                            trigger.textContent = option.textContent.trim();
                            parentDayForm.classList.remove('is-done');
                            parentDayForm.classList.add('is-loading');
                            closeParentDayMenu();
                            parentDayForm.requestSubmit();
                        });

                        option.addEventListener('keydown', (event) => {
                            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                                event.preventDefault();
                                const direction = event.key === 'ArrowDown' ? 1 : -1;
                                options[(index + direction + options.length) % options.length].focus();
                            }

                            if (event.key === 'Home' || event.key === 'End') {
                                event.preventDefault();
                                options[event.key === 'Home' ? 0 : options.length - 1].focus();
                            }

                            if (event.key === 'Escape') {
                                event.preventDefault();
                                closeParentDayMenu(true);
                            }
                        });
                    });

                    select?.addEventListener('change', () => {
                        parentDayForm.classList.remove('is-done');
                        parentDayForm.classList.add('is-loading');
                    });

                    document.addEventListener('click', (event) => {
                        if (! control.contains(event.target)) {
                            closeParentDayMenu();
                        }
                    });

                    if (parentDayForm.classList.contains('is-done')) {
                        window.setTimeout(() => {
                            parentDayForm.classList.remove('is-done');
                        }, 1800);
                    }
                }

                document.querySelectorAll('.mobile-nav-link.is-active').forEach((link) => {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });

                document.querySelectorAll('[data-appointment-filters]').forEach((filterGroup) => {
                    const panel = filterGroup.closest('.panel');
                    const appointments = panel?.querySelectorAll('[data-appointment-status]') ?? [];

                    filterGroup.querySelectorAll('[data-appointment-filter]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const filter = button.dataset.appointmentFilter;

                            filterGroup.querySelectorAll('[data-appointment-filter]').forEach((item) => {
                                item.classList.toggle('is-active', item === button);
                            });

                            appointments.forEach((appointment) => {
                                appointment.hidden = filter !== 'all'
                                    && appointment.dataset.appointmentStatus !== filter;
                            });
                        });
                    });
                });

                const defaultIcon = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"></path>
                        <path d="M13 6l6 6-6 6"></path>
                    </svg>
                `;

                document.querySelectorAll(
                    'button, a.button, a.ghost-button, a.danger-button, a.print-button, a.header-link'
                ).forEach((control) => {
                    if (! control.hasAttribute('data-no-default-icon') && ! control.querySelector('svg')) {
                        control.insertAdjacentHTML('afterbegin', defaultIcon);
                    }
                });
            })();
        </script>
    </body>
</html>
