@extends('layouts.portal', [
    'pageTitle' => 'Schüler Gebuchte Termine',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
])

@section('content')
    <div class="stack student-bookings-screen">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Meine Termine</h2>
                </div>

                <button type="button" class="print-button" onclick="window.printPortalView()">Termine drucken</button>
            </div>

            @if($bookings->isEmpty())
                <p class="empty-copy">Du hast aktuell noch keine gebuchten Termine.</p>
            @else
                <div class="list-stack">
                    @foreach($bookings as $booking)
                        <article class="list-row is-interactive student-booking-row">
                            <div class="student-booking-summary">
                                <span class="student-booking-teacher">{{ $booking['teacher_name'] }}</span>
                                <span class="student-booking-room">{{ $booking['room'] }}</span>
                                <span class="student-booking-time">{{ $booking['time_label'] }}</span>
                            </div>

                            <div class="list-row-actions actions-on-hover student-booking-actions">
                                <form method="POST" action="{{ route('student.bookings.cancel', $booking['id']) }}">
                                    @csrf
                                    <button type="submit" class="danger-button">Stornieren</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="student-bookings-print" aria-hidden="true">
        @if($bookings->isEmpty())
            <p>Keine Termine vorhanden.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Lehrer</th>
                        <th>Raum</th>
                        <th>Uhrzeit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking['teacher_name'] }}</td>
                            <td>{{ $booking['room'] }}</td>
                            <td>{{ $booking['time_label'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <style>
        .student-bookings-print {
            display: none;
        }

        .student-booking-summary {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: clamp(14px, 4vw, 42px);
            color: var(--heading);
            font-weight: 800;
        }

        .student-booking-actions.actions-on-hover {
            width: auto;
            opacity: 0;
            transform: translateY(4px);
            pointer-events: none;
        }

        .student-booking-row:hover .student-booking-actions,
        .student-booking-row:focus-within .student-booking-actions {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        @media (max-width: 620px) {
            .student-bookings-screen {
                gap: 12px;
            }

            .student-bookings-screen .panel {
                padding: 14px 12px;
            }

            .student-bookings-screen .panel-header {
                align-items: stretch;
                gap: 10px;
            }

            .student-bookings-screen .print-button {
                width: 100%;
                min-height: 44px;
            }

            .student-bookings-screen .list-stack {
                gap: 10px;
            }

            .student-booking-row {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                align-items: stretch;
                gap: 12px;
                padding: 14px;
            }

            .student-booking-summary {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 8px 12px;
                width: 100%;
                font-size: 0.92rem;
            }

            .student-booking-teacher {
                grid-column: 1 / -1;
                font-size: 1rem;
                overflow-wrap: anywhere;
            }

            .student-booking-room,
            .student-booking-time {
                display: inline-flex;
                align-items: center;
                min-height: 32px;
                padding: 4px 9px;
                border: 1px solid color-mix(in srgb, var(--panel-stroke) 35%, transparent);
                border-radius: 7px;
                background: rgba(255, 255, 255, 0.58);
                color: var(--muted);
                white-space: nowrap;
            }

            .student-booking-time {
                justify-self: end;
            }

            .student-booking-actions.actions-on-hover {
                width: 100%;
                opacity: 1;
                transform: none;
                pointer-events: auto;
            }

            .student-booking-actions form {
                width: 100%;
            }

            .student-booking-actions .danger-button {
                width: 100%;
                min-height: 44px;
                padding: 0 12px;
            }
        }

        @media (max-width: 360px) {
            .student-booking-summary {
                grid-template-columns: 1fr;
            }

            .student-booking-teacher,
            .student-booking-room,
            .student-booking-time {
                grid-column: 1;
            }

            .student-booking-room,
            .student-booking-time {
                justify-self: stretch;
            }

            .student-booking-time {
                justify-content: flex-start;
            }
        }

        @media print {
            @page {
                margin: 0;
            }

            body {
                background: white;
                color: #000;
            }

            .portal-header,
            .mobile-nav,
            .flash-stack,
            .student-bookings-screen {
                display: none !important;
            }

            .page-content {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .student-bookings-print {
                display: block;
                padding: 14mm;
                color: #000;
                font-family: Arial, sans-serif;
            }

            .student-bookings-print table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }

            .student-bookings-print th,
            .student-bookings-print td {
                padding: 7px 9px;
                border: 1px solid #555;
                text-align: left;
            }

            .student-bookings-print th {
                background: #eee;
            }

            .student-bookings-print tr {
                break-inside: avoid;
            }
        }
    </style>
@endsection
