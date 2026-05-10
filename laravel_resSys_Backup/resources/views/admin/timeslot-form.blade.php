@extends('layouts.portal', [
    'pageTitle' => 'Termin bearbeiten',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">@if($isEdit)Bearbeiten@endif</span>
                    <h2 class="panel-title">
                        @if($isEdit)
                            Termin bearbeiten
                            @if($timeslot)
                                @if($timeslot->is_reserved)
                                    (Gebucht)
                                @else
                                    (Frei)
                                @endif
                            @endif
                        @else
                            Neuer Termin
                        @endif
                    </h2>
                </div>
            </div>

            <form method="POST" action="@if($isEdit){{ route('admin.timeslots.update', $timeslot->id) }}@else{{ route('admin.timeslots.store') }}@endif" class="form-stack">
                @csrf
                @method($isEdit ? 'PUT' : 'POST')

                <div class="form-group">
                    <label for="teacher_id">Lehrer *</label>
                    <select id="teacher_id" name="teacher_id" required>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->teacher_id }}" @if(($isEdit && $timeslot && $timeslot->teacher_id == $teacher->teacher_id) || (!$isEdit && isset($preselectedTeacherId) && $preselectedTeacherId == $teacher->teacher_id)) selected @endif>
                                {{ $teacher->full_name }} (@if($teacher->kuerzel){{ $teacher->kuerzel }}@else{{ $teacher->first_name }}@endif)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="student_id">Schüler (optional)</label>
                    <select id="student_id" name="student_id">
                        <option value="">Keiner</option>
                        @foreach($students as $student)
                            <option value="{{ $student->student_id }}" @if($isEdit && $timeslot && $timeslot->student_id == $student->student_id) selected @endif>
                                {{ $student->full_name }} ({{ $student->class_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="parent_day">Datum</label>
                    <input type="text" id="parent_day" value="{{ $parentDayLabel ?? '--/--/----' }}" readonly />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="starts_at">Beginn *</label>
                        <input type="time" id="starts_at" name="starts_at" value="@if($isEdit && $timeslot){{ $timeslot->starts_at->format('H:i') }}@else{{ '17:00' }}@endif" required />
                    </div>

                    <div class="form-group">
                        <label for="ends_at">Ende *</label>
                        <input type="time" id="ends_at" name="ends_at" value="@if($isEdit && $timeslot){{ $timeslot->ends_at->format('H:i') }}@else{{ '17:15' }}@endif" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="room">Raum *</label>
                    <input type="text" id="room" name="room" list="room-options" value="@if($isEdit && $timeslot){{ $timeslot->room }}@else{{ 'B201' }}@endif" required placeholder="z.B. B201, A104" />
                    @if(isset($rooms) && $rooms->isNotEmpty())
                        <datalist id="room-options">
                            @foreach($rooms as $room)
                                <option value="{{ $room->name }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                </div>

                <div class="form-group checkbox-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_reserved" @if($isEdit && $timeslot && $timeslot->is_reserved) checked @endif />
                        <span>Termin als gebucht markieren</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="role-btn role-student" style="display:inline-flex; text-decoration:none; padding: 12px 24px; font-size: 1rem;">
                        <span style="font-weight: 700;">@if($isEdit)Speichern@else Erstellen@endif</span>
                    </button>
                    @if($isEdit && $timeslot && $timeslot->teacher)
                        <a href="{{ route('admin.teachers.show', $timeslot->teacher->slug) }}" class="role-btn role-teacher" style="display:inline-flex; text-decoration:none; padding: 12px 24px; font-size: 1rem;">
                            Abbrechen
                        </a>
                    @endif
                </div>
            </form>
        </section>
    </div>
@endsection
