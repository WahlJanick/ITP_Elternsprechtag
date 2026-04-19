@extends('layouts.portal', [
    'pageTitle' => 'Schueler Lehrer-Detail',
    'roleTitle' => 'Schueler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Home', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrer-Detail</span>
                    <h2 class="panel-title">{{ $teacher['name'] }} ({{ $teacher['short'] }})</h2>
                    <p class="panel-subtitle">
                        Verfuegbare Timeslots von 17:00 - 19:00 Uhr. Ein Schueler kann maximal einen Termin pro Lehrer buchen.
                    </p>
                </div>

                <a class="close-button" href="{{ route('student.teachers.index') }}" aria-label="Zurueck">&times;</a>
            </div>

            <div class="button-row" style="margin-bottom: 16px;">
                <span class="badge">Raum: {{ $teacherRoom }}</span>
                <span class="status-chip is-free">{{ $teacher['free_slots'] }} freie Slots</span>
            </div>

            @if($freeSlots->isEmpty())
                <p class="empty-copy">Dieser Lehrer hat derzeit keine freien Timeslots.</p>
            @else
                <form method="POST" action="{{ route('student.timeslots.book') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacher['reference_id'] }}">

                    <div class="slot-grid">
                        @foreach($freeSlots as $slot)
                            <label class="slot-button" style="cursor: {{ $alreadyBooked ? 'not-allowed' : 'pointer' }}; opacity: {{ $alreadyBooked ? '0.6' : '1' }};">
                                <input type="radio" name="timeslot_id" value="{{ $slot['id'] }}" {{ $alreadyBooked ? 'disabled' : '' }} style="position: absolute; opacity: 0;">
                                {{ $slot['label'] }}
                                <small>{{ $slot['date_label'] }}</small>
                            </label>
                        @endforeach
                    </div>

                    <div class="inline-actions" style="margin-top: 24px; justify-content: space-between;">
                        <span class="hint">Wichtig: Max. 1 Timeslot von einem Lehrer pro Schueler.</span>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <span class="status-chip {{ $alreadyBooked ? 'is-booked' : 'is-free' }}">
                                {{ $alreadyBooked ? 'Bereits ein Termin vorhanden' : 'Direkt buchbar' }}
                            </span>
                            @if(!$alreadyBooked)
                                <button type="submit" class="button" id="book-button" disabled>Buchen</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Navigation</span>
                    <h2 class="panel-title">Vorherige und naechste Lehrer</h2>
                </div>
            </div>

            <div class="teacher-grid">
                @foreach($teachers as $listTeacher)
                    <a class="tile" href="{{ route('student.teachers.show', $listTeacher['slug']) }}">
                        <span class="tile-code">{{ $listTeacher['short'] }}</span>
                        <span class="tile-title">{{ $listTeacher['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    @if(!$alreadyBooked)
        <script>
            document.querySelectorAll('input[name="timeslot_id"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('book-button').disabled = false;
                    document.querySelectorAll('.slot-button').forEach(btn => {
                        btn.style.background = 'rgba(255, 255, 255, 0.64)';
                        btn.style.borderColor = '';
                    });
                    this.closest('.slot-button').style.background = '#d7e6ff';
                    this.closest('.slot-button').style.borderColor = '#4d79ce';
                });
            });
        </script>
    @endif
@endsection
