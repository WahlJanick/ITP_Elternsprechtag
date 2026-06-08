@extends('layouts.portal', [
    'pageTitle' => 'Admin-Übersicht',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="admin-menu-launcher" role="navigation" aria-label="Admin-Menüs">
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="overview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    Übersicht
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="parent-day">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                        <path d="M16 3v4M8 3v4M3 10h18"></path>
                    </svg>
                    Sprechtage
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="teachers">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M2 21v-2a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v2M19 8v6M22 11h-6"></path>
                    </svg>
                    Lehrer
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="organization">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-3h4v3"></path>
                    </svg>
                    Klassen & Räume
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="import">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12M7 10l5 5 5-5M5 21h14"></path>
                    </svg>
                    Import
                </button>
            </div>
        </section>

        @if($teacherDurationChangeCount > 0)
            <section class="notification-banner" data-admin-menu="overview" hidden>
                <div class="notification-banner-copy">
                    <strong>
                        {{ $teacherDurationChangeCount }} Lehrer {{ $teacherDurationChangeCount === 1 ? 'hat' : 'haben' }}
                        {{ $teacherDurationChangeCount === 1 ? 'seine' : 'ihre' }} Termindauer angepasst.
                    </strong>
                </div>

                <a class="button" href="#teacher-activity">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 12H2"></path>
                        <path d="M16 6l6 6-6 6"></path>
                    </svg>
                    Zu Lehreraktivitäten
                </a>
            </section>
        @endif

        <section class="panel section-anchor" id="dashboard" data-admin-menu="overview" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Dashboard <span class="badge">{{ $parentDayLabel }}</span></h2>
                </div>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span class="number">{{ $stats['students'] }}</span>
                    <span class="mini-label">Schüler gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['teachers'] }}</span>
                    <span class="mini-label">Lehrer gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['free_slots'] }}</span>
                    <span class="mini-label">Freie Termine</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['booked_slots'] }}</span>
                    <span class="mini-label">Gebuchte Termine</span>
                </article>
            </div>
        </section>

        <section class="panel section-anchor teacher-activity-panel" id="teacher-activity" data-admin-menu="overview" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehreraktivitäten</h2>
                </div>

                @if($teacherDurationChanges->isNotEmpty())
                    <div class="teacher-mini-actions teacher-activity-toolbar">
                        <form method="POST" action="{{ route('admin.teachers.activities.delete-all') }}">
                            @csrf
                            <button type="submit" class="danger-button button-compact">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                    <path d="M5 6l1 14h12l1-14"></path>
                                </svg>
                                Alle löschen
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if($teacherDurationChanges->isNotEmpty())
                <div class="teacher-summary-grid">
                    @foreach($teacherDurationChanges as $teacher)
                        <article class="teacher-mini-card is-highlighted" data-activity-card>
                            <div class="teacher-mini-top">
                                <div class="list-row-copy">
                                    <p class="list-row-title">{{ $teacher['name'] }}</p>
                                    <p class="meta-copy">{{ $teacher['timeslot_duration_label'] }}</p>
                                </div>
                                <span class="status-chip">Geändert</span>
                            </div>
                            <p class="meta-copy">
                                @if($teacher['duration_changed_label'])
                                    {{ $teacher['duration_changed_label'] }}
                                @endif
                            </p>
                            <div class="teacher-mini-actions actions-on-hover">
                                <a class="button button-compact" href="{{ route('admin.teachers.show', $teacher['slug']) }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M19 8v6"></path>
                                        <path d="M22 11h-6"></path>
                                    </svg>
                                    Lehrer öffnen
                                </a>
                                <form method="POST" action="{{ route('admin.teachers.activities.delete', $teacher['slug']) }}">
                                    @csrf
                                    <button type="submit" class="danger-button button-compact">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4h8v2"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M5 6l1 14h12l1-14"></path>
                                        </svg>
                                        Löschen
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Änderungen an Termindauern gemeldet.</p>
            @endif
        </section>

        <section class="panel section-anchor" id="parent-day" data-admin-menu="parent-day" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Datumfestlegung</h2>
                    <p class="panel-subtitle">Lege beliebig viele Elternsprechtage an und wechsle oben in der Leiste.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.parent-day.update') }}" class="stack">
                @csrf
                <div class="field field-inline">
                    <label for="parent_day">Datum des Elternsprechtags</label>
                    <div class="field-inline-row">
                        <input
                            id="parent_day"
                            type="date"
                            name="parent_day"
                            value="{{ old('parent_day', $parentDayValue) }}"
                            required
                        />
                        <button type="submit" class="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14"></path>
                                <path d="M5 12h14"></path>
                            </svg>
                            Anlegen
                        </button>
                    </div>
                </div>
            </form>

            @if($parentDays->isEmpty())
                <p class="empty-copy">Noch keine Elternsprechtage angelegt.</p>
            @else
                <div class="list-stack">
                    @foreach($parentDays as $day)
                        <article class="list-row is-interactive {{ $activeParentDay && $activeParentDay->id === $day->id ? 'is-highlighted' : '' }}">
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $day->date->format('d/m/Y') }}</p>
                                <p class="meta-copy">
                                    {{ $activeParentDay && $activeParentDay->id === $day->id ? 'Aktiver Elternsprechtag' : 'Nicht aktiv' }}
                                </p>
                            </div>
                            <div class="list-row-actions actions-on-hover">
                                <form method="POST" action="{{ route('admin.parent-days.delete', $day->id) }}">
                                    @csrf
                                    <button type="submit" class="danger-button">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M3 6h18"></path>
                                            <path d="M8 6V4h8v2"></path>
                                            <path d="M10 11v6"></path>
                                            <path d="M14 11v6"></path>
                                            <path d="M5 6l1 14h12l1-14"></path>
                                        </svg>
                                        Löschen
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="panel section-anchor admin-teachers-panel" id="teachers" data-admin-menu="teachers" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehrer</h2>
                </div>
            </div>

            <div class="field" style="margin-bottom: 12px;">
                <label for="teacher_overview_search">Suche</label>
                <input
                    id="teacher_overview_search"
                    type="search"
                    placeholder="Name, Kürzel oder Klasse"
                    data-teacher-overview-search
                />
            </div>

            @if($teachers->isNotEmpty())
                <div class="list-stack">
                    @foreach($teachers as $teacher)
                        <article
                            class="list-row admin-teacher-row is-interactive {{ $teacher['duration_changed'] ? 'is-highlighted' : '' }}"
                            tabindex="0"
                            data-selectable-row
                            data-search-item
                            data-search-text="{{ \Illuminate\Support\Str::lower($teacher['name'].' '.$teacher['short'].' '.$teacher['display_classes']) }}"
                        >
                            <div class="admin-teacher-head">
                                <p class="list-row-title">{{ $teacher['name'] }}</p>
                                <div class="list-row-actions actions-on-hover">
                                    @if($teacher['room'] === '')
                                        <button type="button" class="ghost-button" data-teacher-room-add>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                                                <path d="M12 8v6M9 11h6"></path>
                                            </svg>
                                            Raum hinzufügen
                                        </button>
                                    @endif
                                    <a class="ghost-button" href="{{ route('admin.teachers.appointments', $teacher['slug']) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                            <path d="M16 2v4"></path>
                                            <path d="M8 2v4"></path>
                                            <path d="M3 10h18"></path>
                                        </svg>
                                        Termine
                                    </a>
                                    <a class="button" href="{{ route('admin.teachers.show', $teacher['slug']) }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                        Bearbeiten
                                    </a>
                                </div>
                            </div>

                            <div class="teacher-class-badges" aria-label="Zugeordnete Klassen">
                                @forelse($teacher['classes'] as $className)
                                    <span class="teacher-class-badge">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m3 10 9-5 9 5-9 5Z"></path>
                                            <path d="M7 12.5V17c2.8 2 7.2 2 10 0v-4.5"></path>
                                        </svg>
                                        {{ $className }}
                                    </span>
                                @empty
                                    <span class="teacher-class-badge is-all">Alle Klassen</span>
                                @endforelse
                            </div>

                            <form
                                method="POST"
                                action="{{ route('admin.teachers.room.update', $teacher['slug']) }}"
                                class="teacher-room-form admin-teacher-room-row {{ $teacher['room'] === '' ? 'is-unassigned' : '' }}"
                                data-teacher-room-form
                            >
                                @csrf
                                <label class="sr-only" for="teacher_room_{{ $teacher['reference_id'] }}">
                                    Raum für {{ $teacher['name'] }}
                                </label>
                                <button
                                    type="button"
                                    class="teacher-room-inline-display"
                                    data-teacher-room-edit
                                    title="Doppelklicken zum Bearbeiten"
                                    aria-label="Raum {{ $teacher['room_label'] }} bearbeiten"
                                >
                                    <svg class="room-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                                    </svg>
                                    <span>Raum: {{ $teacher['room_label'] }}</span>
                                    <svg class="edit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                    </svg>
                                </button>
                                <input
                                    id="teacher_room_{{ $teacher['reference_id'] }}"
                                    type="text"
                                    name="room"
                                    value="{{ $teacher['room'] }}"
                                    list="admin_room_options"
                                    placeholder="Raum festlegen"
                                    data-teacher-room-input
                                    data-original-value="{{ $teacher['room'] }}"
                                    hidden
                                />
                            </form>

                            <div class="admin-teacher-slot-counts">
                                <span class="badge">{{ $teacher['free_slots'] }} frei</span>
                                <span class="badge">{{ $teacher['booked_slots'] }} gebucht</span>
                                @if($teacher['duration_changed'])
                                    <span class="status-chip">Termindauer geändert</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Lehrer angelegt.</p>
            @endif

            <datalist id="admin_room_options">
                @foreach($rooms as $room)
                    <option value="{{ $room->name }}"></option>
                @endforeach
            </datalist>
        </section>

        <section class="panel admin-teachers-panel" id="teacher-create" data-admin-menu="teachers" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehrer anlegen</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.accounts.store') }}" class="stack wizard" data-wizard="teacher">
                @csrf
                <div class="wizard-steps">
                    <div class="wizard-step-indicator is-active" data-step-indicator>1. Zugang</div>
                    <div class="wizard-step-indicator" data-step-indicator>2. Klassen</div>
                    <div class="wizard-step-indicator" data-step-indicator>3. Termine</div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="field">
                        <label for="teacher_emails">Lehrer (Vorname Nachname &lt;E-Mail&gt;)</label>
                        <input
                            id="teacher_emails"
                            type="text"
                            name="teacher_emails"
                            value="{{ old('teacher_emails', '') }}"
                            placeholder="Max Mustermann <max.mustermann@schule.at>, Erika Muster <erika.muster@schule.at>"
                        />
                    </div>

                    <div class="button-row">
                        <button type="button" class="button" data-step-next>Weiter</button>
                    </div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="class-grid-compact">
                        @foreach($classOptions as $className)
                            <label class="class-chip">
                                <input
                                    type="checkbox"
                                    name="classes[]"
                                    value="{{ $className }}"
                                    {{ in_array($className, old('classes', []), true) ? 'checked' : '' }}
                                />
                                <span>{{ $className }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="field">
                        <label for="additional_classes">Weitere Klassen</label>
                        <input
                            id="additional_classes"
                            type="text"
                            name="additional_classes"
                            value="{{ old('additional_classes', '') }}"
                            placeholder="z.B. 3AHIT, 4AHIT"
                        />
                    </div>

                    <div class="button-row">
                        <button type="button" class="ghost-button" data-step-prev>Zurück</button>
                        <button type="button" class="button" data-step-next>Weiter</button>
                    </div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div class="field">
                            <label for="timeslot_duration">Termin-Dauer in Minuten</label>
                            <input
                                id="timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                step="5"
                                value="{{ old('timeslot_duration', '') }}"
                                placeholder="z.B. 10"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_parent_day">Datum des Elternsprechtags</label>
                            <input
                                id="timeslot_parent_day"
                                type="text"
                                value="{{ $parentDayLabel }}"
                                readonly
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_start">Beginn</label>
                            <input
                                id="timeslot_start"
                                type="time"
                                name="timeslot_start"
                                value="{{ old('timeslot_start', '') }}"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_end">Ende</label>
                            <input
                                id="timeslot_end"
                                type="time"
                                name="timeslot_end"
                                value="{{ old('timeslot_end', '') }}"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_room">Raum</label>
                            <input
                                id="timeslot_room"
                                type="text"
                                name="timeslot_room"
                                value="{{ old('timeslot_room', '') }}"
                                placeholder="z.B. B201"
                            />
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="button" class="ghost-button" data-step-prev>Zurück</button>
                        <button type="submit" class="button">Lehrer anlegen</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="panel section-anchor" id="classes" data-admin-menu="organization" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Schulklassen</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form method="POST" action="{{ route('admin.classes.store') }}" class="stack">
                    @csrf
                    <div class="field field-inline">
                        <label for="new_class">Neue Klasse</label>
                        <div class="field-inline-row class-create-row">
                            <input
                                id="new_class"
                                type="text"
                                name="name"
                                placeholder="z.B. 3AHIT"
                                required
                            />
                            <button type="submit" class="button">Anlegen</button>
                            <button type="button" class="ghost-button button-compact" data-open-modal="class-bulk">Mehrere hinzufügen</button>
                        </div>
                    </div>
                </form>

                @if($schoolClasses->isNotEmpty())
                    <div class="class-list compact-grid">
                        @foreach($schoolClasses as $schoolClass)
                            <div class="class-row compact-grid">
                                <form
                                    method="POST"
                                    action="{{ route('admin.classes.update', $schoolClass) }}"
                                    class="class-row-form"
                                    data-class-form
                                >
                                    @csrf
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $schoolClass->name }}"
                                        class="class-input compact"
                                        data-class-input
                                    />
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-copy">Noch keine Klassen angelegt.</p>
                @endif
            </div>
        </section>

        <section class="panel section-anchor" id="rooms" data-admin-menu="organization" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Räume</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form method="POST" action="{{ route('admin.rooms.store') }}" class="stack">
                    @csrf
                    <div class="field field-inline">
                        <label for="new_room">Neuer Raum</label>
                        <div class="field-inline-row class-create-row">
                            <input
                                id="new_room"
                                type="text"
                                name="name"
                                placeholder="z.B. Raum 101"
                                required
                            />
                            <button type="submit" class="button">Anlegen</button>
                            <button type="button" class="ghost-button button-compact" data-open-modal="room-bulk">Mehrere hinzufügen</button>
                        </div>
                    </div>
                </form>

                @if($rooms->isNotEmpty())
                    <div class="class-list compact-grid">
                        @foreach($rooms as $room)
                            <div class="class-row compact-grid">
                                <form
                                    method="POST"
                                    action="{{ route('admin.rooms.update', $room) }}"
                                    class="class-row-form"
                                    data-room-form
                                >
                                    @csrf
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ $room->name }}"
                                        class="class-input compact"
                                        data-room-input
                                    />
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-copy">Noch keine Räume angelegt.</p>
                @endif
            </div>
        </section>

        <section class="panel section-anchor" id="excel-import" data-admin-menu="import" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Excel-Import</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form id="import-parent-day-create" method="POST" action="{{ route('admin.parent-day.update') }}">
                    @csrf
                    <input type="hidden" name="return_to" value="import" />
                </form>

                <form method="POST" action="{{ route('admin.teachers.import') }}" enctype="multipart/form-data" class="stack" data-import>
                    @csrf

                    <div class="field">
                        <label for="teacher_file">Excel-Datei (xlsx)</label>
                        <input
                            id="teacher_file"
                            type="file"
                            name="teacher_file"
                            accept=".xlsx,.xls"
                            required
                        />
                        <p class="hint">
                            Unterstützt benannte Spalten sowie das Standardformat:
                            A Kürzel, B Nachname, C Vorname, D Klassen.
                        </p>
                    </div>

                    <div class="field">
                        <label>Elternsprechtage für die importierten Lehrer</label>
                        @if($parentDays->isNotEmpty())
                            @php
                                $selectedImportParentDays = collect(
                                    old('parent_day_ids', [$activeParentDay?->id])
                                )->map(fn ($id) => (int) $id)->all();
                            @endphp
                            <div class="button-row import-days-toolbar">
                                <button type="button" class="ghost-button button-compact" data-import-days-all>
                                    Alle auswählen
                                </button>
                                <button type="button" class="ghost-button button-compact" data-import-days-none>
                                    Auswahl löschen
                                </button>
                            </div>
                            <div class="import-parent-days">
                                @foreach($parentDays as $day)
                                    <label class="class-chip">
                                        <input
                                            type="checkbox"
                                            name="parent_day_ids[]"
                                            value="{{ $day->id }}"
                                            @checked(in_array($day->id, $selectedImportParentDays, true))
                                        />
                                        <span>{{ $day->date->format('d.m.Y') }}</span>
                                    </label>
                                @endforeach
                                <div class="import-parent-day-add">
                                    <input
                                        type="date"
                                        name="parent_day"
                                        form="import-parent-day-create"
                                        aria-label="Elternsprechtag hinzufügen"
                                        data-import-parent-day-input
                                        required
                                    />
                                </div>
                            </div>
                            <p class="hint">Wähle alle Tage aus, an denen diese Lehrer Termine erhalten sollen.</p>
                        @else
                            <p class="empty-copy">Lege direkt den ersten Elternsprechtag an.</p>
                            <div class="import-parent-days">
                                <div class="import-parent-day-add">
                                    <input
                                        type="date"
                                        name="parent_day"
                                        form="import-parent-day-create"
                                        aria-label="Elternsprechtag hinzufügen"
                                        data-import-parent-day-input
                                        required
                                    />
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div class="field">
                            <label for="import_timeslot_duration">Standard-Termindauer</label>
                            <input
                                id="import_timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                max="120"
                                step="5"
                                value="{{ old('timeslot_duration', 10) }}"
                                required
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_start">Beginn</label>
                            <input
                                id="import_timeslot_start"
                                type="time"
                                name="timeslot_start"
                                value="{{ old('timeslot_start', '17:00') }}"
                                step="60"
                                required
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_end">Ende</label>
                            <input
                                id="import_timeslot_end"
                                type="time"
                                name="timeslot_end"
                                value="{{ old('timeslot_end', '19:00') }}"
                                step="60"
                                required
                            />
                        </div>

                    </div>

                    <div class="button-row">
                        <button type="submit" class="button" @disabled($parentDays->isEmpty())>Import starten</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="modal" data-modal="class-bulk" aria-hidden="true">
        <div class="modal-overlay" data-close-modal></div>
        <div class="modal-content">
            <h3 class="panel-title" style="font-size: 1.3rem;">Mehrere Klassen hinzufügen</h3>
            <form method="POST" action="{{ route('admin.classes.store') }}" class="stack">
                @csrf
                <div class="field">
                    <label for="bulk_classes">Klassen (kommagetrennt)</label>
                    <textarea id="bulk_classes" name="name" rows="3" placeholder="Klasse 1A, Klasse 1B, Klasse 2A"></textarea>
                </div>
                <div class="button-row">
                    <button type="submit" class="button">Hinzufügen</button>
                    <button type="button" class="ghost-button" data-close-modal>Schließen</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" data-modal="room-bulk" aria-hidden="true">
        <div class="modal-overlay" data-close-modal></div>
        <div class="modal-content">
            <h3 class="panel-title" style="font-size: 1.3rem;">Mehrere Räume hinzufügen</h3>
            <form method="POST" action="{{ route('admin.rooms.store') }}" class="stack">
                @csrf
                <div class="field">
                    <label for="bulk_rooms">Räume (kommagetrennt)</label>
                    <textarea id="bulk_rooms" name="name" rows="3" placeholder="Raum 101, Raum 102, Raum 103"></textarea>
                </div>
                <div class="button-row">
                    <button type="submit" class="button">Hinzufügen</button>
                    <button type="button" class="ghost-button" data-close-modal>Schließen</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const adminMenuButtons = Array.from(document.querySelectorAll('[data-admin-menu-button]'));
            const adminMenus = Array.from(document.querySelectorAll('[data-admin-menu]'));
            const hashMenus = {
                dashboard: 'overview',
                'teacher-activity': 'overview',
                'parent-day': 'parent-day',
                teachers: 'teachers',
                'teacher-create': 'teachers',
                classes: 'organization',
                rooms: 'organization',
                'excel-import': 'import',
            };

            const openAdminMenu = (menuName, updateHash = false) => {
                adminMenus.forEach((menu) => {
                    menu.hidden = menu.dataset.adminMenu !== menuName;
                });

                adminMenuButtons.forEach((button) => {
                    const active = button.dataset.adminMenuButton === menuName;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', String(active));
                });

                if (updateHash) {
                    const target = adminMenus.find((menu) => menu.dataset.adminMenu === menuName && menu.id);
                    if (target) {
                        history.replaceState(null, '', `#${target.id}`);
                    }
                }
            };

            adminMenuButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    openAdminMenu(button.dataset.adminMenuButton, true);
                });
            });

            const initialHash = window.location.hash.slice(1);
            if (hashMenus[initialHash]) {
                openAdminMenu(hashMenus[initialHash]);
            }

            const wizard = document.querySelector('[data-wizard="teacher"]');
            if (wizard) {
                const steps = Array.from(wizard.querySelectorAll('[data-step]'));
                const indicators = Array.from(wizard.querySelectorAll('[data-step-indicator]'));
                let current = 0;

                const render = () => {
                    steps.forEach((step, index) => {
                        step.style.display = index === current ? 'grid' : 'none';
                    });
                    indicators.forEach((indicator, index) => {
                        indicator.classList.toggle('is-active', index === current);
                    });
                };

                wizard.addEventListener('click', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    if (target.closest('[data-step-next]')) {
                        current = Math.min(steps.length - 1, current + 1);
                        render();
                    }

                    if (target.closest('[data-step-prev]')) {
                        current = Math.max(0, current - 1);
                        render();
                    }
                });

                render();
            }

            const importDayCheckboxes = Array.from(
                document.querySelectorAll('.import-parent-days input[name="parent_day_ids[]"]')
            );

            document.querySelector('[data-import-days-all]')?.addEventListener('click', () => {
                importDayCheckboxes.forEach((checkbox) => {
                    checkbox.checked = true;
                });
            });

            document.querySelector('[data-import-days-none]')?.addEventListener('click', () => {
                importDayCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
            });

            const importParentDayInput = document.querySelector('[data-import-parent-day-input]');
            importParentDayInput?.addEventListener('blur', () => {
                if (importParentDayInput.value && importParentDayInput.checkValidity()) {
                    document.querySelector('#import-parent-day-create')?.requestSubmit();
                }
            });

            document.querySelectorAll('[data-teacher-room-form]').forEach((form) => {
                const input = form.querySelector('[data-teacher-room-input]');
                const editTrigger = form.querySelector('[data-teacher-room-edit]');
                const teacherRow = form.closest('.admin-teacher-row');
                const addTrigger = teacherRow?.querySelector('[data-teacher-room-add]');

                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const openRoomEditor = () => {
                    editTrigger.hidden = true;
                    input.hidden = false;
                    input.focus();
                    input.select();
                };

                editTrigger?.addEventListener('dblclick', openRoomEditor);
                addTrigger?.addEventListener('click', openRoomEditor);

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        input.blur();
                    }
                });

                input.addEventListener('blur', () => {
                    const currentValue = input.value.trim();
                    const originalValue = input.dataset.originalValue?.trim() ?? '';

                    if (currentValue !== originalValue) {
                        input.value = currentValue;
                        form.requestSubmit();
                        return;
                    }

                    if (editTrigger) {
                        input.hidden = true;
                        editTrigger.hidden = false;
                    }
                });
            });

            const collapsibles = document.querySelectorAll('[data-collapsible]');
            collapsibles.forEach((panel) => {
                const content = panel.querySelector('[data-collapsible-content]');
                const toggle = panel.querySelector('[data-collapsible-toggle]');
                const label = toggle?.querySelector('[data-collapsible-label]');
                const openIcon = toggle?.querySelector('[data-collapsible-icon-open]');
                const closeIcon = toggle?.querySelector('[data-collapsible-icon-close]');

                if (!content || !toggle) {
                    return;
                }

                let collapsed = panel.dataset.collapsed === 'true';

                const renderCollapse = () => {
                    content.classList.toggle('is-collapsed', collapsed);
                    if (label) {
                        label.textContent = collapsed ? 'Aufklappen' : 'Zuklappen';
                    }
                    if (openIcon) {
                        openIcon.hidden = !collapsed;
                    }
                    if (closeIcon) {
                        closeIcon.hidden = collapsed;
                    }
                    toggle.setAttribute('aria-expanded', String(!collapsed));
                };

                toggle.addEventListener('click', () => {
                    collapsed = !collapsed;
                    renderCollapse();
                });

                renderCollapse();
            });

            const openModalButtons = document.querySelectorAll('[data-open-modal]');
            const closeModalButtons = document.querySelectorAll('[data-close-modal]');

            const openModal = (name) => {
                const modal = document.querySelector(`[data-modal="${name}"]`);
                if (modal) {
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                }
            };

            const closeModal = (modal) => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            };

            openModalButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const name = button.getAttribute('data-open-modal');
                    if (name) {
                        openModal(name);
                    }
                });
            });

            closeModalButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('[data-modal]');
                    if (modal) {
                        closeModal(modal);
                    }
                });
            });

            const classInputs = document.querySelectorAll('[data-class-input]');
            classInputs.forEach((input) => {
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        const form = input.closest('[data-class-form]');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });

            const roomInputs = document.querySelectorAll('[data-room-input]');
            roomInputs.forEach((input) => {
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        const form = input.closest('[data-room-form]');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });

            const setupSearch = (inputSelector, itemSelector) => {
                const searchInput = document.querySelector(inputSelector);
                if (!searchInput) {
                    return;
                }

                const items = Array.from(document.querySelectorAll(itemSelector));
                const syncSearch = () => {
                    const term = searchInput.value.trim().toLowerCase();
                    items.forEach((item) => {
                        const text = item.getAttribute('data-search-text') || '';
                        item.style.display = text.includes(term) ? '' : 'none';
                    });
                };

                searchInput.addEventListener('input', syncSearch);
                syncSearch();
            };

            setupSearch('[data-teacher-search]', '[data-account-search-item]');
            setupSearch('[data-teacher-overview-search]', '[data-search-item]');

            const selectableRows = document.querySelectorAll('[data-selectable-row]');
            selectableRows.forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLElement && event.target.closest('a, button, input, form, textarea, select, label')) {
                        return;
                    }

                    const isSelected = row.classList.contains('is-selected');
                    selectableRows.forEach((entry) => entry.classList.remove('is-selected'));
                    if (!isSelected) {
                        row.classList.add('is-selected');
                    }
                });

                row.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        row.click();
                    }
                });
            });

        })();
    </script>
@endsection
