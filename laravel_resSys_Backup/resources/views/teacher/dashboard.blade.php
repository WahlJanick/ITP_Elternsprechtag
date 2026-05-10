@extends('layouts.portal', [
    'pageTitle' => 'Lehrer-Übersicht',
    'roleTitle' => 'Lehrer-Ansicht',
    'theme' => 'teacher',
    'homeRoute' => 'teacher.dashboard',
    'navLinks' => [
        ['label' => 'Termine', 'route' => 'teacher.dashboard', 'active' => 'teacher.dashboard'],
    ],
])

@section('content')
    <div class="stack">
        @if($teacher)
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <span class="eyebrow">Einstellungen</span>
                        <h2 class="panel-title">Termindauer</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('teacher.timeslot-duration.update') }}" class="stack">
                    @csrf
                    <div class="field field-inline">
                        <label for="timeslot_duration">Dauer in Minuten</label>
                        <div class="field-inline-row">
                            <input
                                id="timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                step="5"
                                value="{{ old('timeslot_duration', $teacher->timeslot_duration ?? 10) }}"
                                required
                            />
                            <button type="submit" class="button">Speichern</button>
                        </div>
                    </div>
                </form>
            </section>
        @endif

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehreransicht</span>
                    <h2 class="panel-title">Gebuchte Termine</h2>
                    <p class="panel-subtitle">
                        Lehrer sehen hier alle für sie angelegten Termine inklusive Schüler, Klasse, Raum und Zeit.
                    </p>
                    @if($teacher)
                        <div class="button-row" style="margin-top: 12px;">
                            <span class="badge">{{ $teacher->full_name }}</span>
                            @if($teacher->kuerzel)
                                <span class="badge">{{ $teacher->kuerzel }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                <button type="button" class="print-button" onclick="window.print()">Termine drucken</button>
            </div>

            @if(! $hasTeacherMapping)
                <p class="empty-copy">
                    Dein Azure-Name wurde noch keinem Lehrer in der Lehrerliste zugeordnet. Sobald der Name passt,
                    siehst du hier nur deine eigenen Timeslots.
                </p>
            @elseif($appointments->isEmpty())
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            @else
                <div class="list-stack">
                    @foreach($appointments as $appointment)
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $appointment['student_name'] }} / {{ $appointment['class_name'] }} / {{ $appointment['room'] }} / {{ $appointment['time_label'] }}</p>
                                <p class="meta-copy">{{ $appointment['date_label'] }}</p>
                            </div>

                            <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                            </span>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
