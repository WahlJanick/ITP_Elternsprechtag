@extends('layouts.portal', [
    'pageTitle' => 'Admin Lehrer-Detail',
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
                    <span class="eyebrow">Detailansicht</span>
                    <h2 class="panel-title">{{ $teacher['name'] }} ({{ $teacher['short'] }})</h2>
                </div>
            </div>

        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Timeslots</span>
                    <h2 class="panel-title">Terminliste bearbeiten</h2>
                    <p class="panel-subtitle">
                        Klicke auf einen Termin, um ihn zu bearbeiten oder zu löschen.
                    </p>
                </div>
            </div>

            <div class="list-stack">
                @foreach($appointments as $appointment)
                    <article class="list-row">
                        <div class="list-row-copy">
                            <p class="list-row-title">
                                <strong>{{ $appointment['student_name'] ?? 'Frei' }}</strong>
                                <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }} {{ $appointment['is_reserved'] ? '' : 'ml-2' }}">
                                    {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                                </span>
                            </p>
                            <p class="meta-copy">{{ $appointment['date_label'] }} - {{ $appointment['time_label'] }}</p>
                            <p style="color: #4d79ce; font-size: 0.9rem;">Raum: {{ $appointment['room'] }}</p>
                        </div>

                        <div class="list-row-actions">
                            @if($appointment['is_reserved'])
                                <form method="POST" action="{{ route('admin.timeslots.release', $appointment['id']) }}">
                                    @csrf
                                    <button type="submit" class="danger-button">Stornieren</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
