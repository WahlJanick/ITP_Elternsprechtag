@extends('layouts.portal', [
    'pageTitle' => 'Schueler Lehreruebersicht',
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
                    <span class="eyebrow">Buchung</span>
                    <h2 class="panel-title">Lehrer mit freien Timeslots</h2>
                    <p class="panel-subtitle">
                        Waehle einen Lehrer aus.
                    </p>
                </div>
                <a class="button" href="{{ route('student.bookings') }}">Meine Termine</a>
            </div>

            @if($teachers->isEmpty())
                <p class="empty-copy">Fuer deine aktuelle Ansicht sind keine Lehrer mit freien Timeslots vorhanden.</p>
            @else
                <div class="teacher-grid">
                    @foreach($teachers as $teacher)
                        <a class="tile" href="{{ route('student.teachers.show', $teacher['slug']) }}">
                            <span class="tile-code">{{ $teacher['short'] }}</span>
                            <span class="tile-title">{{ $teacher['name'] }}</span>
                            <span class="status-chip is-free">{{ $teacher['free_slots'] }} freie Slots</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
