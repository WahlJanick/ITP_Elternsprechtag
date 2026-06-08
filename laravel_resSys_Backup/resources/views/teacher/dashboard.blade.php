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
            </section>
        @endif

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehreransicht</span>
                    <h2 class="panel-title">Gebuchte Termine</h2>
                    <p class="panel-subtitle">
                        Uhrzeit und Buchungsstatus auf einen Blick.
                    </p>
                    @if($teacher)
                        <div class="button-row" style="margin-top: 12px;">
                            <span class="badge">{{ $teacher->full_name }}</span>
                            @if($teacher->kuerzel)
                                <span class="badge">{{ $teacher->kuerzel }}</span>
                            @endif
                        </div>
                    @endif
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

                <div class="appointments-list">
                    @foreach($appointments as $appointment)
                        <article class="appointment-row" data-appointment-status="{{ $appointment['is_reserved'] ? 'booked' : 'free' }}">
                            <div class="appointment-time">{{ $appointment['time_label'] }}</div>
                            <div class="appointment-main">
                                @if($appointment['is_reserved'])
                                    <p class="appointment-title">{{ $appointment['student_name'] }}</p>
                                    <p class="appointment-meta">{{ $appointment['class_name'] }}</p>
                                @endif
                            </div>
                            <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
