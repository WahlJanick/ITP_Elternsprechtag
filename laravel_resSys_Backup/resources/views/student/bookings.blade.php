@extends('layouts.portal', [
    'pageTitle' => 'Schüler Gebuchte Termine',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
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
                                <span>{{ $booking['teacher_name'] }}</span>
                                <span>{{ $booking['room'] }}</span>
                                <span>{{ $booking['time_label'] }}</span>
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
            .student-booking-row {
                align-items: center;
                flex-wrap: nowrap;
            }

            .student-booking-summary {
                flex: 1;
                justify-content: space-between;
                gap: 10px;
                font-size: 0.9rem;
            }

            .student-booking-actions.actions-on-hover {
                width: auto;
            }

            .student-booking-actions .danger-button {
                min-height: 38px;
                padding: 0 10px;
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
