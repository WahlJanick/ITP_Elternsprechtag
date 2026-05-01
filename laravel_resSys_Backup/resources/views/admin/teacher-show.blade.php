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
                    <p class="panel-subtitle">Zugeordnete Klassen: {{ $teacher['display_classes'] }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.update', $teacher['slug']) }}" class="stack">
                @csrf

                <div class="teacher-grid">
                    <div class="field">
                        <label for="first_name">Vorname</label>
                        <input
                            id="first_name"
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', $teacher['first_name']) }}"
                            required
                        />
                    </div>

                    <div class="field">
                        <label for="last_name">Nachname</label>
                        <input
                            id="last_name"
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', $teacher['last_name']) }}"
                            required
                        />
                    </div>

                    <div class="field">
                        <label for="kuerzel">Kuerzel</label>
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
                </div>
            </form>

        </section>

        <section class="panel" id="klassen">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Klassen</span>
                    <h2 class="panel-title">Klassen zu Lehrer zuteilen</h2>
                    <p class="panel-subtitle">
                        Schueler aus den gewaehlten Klassen sehen diesen Lehrer danach automatisch bei den Timeslots.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.classes.update', $teacher['slug']) }}" class="stack">
                @csrf

                @if($classOptions->isEmpty())
                    <p class="empty-copy">Es sind noch keine Klassen verfuegbar.</p>
                @else
                    <div class="teacher-grid">
                        @foreach($classOptions as $className)
                            @php($isChecked = in_array($className, old('classes', $teacher['classes']), true))
                            <label class="tile" style="cursor: pointer;">
                                <span class="tile-code">{{ substr($className, 0, 2) }}</span>
                                <span class="tile-title">{{ $className }}</span>
                                <span class="tile-meta">Sichtbar fuer diese Klasse</span>
                                <span class="status-chip {{ $isChecked ? 'is-booked' : 'is-free' }}">
                                    <input
                                        type="checkbox"
                                        name="classes[]"
                                        value="{{ $className }}"
                                        {{ $isChecked ? 'checked' : '' }}
                                        style="margin-right: 8px;"
                                    />
                                    {{ $isChecked ? 'Zugeordnet' : 'Auswaehlbar' }}
                                </span>
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
                    <p class="meta-copy">Mehrere Klassen mit Komma, Leerzeichen oder Semikolon trennen.</p>
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Klassen speichern</button>
                    <a href="{{ route('admin.dashboard') }}" class="ghost-button">Zur Uebersicht</a>
                </div>
            </form>
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
