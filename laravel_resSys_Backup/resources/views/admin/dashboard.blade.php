@extends('layouts.portal', [
    'pageTitle' => 'Admin Dashboard',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Uebersicht', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Administration</span>
                    <h2 class="panel-title">Alle Lehrer, Schueler und Timeslots</h2>
                    <p class="panel-subtitle">
                        Die Admin-Variante fasst alle wichtigen Kennzahlen zusammen und fuehrt in die Detailseiten.
                    </p>
                </div>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span class="number">{{ $stats['students'] }}</span>
                    <span class="mini-label">Schueler gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['teachers'] }}</span>
                    <span class="mini-label">Lehrer gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['free_slots'] }}</span>
                    <span class="mini-label">Freie Slots</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['booked_slots'] }}</span>
                    <span class="mini-label">Gebuchte Slots</span>
                </article>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrer</span>
                    <h2 class="panel-title">Detailansichten pro Lehrer</h2>
                    <p class="panel-subtitle">
                        Jeder Kasten oeffnet die Admin-Detailseite mit Filtern und Terminliste.
                    </p>
                </div>
            </div>

            <div class="teacher-grid">
                @foreach($teachers as $teacher)
                    <a class="tile" href="{{ route('admin.teachers.show', $teacher['slug']) }}">
                        <span class="tile-code">{{ $teacher['short'] }}</span>
                        <span class="tile-title">{{ $teacher['name'] }}</span>
                        <span class="tile-meta">{{ $teacher['display_classes'] }}</span>
                        <span class="status-chip is-booked">{{ $teacher['booked_slots'] }} gebucht</span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
