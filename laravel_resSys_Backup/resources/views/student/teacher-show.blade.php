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
                        Verfuegbare Timeslots. Ein Schueler kann maximal einen Termin pro Lehrer buchen.
                    </p>
                </div>

                <a class="close-button" href="{{ route('student.teachers.index') }}" aria-label="Zurueck">&times;</a>
            </div>

            <div class="button-row" style="margin-bottom: 16px;">
                <span class="badge">{{ $teacher['display_classes'] }}</span>
                <span class="status-chip is-free">{{ $teacher['free_slots'] }} freie Slots</span>
            </div>

            @if($freeSlots->isEmpty())
                <p class="empty-copy">Dieser Lehrer hat derzeit keine freien Timeslots.</p>
            @else
                <div class="slot-grid">
                    @foreach($freeSlots as $slot)
                        <form method="POST" action="{{ route('student.timeslots.book', $slot['id']) }}">
                            @csrf
                            <button type="submit" class="slot-button" @disabled($alreadyBooked)>
                                {{ $slot['label'] }}
                                <small>{{ $slot['room'] }} / {{ $slot['date_label'] }}</small>
                            </button>
                        </form>
                    @endforeach
                </div>
            @endif

            <div class="inline-actions" style="margin-top: 18px;">
                <span class="hint">Wichtig: Max. 1 Timeslot von einem Lehrer pro Schueler.</span>
                <span class="status-chip {{ $alreadyBooked ? 'is-booked' : 'is-free' }}">
                    {{ $alreadyBooked ? 'Bereits ein Termin vorhanden' : 'Direkt buchbar' }}
                </span>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Weitere Lehrer</span>
                    <h2 class="panel-title">Weitere Buchungsoptionen</h2>
                </div>
            </div>

            <div class="teacher-grid">
                @foreach($teachers as $listTeacher)
                    <a class="tile" href="{{ route('student.teachers.show', $listTeacher['slug']) }}">
                        <span class="tile-code">{{ $listTeacher['short'] }}</span>
                        <span class="tile-title">{{ $listTeacher['name'] }}</span>
                        <span class="tile-meta">{{ $listTeacher['display_classes'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
