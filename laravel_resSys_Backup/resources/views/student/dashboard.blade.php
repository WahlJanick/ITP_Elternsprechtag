@extends('layouts.portal', [
    'pageTitle' => 'Schueler Start',
    'roleTitle' => 'Schueler-Ansicht',
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
                    <h2 class="hero-title">Gebuchte Timeslots</h2>
                    <p class="hero-copy">
                        Dein Schnellzugriff auf gebuchte Termine, freie Lehrer und die naechste Buchung.
                    </p>
                    @if($currentClass)
                        <div class="button-row" style="margin-top: 12px;">
                            <span class="badge">Klasse {{ $currentClass }}</span>
                        </div>
                    @endif
                </div>

                <div class="hero-actions">
                    <a class="button" href="{{ route('student.teachers.index') }}">Buchen</a>
                    <a class="ghost-button" href="{{ route('student.bookings') }}">Details</a>
                </div>
            </div>

            <div class="hero-grid">
                <div class="panel panel-strong">
                    <span class="eyebrow">Uebersicht</span>
                    <div class="summary-grid">
                        <article class="stat-card">
                            <span class="number">{{ $summary['count'] }}</span>
                            <span class="mini-label">Gebuchte Timeslots</span>
                        </article>
                        <article class="stat-card">
                            <span class="number">{{ $summary['teacher_count'] }}</span>
                            <span class="mini-label">Lehrer verfuegbar</span>
                        </article>
                        <article class="stat-card">
                            <span class="number">{{ $summary['free_slot_count'] }}</span>
                            <span class="mini-label">Freie Slots insgesamt</span>
                        </article>
                    </div>
                </div>

                <div class="panel panel-strong">
                    <span class="eyebrow">Gebuchte Lehrer</span>
                    <p class="hero-copy">
                        {{ $summary['teacher_names'] ?: 'Noch keine Termine gebucht.' }}
                    </p>
                    <div class="button-row" style="margin-top: 16px;">
                        <span class="badge">Max. 1 Termin pro Lehrer</span>
                        <span class="badge">Azure Login aktiv</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Empfohlen</span>
                    <h2 class="panel-title">Lehrer deiner Klasse</h2>
                    <p class="panel-subtitle">
                        Direkter Einstieg in die Buchung. Jeder Kasten fuehrt auf eine eigene Detailseite.
                    </p>
                </div>
            </div>

            @if($highlights->isEmpty())
                <p class="empty-copy">Aktuell gibt es keine Lehrer mit freien Timeslots.</p>
            @else
                <div class="teacher-grid">
                    @foreach($highlights as $teacher)
                        <a class="tile" href="{{ route('student.teachers.show', $teacher['slug']) }}">
                            <span class="tile-code">{{ $teacher['short'] }}</span>
                            <span class="tile-title">{{ $teacher['name'] }}</span>
                            <span class="tile-meta">{{ $teacher['display_classes'] }}</span>
                            <span class="status-chip is-free">{{ $teacher['free_slots'] }} freie Slots</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
