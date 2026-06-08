@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Start',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Startseite</span>
                    <h2 class="hero-title">Gebuchte Termine</h2>
                </div>

                <div class="hero-actions">
                    <a class="button" href="{{ route('student.teachers.index') }}">Buchen</a>
                    <a class="ghost-button" href="{{ route('student.bookings') }}">Details</a>
                </div>
            </div>

            <div class="hero-grid">
                <div class="summary-grid">
                        <article class="stat-card">
                            <span class="number">{{ $summary['count'] }}</span>
                            <span class="mini-label">Gebuchte Termine</span>
                        </article>
                        <article class="stat-card">
                            <span class="number">{{ $summary['assigned_teacher_count'] }}</span>
                            <span class="mini-label">Lehrer deiner Klasse</span>
                        </article>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrer</span>
                    <p class="panel-subtitle">
                        @if($currentClass)
                            Klasse {{ $currentClass }}
                        @else
                            Deine Klasse konnte noch nicht automatisch erkannt werden.
                        @endif
                    </p>
                </div>

                <a class="ghost-button" href="{{ route('student.teachers.index') }}">Alle Lehrer ansehen</a>
            </div>

            @if($assignedTeachers->isEmpty())
                <p class="empty-copy">Aktuell ist noch kein Lehrerprofil deiner Klasse zugeordnet.</p>
            @else
                <div class="teacher-grid">
                    @foreach($assignedTeachers as $teacher)
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
