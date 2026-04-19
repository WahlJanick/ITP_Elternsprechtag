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
                        Dein Schnellzugriff auf gebuchte Termine.
                    </p>
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
    </div>
@endsection
