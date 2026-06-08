@extends('layouts.portal', [
    'pageTitle' => 'Lehrer-Übersicht',
    'roleTitle' => 'Lehrer-Ansicht',
    'theme' => 'teacher',
    'homeRoute' => 'teacher.dashboard',
    'navLinks' => [
        ['label' => 'Termine', 'route' => 'teacher.dashboard', 'active' => 'teacher.dashboard'],
    ],
])

@section('content')
    <div class="stack">
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
                    <span class="eyebrow">Termine</span>
                </div>

                <button type="button" class="print-button" onclick="window.print()">Termine drucken</button>
            </div>

            @if(! $hasTeacherMapping)
                <p class="empty-copy">
                    Dein Azure-Name wurde noch keinem Lehrer in der Lehrerliste zugeordnet. Sobald der Name passt,
                    siehst du hier nur deine eigenen Timeslots.
                </p>
            @elseif($appointments->isEmpty())
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            @else
                <div class="appointment-filter" data-appointment-filters>
                    <button type="button" class="filter-button is-active" data-appointment-filter="all">Alle</button>
                    <button type="button" class="filter-button" data-appointment-filter="free">Frei</button>
                    <button type="button" class="filter-button" data-appointment-filter="booked">Gebucht</button>
                </div>

                <div class="appointments-list teacher-appointments-grid">
                    @foreach($appointments as $appointment)
                        <article class="appointment-row teacher-appointment-row" data-appointment-status="{{ $appointment['is_reserved'] ? 'booked' : 'free' }}">
                            <div class="appointment-time">{{ $appointment['time_label'] }}</div>
                            <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                            </span>
                            @if($appointment['is_reserved'])
                                <div class="appointment-main">
                                    <p class="appointment-title">{{ $appointment['student_name'] }}</p>
                                    <p class="appointment-meta">{{ $appointment['class_name'] }}</p>
                                </div>
                            @else
                                <div class="appointment-main appointment-empty-copy" aria-hidden="true">
                                    <p class="appointment-title">&nbsp;</p>
                                    <p class="appointment-meta">&nbsp;</p>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
