@extends('layouts.portal', [
    'pageTitle' => 'Admin Dashboard',
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
                    <span class="eyebrow">Administration</span>
                    <h2 class="panel-title">Alle Lehrer, Schueler und Timeslots</h2>
                    <p class="panel-subtitle">
                        Die Admin-Variante fasst alle wichtigen Kennzahlen zusammen und fuehrt in die Detailseiten.
                    </p>
                </div>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span class="number">{{ $stats['students'] }}</span>
                    <span class="mini-label">Schueler gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['teachers'] }}</span>
                    <span class="mini-label">Lehrer gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['free_slots'] }}</span>
                    <span class="mini-label">Freie Slots</span>
                </article>
                <article class="stat-card">
                    <span class="number">{{ $stats['booked_slots'] }}</span>
                    <span class="mini-label">Gebuchte Slots</span>
                </article>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehrererstellung</span>
                    <h2 class="panel-title">Lehrer und Standard-Timeslots anlegen</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.teachers.accounts.store') }}" class="stack">
                @csrf

                <div class="stack">
                    <div>
                        <span class="eyebrow">E-Mails</span>
                    </div>

                    <div class="field">
                        <label for="teacher_emails">Lehrer-E-Mails</label>
                        <input
                            id="teacher_emails"
                            type="text"
                            name="teacher_emails"
                            value="{{ old('teacher_emails', '') }}"
                            placeholder="max.mustermann@schule.at, erika.muster@schule.at"
                        />
                    </div>
                </div>

                <div class="stack">
                    <div>
                        <span class="eyebrow">Klassen</span>
                    </div>

                    <div class="teacher-grid">
                        @foreach($classOptions as $className)
                            <label class="tile" style="cursor: pointer; min-height: 92px;">
                                <span class="tile-code">{{ substr($className, 0, 2) }}</span>
                                <span class="tile-title">{{ $className }}</span>
                                <span class="status-chip {{ in_array($className, old('classes', []), true) ? 'is-booked' : 'is-free' }}">
                                    <input
                                        type="checkbox"
                                        name="classes[]"
                                        value="{{ $className }}"
                                        {{ in_array($className, old('classes', []), true) ? 'checked' : '' }}
                                        style="margin-right: 8px;"
                                    />
                                    {{ in_array($className, old('classes', []), true) ? 'Gewaehlt' : 'Waehlen' }}
                                </span>
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
                </div>

                <div class="stack">
                    <div>
                        <span class="eyebrow">Timeslots</span>
                    </div>

                    <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div class="field">
                            <label for="timeslot_duration">Timeslot-Dauer in Minuten</label>
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
                            <label for="timeslot_day">Datum des Elternsprechtags</label>
                            <input
                                id="timeslot_day"
                                type="date"
                                name="timeslot_day"
                                value="{{ old('timeslot_day', '') }}"
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
                </div>

                <div class="field">
                    <div class="button-row">
                        <button type="submit" class="button">Lehrer anlegen</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Zugaenge</span>
                    <h2 class="panel-title">Angelegte Lehrerzugaenge</h2>
                </div>
            </div>
            @if($teacherAccounts->isNotEmpty())
                <div class="list-stack">
                    @foreach($teacherAccounts as $account)
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $account['name'] }}</p>
                                <p class="meta-copy">{{ $account['email'] }}</p>
                                <p class="meta-copy">{{ $account['teacher_name'] }}</p>
                                <p class="meta-copy">{{ $account['display_classes'] }}</p>
                            </div>

                            <div class="list-row-actions">
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
                                    <button type="submit" class="danger-button">Loeschen</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="empty-copy">Noch keine Lehrerzugaenge angelegt.</p>
            @endif
        </section>
    </div>
@endsection
