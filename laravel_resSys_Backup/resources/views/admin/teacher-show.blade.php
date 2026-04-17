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
                    <p class="panel-subtitle">
                        Filtere nach Name, Klasse, Raum und Zeit und verwalte die Slots dieses Lehrers.
                    </p>
                </div>
            </div>

            <div class="filters">
                <div class="field">
                    <label for="filter-name">Name</label>
                    <input id="filter-name" type="text" value="{{ $teacher['name'] }}" />
                </div>

                <div class="field">
                    <label for="filter-class">Klasse</label>
                    <select id="filter-class">
                        @foreach($filters['classes'] as $class)
                            <option>{{ $class }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="filter-room">Raum</label>
                    <select id="filter-room">
                        @foreach($filters['rooms'] as $room)
                            <option>{{ $room }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="filter-from">Zeit ab</label>
                    <select id="filter-from">
                        @foreach($filters['times'] as $time)
                            <option>{{ $time }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="filter-to">Bis</label>
                    <select id="filter-to">
                        @foreach($filters['times'] as $time)
                            <option>{{ $time }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Timeslots</span>
                    <h2 class="panel-title">Terminliste</h2>
                </div>
            </div>

            <div class="list-stack">
                @foreach($appointments as $appointment)
                    <article class="list-row">
                        <div class="list-row-copy">
                            <p class="list-row-title">{{ $appointment['student_name'] }} / {{ $appointment['class_name'] }} / {{ $appointment['room'] }} / {{ $appointment['time_label'] }}</p>
                            <p class="meta-copy">{{ $appointment['date_label'] }}</p>
                        </div>

                        <div class="list-row-actions">
                            <span class="status-chip {{ $appointment['is_reserved'] ? 'is-booked' : 'is-free' }}">
                                {{ $appointment['is_reserved'] ? 'Gebucht' : 'Frei' }}
                            </span>
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
