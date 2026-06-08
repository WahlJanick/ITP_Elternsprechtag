@extends('layouts.portal', [
    'pageTitle' => 'Admin Lehrer-Detail',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Termine', 'route' => 'admin.teachers.appointments', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.appointments'],
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

                <div class="teacher-grid admin-teacher-form-grid">
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
                    <a href="{{ route('admin.teachers.appointments', $teacher['slug']) }}" class="ghost-button">Terminübersicht</a>
                    <a href="{{ route('admin.dashboard') }}#teachers" class="ghost-button">Zur Lehrerübersicht</a>
                </div>
            </form>
        </section>

        <section class="panel compact-settings-panel">
            <form method="POST" action="{{ route('admin.teachers.duration.update', $teacher['slug']) }}" class="compact-settings-form">
                @csrf
                <label for="timeslot_duration"><strong>Termindauer</strong></label>
                <input
                    id="timeslot_duration"
                    type="number"
                    name="timeslot_duration"
                    min="5"
                    step="5"
                    value="{{ old('timeslot_duration', $teacher['timeslot_duration'] ?? 10) }}"
                    aria-label="Termindauer in Minuten"
                    required
                    @if(! $canEditParentDay) disabled @endif
                />
                <span class="hint">Minuten · {{ $parentDayLabel }}</span>
                @if($canEditParentDay)
                    <button type="submit" class="button button-compact">Speichern</button>
                @else
                    <span class="badge">Nur Ansicht</span>
                @endif
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
    </div>
@endsection
