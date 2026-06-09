@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Lehrerübersicht',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings', 'disabled' => ! $hasBookings],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Buchung</span>
                    <h2 class="panel-title">Lehrer, die dich unterrichten</h2>
                </div>
                @if($hasBookings)
                    <a class="button" href="{{ route('student.bookings') }}">Meine Termine</a>
                @else
                    <span class="button is-disabled" aria-disabled="true">Meine Termine</span>
                @endif
            </div>

            @if($teachers->isEmpty())
                <p class="empty-copy">Für deine aktuelle Ansicht sind keine Lehrer hinterlegt.</p>
            @else
                <div class="teacher-grid">
                    @foreach($teachers as $teacher)
                        <a class="tile" href="{{ route('student.teachers.show', $teacher['slug']) }}">
                            <span class="tile-code">{{ $teacher['short'] }}</span>
                            <span class="tile-title">{{ $teacher['name'] }}</span>
                            <span class="status-chip {{ $teacher['free_slots'] > 0 ? 'is-free' : 'is-booked' }}">
                                {{ $teacher['free_slots'] > 0 ? $teacher['free_slots'].' freie Termine' : 'Aktuell keine freien Termine' }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
