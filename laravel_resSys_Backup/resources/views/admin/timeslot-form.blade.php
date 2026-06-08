@extends('layouts.portal', [
    'pageTitle' => 'Termine',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Bearbeiten', 'route' => 'admin.teachers.show', 'params' => [$teacherSlug], 'active_exact' => 'admin.teachers.show'],
        ['label' => 'Terminuebersicht', 'route' => 'admin.teachers.appointments', 'params' => [$teacherSlug], 'active_exact' => 'admin.teachers.appointments'],
    ],
])

@php
    $isEditing = (bool) $isEdit;
    $formAction = $isEditing
        ? route('admin.timeslots.update', $timeslot->id)
        : route('admin.timeslots.store');
    $teacherValue = $isEditing && isset($timeslot) ? $timeslot->teacher_id : ($preselectedTeacherId ?? null);
    $studentValue = $isEditing && isset($timeslot) ? $timeslot->student_id : null;
    $startValue = $isEditing && isset($timeslot) ? $timeslot->starts_at->format('H:i') : '17:00';
    $endValue = $isEditing && isset($timeslot) ? $timeslot->ends_at->format('H:i') : '19:00';
    $roomValue = $isEditing && isset($timeslot) ? $timeslot->room : 'B201';
    $isReserved = $isEditing && isset($timeslot) && $timeslot->is_reserved;
    $disabledAttr = ! $canEditParentDay ? 'disabled' : '';
    $slotLengthValue = old('slot_length_minutes', $suggestedTimeslotDuration ?? 10);
@endphp

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">{{ $isEditing ? 'Bearbeiten' : 'Generierung' }}</span>
                    <h2 class="panel-title">
                        @if($isEditing)
                            Termin bearbeiten
                            @if(isset($timeslot))
                                {{ $timeslot->is_reserved ? '(Gebucht)' : '(Frei)' }}
                            @endif
                        @else
                            Termine generieren
                        @endif
                    </h2>
                    <p class="panel-subtitle">Aktiver Elternsprechtag: {{ $parentDayLabel ?? '--/--/----' }}</p>
                </div>

                <div class="button-row">
                    <a href="{{ route('admin.teachers.appointments', $teacherSlug) }}" class="ghost-button">Terminuebersicht</a>
                    <a href="{{ route('admin.teachers.show', $teacherSlug) }}" class="ghost-button">Lehrer bearbeiten</a>
                </div>
            </div>

            <form method="POST" action="{{ $formAction }}" class="stack">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                @if(! $canEditParentDay)
                    <p class="hint">Dieser Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr moeglich.</p>
                @endif

                <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <div class="field">
                        <label for="teacher_id">Lehrer *</label>
                        <select id="teacher_id" name="teacher_id" required {{ $disabledAttr }}>
                            @foreach($teachers as $teacherOption)
                                <option value="{{ $teacherOption->teacher_id }}" {{ (string) $teacherValue === (string) $teacherOption->teacher_id ? 'selected' : '' }}>
                                    {{ $teacherOption->full_name }} ({{ $teacherOption->kuerzel ?: $teacherOption->first_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if($isEditing)
                        <div class="field">
                            <label for="student_id">Schueler (optional)</label>
                            <select id="student_id" name="student_id" {{ $disabledAttr }}>
                                <option value="">Keiner</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->student_id }}" {{ (string) $studentValue === (string) $student->student_id ? 'selected' : '' }}>
                                        {{ $student->full_name }} ({{ $student->class_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="field">
                        <label for="parent_day">Datum</label>
                        <input type="text" id="parent_day" value="{{ $parentDayLabel ?? '--/--/----' }}" readonly />
                    </div>

                    <div class="field">
                        <label for="starts_at">Beginn *</label>
                        <input
                            type="text"
                            id="starts_at"
                            name="starts_at"
                            value="{{ old('starts_at', $startValue) }}"
                            inputmode="numeric"
                            pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
                            placeholder="17:00"
                            title="24h-Format, z.B. 17:00"
                            required
                            {{ $disabledAttr }}
                        />
                    </div>

                    <div class="field">
                        <label for="ends_at">Ende *</label>
                        <input
                            type="text"
                            id="ends_at"
                            name="ends_at"
                            value="{{ old('ends_at', $endValue) }}"
                            inputmode="numeric"
                            pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
                            placeholder="19:00"
                            title="24h-Format, z.B. 19:00"
                            required
                            {{ $disabledAttr }}
                        />
                    </div>

                    @if(! $isEditing)
                        <div class="field">
                            <label for="slot_length_minutes">Terminlänge *</label>
                            <input
                                type="number"
                                id="slot_length_minutes"
                                name="slot_length_minutes"
                                min="5"
                                step="5"
                                value="{{ $slotLengthValue }}"
                                required
                                {{ $disabledAttr }}
                            />
                        </div>
                    @endif

                    <div class="field">
                        <label for="room">Raum *</label>
                        <input
                            type="text"
                            id="room"
                            name="room"
                            list="room-options"
                            value="{{ old('room', $roomValue) }}"
                            required
                            placeholder="z.B. B201, A104"
                            {{ $disabledAttr }}
                        />
                        @if(isset($rooms) && $rooms->isNotEmpty())
                            <datalist id="room-options">
                                @foreach($rooms as $room)
                                    <option value="{{ $room->name }}"></option>
                                @endforeach
                            </datalist>
                        @endif
                    </div>
                </div>

                @if($isEditing)
                    <div class="field">
                        <label class="class-chip" style="width: max-content;">
                            <input type="checkbox" name="is_reserved" {{ $isReserved ? 'checked' : '' }} {{ $disabledAttr }} />
                            <span>Termin als gebucht markieren</span>
                        </label>
                    </div>
                @endif

                <div class="button-row">
                    @if($canEditParentDay)
                        <button type="submit" class="button">{{ $isEditing ? 'Speichern' : 'Termine generieren' }}</button>
                    @else
                        <span class="badge">Nur Ansicht</span>
                    @endif

                    <a href="{{ route('admin.teachers.appointments', $teacherSlug) }}" class="ghost-button">Zurueck</a>
                </div>
            </form>
        </section>
    </div>
@endsection
