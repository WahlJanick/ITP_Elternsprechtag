@extends('layouts.portal', [
    'pageTitle' => 'Admin Lehrer-Detail',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Terminübersicht', 'href' => '#appointments'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $teacher['name'] }} ({{ $teacher['short'] }})</h2>
                    <div class="button-row" style="margin-top: 12px;">
                        <span class="badge">{{ $teacher['display_classes'] }}</span>
                        <span class="badge">{{ $teacher['timeslot_duration_label'] }}</span>
                        @if($teacher['duration_changed'])
                            <span class="status-chip">Termindauer geändert</span>
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.update', $teacher['slug']) }}" class="stack">
                @csrf

                <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(120px, max-content)); align-items: end;">
                    <div class="field compact-field">
                        <label for="first_name">Vorname</label>
                        <input
                            id="first_name"
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $teacher['first_name']) }}"
                            required
                        />
                    </div>

                    <div class="field compact-field">
                        <label for="last_name">Nachname</label>
                        <input
                            id="last_name"
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $teacher['last_name']) }}"
                            required
                        />
                    </div>

                    <div class="field compact-field compact-field-short">
                        <label for="kuerzel">Kürzel</label>
                        <input
                            id="kuerzel"
                            type="text"
                            name="kuerzel"
                            value="{{ old('kuerzel', $teacher['kuerzel']) }}"
                            placeholder="z.B. MM"
                        />
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Lehrer speichern</button>
                    <a href="{{ route('admin.dashboard') }}#teachers" class="ghost-button">Zur Lehrerübersicht</a>
                </div>
            </form>
        </section>

        <section class="panel" id="classes">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Schulklassen</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.classes.update', $teacher['slug']) }}" class="stack">
                @csrf

                @if($classOptions->isEmpty())
                    <p class="empty-copy">Es sind noch keine Klassen verfügbar.</p>
                @else
                    <div class="class-grid-compact">
                        @foreach($classOptions as $className)
                            @php($isChecked = in_array($className, old('classes', $teacher['classes']), true))
                            <label class="class-chip">
                                <input
                                    type="checkbox"
                                    name="classes[]"
                                    value="{{ $className }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                />
                                <span>{{ $className }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="field">
                    <label for="additional_classes">Weitere Klassen</label>
                    <input
                        id="additional_classes"
                        type="text"
                        name="additional_classes"
                        value="{{ old('additional_classes') }}"
                        placeholder="z.B. 3AHIT, 4AHIT"
                    />
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Klassen speichern</button>
                </div>
            </form>
        </section>

        <section class="panel section-anchor" id="appointments">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Terminübersicht</h2>
                </div>

                <a class="button" href="{{ route('admin.timeslots.create', ['teacher' => $teacher['reference_id']]) }}">Termin hinzufügen</a>
            </div>

            @if($appointments->isEmpty())
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            @else
                <div class="list-stack">
                    @foreach($appointments as $appointment)
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $appointment['time_label'] }}</p>
                                <p class="meta-copy">{{ $appointment['date_label'] }} · Raum {{ $appointment['room'] }}</p>
                                <p class="meta-copy">{{ $appointment['student_name'] }} · {{ $appointment['class_name'] }}</p>
                            </div>

                            <div class="list-row-actions">
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
