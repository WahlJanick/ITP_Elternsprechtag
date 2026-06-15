@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Lehrer-Detail',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings', 'disabled' => ! $hasBookings],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">{{ $teacher['name'] }}</h2>
                </div>

                <a class="close-button" href="{{ route('student.teachers.index') }}" aria-label="Zurück">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5"></path>
                        <path d="M12 19l-7-7 7-7"></path>
                    </svg>
                </a>
            </div>

            <div class="button-row" style="margin-bottom: 16px;">
                <span class="badge">Raum: {{ $teacherRoom }}</span>
                <span class="status-chip is-free">{{ $teacher['free_slots'] }} freie Termine</span>
            </div>

            @if($freeSlots->isEmpty())
                <p class="empty-copy">Dieser Lehrer hat derzeit keine freien Termine.</p>
            @else
                <form method="POST" action="{{ route('student.timeslots.book') }}" id="booking-form">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacher['reference_id'] }}">

                    <div class="slot-grid">
                        @foreach($freeSlots as $slot)
                            <label class="slot-button" style="cursor: {{ $alreadyBooked ? 'not-allowed' : 'pointer' }}; opacity: {{ $alreadyBooked ? '0.6' : '1' }};">
                                <input type="radio" name="timeslot_id" value="{{ $slot['id'] }}" {{ $alreadyBooked ? 'disabled' : '' }} style="position: absolute; opacity: 0;">
                                <span class="slot-selection-check" aria-hidden="true">✓</span>
                                <span class="slot-time">{{ $slot['label'] }}</span>
                                <span class="slot-selection-label">Ausgewählt</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="inline-actions" style="margin-top: 24px; justify-content: space-between;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            @if(!$alreadyBooked)
                                <span class="selected-slot-summary" id="selected-slot-summary" aria-live="polite">
                                    Bitte einen Termin auswählen.
                                </span>
                                <button type="submit" class="button" id="book-button" disabled>Buchen</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </section>

        @if($teachers->isNotEmpty())
            <section class="panel teacher-navigation">
                <div class="teacher-grid">
                    @foreach($teachers as $listTeacher)
                        <a class="tile" href="{{ route('student.teachers.show', $listTeacher['slug']) }}">
                            <span class="tile-code">{{ $listTeacher['short'] }}</span>
                            <span class="tile-title">{{ $listTeacher['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <style>
        .slot-button {
            position: relative;
            overflow: hidden;
        }

        .slot-time {
            font-size: 1.05rem;
            font-weight: 800;
        }

        .slot-selection-check {
            position: absolute;
            top: 7px;
            right: 8px;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: var(--heading);
            color: white;
            font-size: 0.9rem;
            font-weight: 800;
            opacity: 0;
            transform: scale(0.65);
            transition: opacity 0.16s ease, transform 0.16s ease;
        }

        .slot-selection-label {
            color: var(--heading);
            font-size: 0.75rem;
            font-weight: 800;
            opacity: 0;
            transform: translateY(3px);
            transition: opacity 0.16s ease, transform 0.16s ease;
        }

        .slot-button.is-selected {
            border-color: var(--heading);
            border-width: 3px;
            background: color-mix(in srgb, var(--accent) 62%, white);
            box-shadow:
                0 0 0 4px color-mix(in srgb, var(--accent) 28%, transparent),
                0 10px 20px color-mix(in srgb, var(--panel-stroke) 24%, transparent);
            transform: translateY(-2px);
        }

        .slot-button.is-selected .slot-selection-check,
        .slot-button.is-selected .slot-selection-label {
            opacity: 1;
            transform: none;
        }

        .selected-slot-summary {
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 700;
        }

        .selected-slot-summary.has-selection {
            color: var(--heading);
        }
    </style>

    @if(!$alreadyBooked)
        <script>
            document.querySelectorAll('input[name="timeslot_id"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('book-button').disabled = false;
                    document.querySelectorAll('.slot-button').forEach(btn => {
                        btn.classList.remove('is-selected');
                    });
                    this.closest('.slot-button').classList.add('is-selected');

                    const summary = document.getElementById('selected-slot-summary');
                    summary.textContent = `Ausgewählt: ${this.closest('.slot-button').querySelector('.slot-time').textContent.trim()} Uhr`;
                    summary.classList.add('has-selection');
                });
            });
        </script>
    @endif
@endsection
