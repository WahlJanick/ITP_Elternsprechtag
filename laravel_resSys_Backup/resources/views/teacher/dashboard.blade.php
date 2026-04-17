@extends('layouts.portal', [
    'pageTitle' => 'Lehrer Dashboard',
    'roleTitle' => 'Lehrer-Ansicht',
    'theme' => 'teacher',
    'homeRoute' => 'teacher.dashboard',
    'navLinks' => [
        ['label' => 'Timeslots', 'route' => 'teacher.dashboard', 'active' => 'teacher.dashboard'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehreransicht</span>
                    <h2 class="panel-title">Gebuchte Timeslots</h2>
                    <p class="panel-subtitle">
                        Lehrer sehen hier alle fuer sie angelegten Termine inklusive Schueler, Klasse, Raum und Zeit.
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

                <button type="button" class="print-button" onclick="window.print()">Timeslots drucken</button>
            </div>

            @if(! $hasTeacherMapping)
                <p class="empty-copy">
                    Dein Azure-Name wurde noch keinem Lehrer in der Lehrerliste zugeordnet. Sobald der Name passt,
                    siehst du hier nur deine eigenen Timeslots.
                </p>
            @elseif($appointments->isEmpty())
                <p class="empty-copy">Fuer diesen Lehrer wurden noch keine Timeslots erzeugt.</p>
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
