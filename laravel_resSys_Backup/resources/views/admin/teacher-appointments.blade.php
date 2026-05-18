@extends('layouts.portal', [
    'pageTitle' => 'Admin Terminübersicht Lehrer',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehreraktivitäten', 'href' => route('admin.dashboard').'#teacher-activity'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Bearbeiten', 'route' => 'admin.teachers.show', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.show'],
        ['label' => 'Terminübersicht', 'route' => 'admin.teachers.appointments', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.appointments'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrertermine</span>
                    <h2 class="panel-title">{{ $teacher['name'] }} ({{ $teacher['short'] }})</h2>
                    <p class="panel-subtitle">Alle Termine sind hier als kompakte Liste mit Startuhrzeit zusammengefasst.</p>
                    <div class="button-row" style="margin-top: 12px;">
                        <span class="badge">{{ $teacher['display_classes'] }}</span>
                        <span class="badge">{{ $teacher['timeslot_duration_label'] }}</span>
                        @if($teacher['duration_changed'])
                            <span class="status-chip">Termindauer geändert</span>
                        @endif
                    </div>
                </div>

                <div class="button-row">
                    <a class="ghost-button" href="{{ route('admin.teachers.show', $teacher['slug']) }}">Lehrer bearbeiten</a>
                    <a class="button" href="{{ route('admin.timeslots.create', ['teacher' => $teacher['reference_id']]) }}">Termin hinzufügen</a>
                </div>
            </div>

            @if($appointments->isEmpty())
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            @else
                <div class="appointments-list">
                    @foreach($appointments as $appointment)
                        <article class="appointment-row">
                            <div class="appointment-time">{{ $appointment['time_label'] }}</div>

                            <div class="appointment-main">
                                <p class="appointment-title">{{ $appointment['student_name'] }}</p>
                                <p class="appointment-meta">{{ $appointment['date_label'] }} · {{ $appointment['class_name'] }} · Raum {{ $appointment['room'] }}</p>
                            </div>

                            <div class="appointment-actions">
                                <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                    {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                                </span>
                                <a class="ghost-button" href="{{ route('admin.timeslots.edit', $appointment['id']) }}">Bearbeiten</a>
                                @if($appointment['is_reserved'])
                                    <form method="POST" action="{{ route('admin.timeslots.release', $appointment['id']) }}">
                                        @csrf
                                        <button type="submit" class="danger-button">Stornieren</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.timeslots.destroy', $appointment['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="danger-button">Löschen</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
