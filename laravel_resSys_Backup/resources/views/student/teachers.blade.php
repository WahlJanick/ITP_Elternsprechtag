@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Lehrerübersicht',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings', 'disabled' => ! $hasBookings],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Buchung</span>
                    <h2 class="panel-title">Deine Lehrer</h2>
                </div>

                @if($teachers->isNotEmpty())
                    <div class="teacher-search" data-teacher-search>
                        <button
                            type="button"
                            class="teacher-search-toggle"
                            aria-label="Lehrer suchen"
                            aria-expanded="false"
                            aria-controls="teacher-search-field"
                            data-teacher-search-toggle
                            data-no-default-icon
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-4-4"></path>
                            </svg>
                        </button>

                        <div class="teacher-search-field" id="teacher-search-field" data-teacher-search-field hidden>
                            <label class="sr-only" for="teacher-search-input">Lehrer suchen</label>
                            <input
                                type="search"
                                id="teacher-search-input"
                                placeholder="Lehrer suchen..."
                                autocomplete="off"
                                data-teacher-search-input
                            >
                        </div>
                    </div>
                @endif
            </div>

            @if($teachers->isEmpty())
                <p class="empty-copy">Für deine aktuelle Ansicht sind keine Lehrer hinterlegt.</p>
            @else
                <div class="teacher-grid" data-teacher-grid>
                    @foreach($teachers as $teacher)
                        <a
                            class="tile"
                            href="{{ route('student.teachers.show', $teacher['slug']) }}"
                            data-teacher-tile
                            data-search-text="{{ $teacher['short'].' '.$teacher['name'] }}"
                        >
                            <span class="tile-code">{{ $teacher['short'] }}</span>
                            <span class="tile-title">{{ $teacher['name'] }}</span>
                            <span class="status-chip {{ $teacher['free_slots'] > 0 ? 'is-free' : 'is-booked' }}">
                                {{ $teacher['free_slots'] > 0 ? $teacher['free_slots'].' freie Termine' : 'Aktuell keine freien Termine' }}
                            </span>
                        </a>
                    @endforeach
                </div>
                <p class="empty-copy" data-teacher-search-empty hidden>Kein Lehrer wurde gefunden.</p>
            @endif
        </section>
    </div>

    <style>
        .teacher-search {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .teacher-search-toggle {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--panel-stroke);
            border-radius: 10px;
            background: color-mix(in srgb, var(--panel-bg) 35%, white);
            color: var(--heading);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .teacher-search-toggle:hover,
        .teacher-search-toggle:focus-visible,
        .teacher-search.is-open .teacher-search-toggle {
            border-color: var(--accent);
            background: color-mix(in srgb, var(--accent) 22%, white);
            outline: none;
        }

        .teacher-search-toggle svg {
            width: 22px;
            height: 22px;
        }

        .teacher-search-field {
            width: min(280px, 48vw);
        }

        .teacher-search-field[hidden],
        [data-teacher-tile][hidden],
        [data-teacher-search-empty][hidden] {
            display: none;
        }

        .teacher-search-field input {
            width: 100%;
            min-height: 44px;
            padding: 10px 13px;
            border: 2px solid var(--panel-stroke);
            border-radius: 10px;
            background: white;
            color: var(--copy);
            font: inherit;
            outline: none;
        }

        .teacher-search-field input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 24%, transparent);
        }

        @media (max-width: 620px) {
            .teacher-search {
                width: 100%;
            }

            .teacher-search-field {
                flex: 1;
                width: auto;
            }
        }
    </style>

    @if($teachers->isNotEmpty())
        <script>
            (() => {
                const search = document.querySelector('[data-teacher-search]');
                const toggle = search?.querySelector('[data-teacher-search-toggle]');
                const field = search?.querySelector('[data-teacher-search-field]');
                const input = search?.querySelector('[data-teacher-search-input]');
                const tiles = Array.from(document.querySelectorAll('[data-teacher-tile]'));
                const emptyMessage = document.querySelector('[data-teacher-search-empty]');

                if (!search || !toggle || !field || !input || !emptyMessage) {
                    return;
                }

                const filterTeachers = () => {
                    const query = input.value.trim().toLocaleLowerCase('de');
                    let visibleTeachers = 0;

                    tiles.forEach((tile) => {
                        const matches = tile.dataset.searchText
                            .toLocaleLowerCase('de')
                            .includes(query);

                        tile.hidden = !matches;
                        visibleTeachers += matches ? 1 : 0;
                    });

                    emptyMessage.hidden = visibleTeachers > 0;
                };

                const openSearch = () => {
                    field.hidden = false;
                    search.classList.add('is-open');
                    toggle.setAttribute('aria-expanded', 'true');
                    input.focus();
                };

                const closeSearch = () => {
                    input.value = '';
                    filterTeachers();
                    field.hidden = true;
                    search.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                };

                toggle.addEventListener('click', () => {
                    if (field.hidden) {
                        openSearch();
                    } else {
                        closeSearch();
                    }
                });

                input.addEventListener('input', filterTeachers);
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeSearch();
                    }
                });
            })();
        </script>
    @endif
@endsection
