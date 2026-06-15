@extends('layouts.portal', [
    'pageTitle' => 'Lehrer-Übersicht',
    'roleTitle' => 'Lehrer-Ansicht',
    'theme' => 'teacher',
    'homeRoute' => 'teacher.dashboard',
    'navLinks' => [],
])

@section('content')
    <div class="stack teacher-dashboard-screen">
        @if($teacher)
            <section class="panel compact-settings-panel">
                <div class="teacher-settings-line">
                    <form method="POST" action="{{ route('teacher.timeslot-duration.update') }}" class="compact-settings-form">
                        @csrf
                        <label for="timeslot_duration"><strong>Termindauer</strong></label>
                        <input
                            id="timeslot_duration"
                            type="number"
                            name="timeslot_duration"
                            min="5"
                            step="5"
                            value="{{ old('timeslot_duration', $currentDuration ?? $teacher->timeslot_duration ?? 10) }}"
                            aria-label="Termindauer in Minuten"
                            required
                        />
                        <span class="hint">Minuten</span>
                        <button type="submit" class="button button-compact">Speichern</button>
                    </form>

                    <span class="teacher-page-room {{ $teacherRoom === '' ? 'is-unassigned' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                            <path d="M9 9h.01M15 9h.01M9 13h.01M15 13h.01"></path>
                        </svg>
                        {{ $teacherRoom !== '' ? $teacherRoom : 'Kein Raum' }}
                    </span>
                </div>
            </section>
        @endif

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Termine</h2>
                </div>

                <button type="button" class="print-button" onclick="window.printPortalView()">Termine drucken</button>
            </div>

            @if(! $hasTeacherMapping)
                <p class="empty-copy">
                    Dein Azure-Name wurde noch keinem Lehrer in der Lehrerliste zugeordnet. Sobald der Name passt,
                    siehst du hier nur deine eigenen Timeslots.
                </p>
            @elseif($appointments->isEmpty())
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            @else
                @php
                    $onlyFreeAppointments = $appointments->every(
                        fn (array $appointment) => ! $appointment['is_reserved']
                    );
                @endphp
                <div class="appointment-filter" data-appointment-filters>
                    <button type="button" class="filter-button is-active" data-appointment-filter="all">Alle</button>
                    <button type="button" class="filter-button {{ $onlyFreeAppointments ? 'is-muted' : '' }}" data-appointment-filter="free">Frei</button>
                    <button type="button" class="filter-button {{ $onlyFreeAppointments ? 'is-muted' : '' }}" data-appointment-filter="booked">Gebucht</button>
                </div>

                <div class="appointments-list teacher-appointments-grid">
                    @foreach($appointments as $appointment)
                        <article class="appointment-row teacher-appointment-row" data-appointment-status="{{ $appointment['is_reserved'] ? 'booked' : 'free' }}">
                            <div class="appointment-time">{{ $appointment['time_label'] }}</div>
                            <div class="teacher-appointment-status {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                @if($appointment['is_reserved'])
                                    <p class="teacher-appointment-booker">{{ $appointment['student_name'] }}</p>
                                    <p class="teacher-appointment-class">{{ $appointment['class_name'] }}</p>
                                @else
                                    <p class="teacher-appointment-status-label">Frei</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="teacher-print-view" aria-hidden="true">
        <header class="teacher-print-header">
            <div>
                <h1>Terminübersicht</h1>
                @if($teacher)
                    <p>{{ $teacher->full_name }}</p>
                @endif
            </div>
            <div class="teacher-print-meta">
                @if($activeParentDay)
                    <span>{{ $activeParentDay->date->format('d.m.Y') }}</span>
                @endif
                @if($teacherRoom !== '')
                    <span>Raum {{ $teacherRoom }}</span>
                @endif
            </div>
        </header>

        @if($appointments->isEmpty())
            <p>Keine Termine vorhanden.</p>
        @else
            <table class="teacher-print-table">
                <thead>
                    <tr>
                        <th>Uhrzeit</th>
                        <th>Schüler</th>
                        <th>Klasse</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment['time_label'] }}</td>
                            @if($appointment['is_reserved'])
                                <td>{{ $appointment['student_name'] }}</td>
                                <td>{{ $appointment['class_name'] }}</td>
                            @else
                                <td>Frei</td>
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <style>
        .teacher-print-view {
            display: none;
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
            .teacher-dashboard-screen {
                display: none !important;
            }

            .page-content {
                max-width: none;
                margin: 0;
                padding: 0;
            }

            .teacher-print-view {
                display: block;
                padding: 14mm;
                color: #000;
                font-family: Arial, sans-serif;
            }

            .teacher-print-header {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                gap: 24px;
                margin-bottom: 18px;
                padding-bottom: 10px;
                border-bottom: 2px solid #000;
            }

            .teacher-print-header h1,
            .teacher-print-header p {
                margin: 0;
            }

            .teacher-print-header h1 {
                font-size: 20pt;
            }

            .teacher-print-header p {
                margin-top: 4px;
                font-size: 12pt;
                font-weight: 700;
            }

            .teacher-print-meta {
                display: grid;
                gap: 3px;
                text-align: right;
                font-size: 10pt;
                font-weight: 700;
            }

            .teacher-print-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }

            .teacher-print-table th,
            .teacher-print-table td {
                padding: 7px 9px;
                border: 1px solid #555;
                text-align: left;
            }

            .teacher-print-table th {
                background: #eee;
                font-weight: 700;
            }

            .teacher-print-table tr {
                break-inside: avoid;
            }
        }
    </style>
@endsection
