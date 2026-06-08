@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Lehrer-Detail',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Start', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrer-Detail</span>
                    <h2 class="panel-title">{{ $teacher['name'] }} ({{ $teacher['short'] }})</h2>
                </div>

                <a class="close-button" href="{{ route('student.teachers.index') }}" aria-label="Zurück">&times;</a>
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

                    <p class="mini-label" style="margin-bottom: 12px;">{{ $freeSlots->first()['date_label'] }}</p>

                    {{-- Custom time dropdown --}}
                    <div class="time-dropdown" id="time-dropdown">
                        <button type="button" class="time-dropdown-trigger {{ $alreadyBooked ? 'is-disabled' : '' }}" id="time-trigger" {{ $alreadyBooked ? 'disabled' : '' }}>
                            <span class="time-trigger-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            </span>
                            <span class="time-trigger-label" id="time-trigger-label">Uhrzeit wählen …</span>
                            <span class="time-trigger-arrow">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </span>
                        </button>

                        <div class="time-dropdown-panel" id="time-panel" hidden>
                            <div class="time-panel-inner">
                                @foreach($freeSlots as $slot)
                                    <button type="button"
                                        class="time-option"
                                        data-value="{{ $slot['id'] }}"
                                        data-label="{{ $slot['label'] }}">
                                        <span class="time-option-time">{{ $slot['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="timeslot_id" id="timeslot-hidden" value="">

                    <style>
                        .time-dropdown { position: relative; margin-bottom: 4px; }

                        .time-dropdown-trigger {
                            width: 100%;
                            min-height: 64px;
                            display: flex;
                            align-items: center;
                            gap: 14px;
                            padding: 0 18px;
                            border-radius: 14px;
                            border: 3px solid var(--panel-stroke);
                            background: rgba(255,255,255,0.72);
                            color: var(--copy);
                            font: inherit;
                            font-weight: 700;
                            font-size: 1.1rem;
                            cursor: pointer;
                            text-align: left;
                            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
                        }

                        .time-dropdown-trigger:hover:not(:disabled) {
                            border-color: #2f64bf;
                            background: white;
                            box-shadow: 0 6px 18px rgba(23,63,123,0.12);
                        }

                        .time-dropdown-trigger.is-selected {
                            border-color: #2f64bf;
                            background: white;
                        }

                        .time-dropdown-trigger.is-open {
                            border-color: #2f64bf;
                            border-bottom-left-radius: 0;
                            border-bottom-right-radius: 0;
                            background: white;
                        }

                        .time-dropdown-trigger.is-disabled {
                            opacity: 0.55;
                            cursor: not-allowed;
                        }

                        .time-trigger-icon {
                            width: 38px; height: 38px;
                            border-radius: 10px;
                            background: var(--panel-strong);
                            display: flex; align-items: center; justify-content: center;
                            flex-shrink: 0;
                            color: #2d5797;
                        }

                        .time-trigger-label { flex: 1; }

                        .time-trigger-arrow {
                            color: #2d5797;
                            transition: transform 0.22s ease;
                            flex-shrink: 0;
                        }

                        .time-dropdown-trigger.is-open .time-trigger-arrow {
                            transform: rotate(180deg);
                        }

                        .time-dropdown-panel {
                            position: absolute;
                            left: 0; right: 0;
                            top: 100%;
                            z-index: 200;
                            border: 3px solid #2f64bf;
                            border-top: none;
                            border-bottom-left-radius: 14px;
                            border-bottom-right-radius: 14px;
                            background: white;
                            box-shadow: 0 16px 32px rgba(16,50,100,0.18);
                            overflow: hidden;
                        }

                        .time-panel-inner {
                            max-height: 52vh;
                            overflow-y: auto;
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 0;
                            padding: 8px;
                            gap: 6px;
                        }

                        .time-option {
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            min-height: 58px;
                            border-radius: 10px;
                            border: 2px solid var(--panel-stroke);
                            background: var(--panel-bg);
                            color: var(--copy);
                            font: inherit;
                            font-weight: 800;
                            font-size: 1.1rem;
                            cursor: pointer;
                            transition: background 0.15s, border-color 0.15s, transform 0.12s;
                        }

                        .time-option:hover {
                            background: var(--panel-strong);
                            border-color: #2f64bf;
                            transform: scale(1.03);
                        }

                        .time-option.is-selected {
                            background: #1a55aa;
                            border-color: #1a55aa;
                            color: white;
                        }
                    </style>

                    <script>
                        (function () {
                            const trigger  = document.getElementById('time-trigger');
                            const panel    = document.getElementById('time-panel');
                            const label    = document.getElementById('time-trigger-label');
                            const hidden   = document.getElementById('timeslot-hidden');
                            if (!trigger) return;

                            trigger.addEventListener('click', function () {
                                const open = !panel.hidden;
                                panel.hidden = open;
                                trigger.classList.toggle('is-open', !open);
                            });

                            document.querySelectorAll('.time-option').forEach(function (opt) {
                                opt.addEventListener('click', function () {
                                    document.querySelectorAll('.time-option').forEach(o => o.classList.remove('is-selected'));
                                    opt.classList.add('is-selected');
                                    label.textContent = opt.dataset.label;
                                    hidden.value = opt.dataset.value;
                                    trigger.classList.add('is-selected');
                                    trigger.classList.remove('is-open');
                                    panel.hidden = true;
                                    const bookBtn = document.getElementById('book-button');
                                    if (bookBtn) bookBtn.disabled = false;
                                });
                            });

                            document.addEventListener('click', function (e) {
                                if (!document.getElementById('time-dropdown').contains(e.target)) {
                                    panel.hidden = true;
                                    trigger.classList.remove('is-open');
                                }
                            });
                        })();
                    </script>

                    <div class="inline-actions" style="margin-top: 24px; justify-content: flex-end;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <span class="status-chip {{ $alreadyBooked ? 'is-booked' : 'is-free' }}">
                                {{ $alreadyBooked ? 'Bereits ein Termin vorhanden' : 'Direkt buchbar' }}
                            </span>
                            @if(!$alreadyBooked)
                                <button type="submit" class="button" id="book-button" disabled>Buchen</button>
                            @endif
                        </div>
                    </div>
                </form>
            @endif
        </section>

    </div>

@endsection
