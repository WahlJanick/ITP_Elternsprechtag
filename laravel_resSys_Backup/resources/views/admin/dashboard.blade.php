@extends('layouts.portal', [
    'pageTitle' => 'Admin-Übersicht',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehreraktivitäten', 'href' => route('admin.dashboard').'#teacher-activity'],
        ['label' => 'Datumfestlegung', 'href' => route('admin.dashboard').'#parent-day'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Schulklassen', 'href' => route('admin.dashboard').'#classes'],
        ['label' => 'Räume', 'href' => route('admin.dashboard').'#rooms'],
        ['label' => 'Excel-Import', 'href' => route('admin.dashboard').'#excel-import'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel section-anchor" id="dashboard">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Dashboard</h2>
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

        <section class="panel section-anchor" id="teacher-activity">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehreraktivitäten</h2>
                </div>
            </div>

            @if($teacherDurationChanges->isNotEmpty())
                <div class="teacher-summary-grid">
                    @foreach($teacherDurationChanges as $teacher)
                        <article class="teacher-mini-card is-highlighted">
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
                            <div class="button-row">
                                <a class="button" href="{{ route('admin.teachers.show', $teacher['slug']) }}">Lehrer öffnen</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Änderungen an Termindauern gemeldet.</p>
            @endif
        </section>

        <section class="panel section-anchor" id="parent-day">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Datumfestlegung</h2>
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
                        <button type="submit" class="button">Speichern</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="panel section-anchor" id="teachers">
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
                            class="list-row is-interactive {{ $teacher['duration_changed'] ? 'is-highlighted' : '' }}"
                            tabindex="0"
                            data-selectable-row
                            data-search-item
                            data-search-text="{{ \Illuminate\Support\Str::lower($teacher['name'].' '.$teacher['short'].' '.$teacher['display_classes']) }}"
                        >
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $teacher['name'] }}</p>
                                <p class="meta-copy">{{ $teacher['short'] }} · {{ $teacher['display_classes'] }}</p>
                                <div class="button-row">
                                    <span class="badge">{{ $teacher['timeslot_duration_label'] }}</span>
                                    <span class="badge">{{ $teacher['free_slots'] }} frei</span>
                                    <span class="badge">{{ $teacher['booked_slots'] }} gebucht</span>
                                    @if($teacher['duration_changed'])
                                        <span class="status-chip">Termindauer geändert</span>
                                    @endif
                                </div>
                            </div>

                            <div class="list-row-actions actions-on-hover">
                                <a class="button" href="{{ route('admin.teachers.show', $teacher['slug']) }}">Bearbeiten</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Lehrer angelegt.</p>
            @endif
        </section>

        <section class="panel">
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

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehrerzugänge</h2>
                </div>
            </div>

            <div class="field" style="margin-bottom: 12px;">
                <label for="teacher_search">Suche</label>
                <input id="teacher_search" type="search" placeholder="Name oder E-Mail" data-teacher-search />
            </div>

            @if($teacherAccounts->isNotEmpty())
                <div class="list-stack">
                    @foreach($teacherAccounts as $account)
                        <article
                            class="list-row is-interactive"
                            tabindex="0"
                            data-selectable-row
                            data-account-search-item
                            data-search-text="{{ \Illuminate\Support\Str::lower($account['name'].' '.$account['email'].' '.$account['teacher_name']) }}"
                        >
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $account['name'] }}</p>
                                <p class="meta-copy">{{ $account['email'] }}</p>
                            </div>

                            <div class="list-row-actions actions-on-hover">
                                @if($account['teacher_slug'])
                                    <a class="button" href="{{ route('admin.teachers.show', $account['teacher_slug']) }}">Bearbeiten</a>
                                @else
                                    <form method="POST" action="{{ route('admin.teachers.accounts.create-profile', $account['user_id']) }}">
                                        @csrf
                                        <button type="submit" class="button">Profil anlegen</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.teachers.accounts.delete', $account['user_id']) }}">
                                    @csrf
                                    <button type="submit" class="danger-button">Löschen</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Lehrerzugänge angelegt.</p>
            @endif
        </section>

        <section class="panel section-anchor" id="classes" data-collapsible data-collapsed="true">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Schulklassen</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>Aufklappen</button>
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
                    <div class="class-list compact">
                        @foreach($schoolClasses as $schoolClass)
                            <div class="class-row compact">
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
                                    <button type="submit" class="ghost-button button-compact">OK</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-copy">Noch keine Klassen angelegt.</p>
                @endif
            </div>
        </section>

        <section class="panel section-anchor" id="rooms" data-collapsible data-collapsed="true">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Räume</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>Aufklappen</button>
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
                    <div class="class-list compact">
                        @foreach($rooms as $room)
                            <div class="class-row compact">
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
                                    <button type="submit" class="ghost-button button-compact">OK</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="empty-copy">Noch keine Räume angelegt.</p>
                @endif
            </div>
        </section>

        <section class="panel section-anchor" id="excel-import">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Excel-Import</h2>
                </div>
            </div>

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
                </div>

                @if($canGenerateTimeslots)
                    <div class="field field-inline">
                        <label for="create_timeslots">Termine direkt anlegen</label>
                        <div class="field-inline-row">
                            <label class="class-chip" style="padding: 6px 10px;">
                                <input id="create_timeslots" type="checkbox" name="create_timeslots" />
                                <span>Standard-Termine erzeugen</span>
                            </label>
                        </div>
                    </div>

                    <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));" data-import-timeslots>
                        <div class="field">
                            <label for="import_timeslot_duration">Termin-Dauer in Minuten</label>
                            <input
                                id="import_timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                step="5"
                                value="{{ old('timeslot_duration', '') }}"
                                placeholder="z.B. 10"
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_parent_day">Datum des Elternsprechtags</label>
                            <input
                                id="import_timeslot_parent_day"
                                type="text"
                                value="{{ $parentDayLabel }}"
                                readonly
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_start">Beginn</label>
                            <input
                                id="import_timeslot_start"
                                type="time"
                                name="timeslot_start"
                                value="{{ old('timeslot_start', '') }}"
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_end">Ende</label>
                            <input
                                id="import_timeslot_end"
                                type="time"
                                name="timeslot_end"
                                value="{{ old('timeslot_end', '') }}"
                            />
                        </div>

                        <div class="field">
                            <label for="import_timeslot_room">Raum</label>
                            <input
                                id="import_timeslot_room"
                                type="text"
                                name="timeslot_room"
                                value="{{ old('timeslot_room', '') }}"
                                placeholder="z.B. B201"
                            />
                        </div>
                    </div>
                @endif

                <div class="button-row">
                    <button type="submit" class="button">Import starten</button>
                </div>
            </form>
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

            const importForm = document.querySelector('[data-import]');
            if (importForm) {
                const toggle = importForm.querySelector('#create_timeslots');
                const timeslotFields = importForm.querySelectorAll('[data-import-timeslots] input');

                const sync = () => {
                    const enabled = toggle && toggle.checked;
                    timeslotFields.forEach((field) => {
                        field.disabled = !enabled;
                    });
                };

                if (toggle) {
                    toggle.addEventListener('change', sync);
                    sync();
                }
            }

            const collapsibles = document.querySelectorAll('[data-collapsible]');
            collapsibles.forEach((panel) => {
                const content = panel.querySelector('[data-collapsible-content]');
                const toggle = panel.querySelector('[data-collapsible-toggle]');

                if (!content || !toggle) {
                    return;
                }

                let collapsed = panel.dataset.collapsed === 'true';

                const renderCollapse = () => {
                    content.classList.toggle('is-collapsed', collapsed);
                    toggle.textContent = collapsed ? 'Aufklappen' : 'Zuklappen';
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
