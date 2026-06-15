@extends('layouts.portal', [
    'pageTitle' => 'Schüler-Start',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings', 'disabled' => $summary['count'] === 0],
    ],
])

@section('content')
    <div class="stack student-dashboard-screen">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Termine</h2>
                </div>
            </div>

            <div class="hero-grid">
                <div class="summary-grid">
                        <article class="stat-card">
                            <span class="number">{{ $summary['count'] }}</span>
                            <span class="mini-label">Gebuchte Termine</span>
                        </article>
                        <article class="stat-card">
                            <span class="number">{{ $summary['assigned_teacher_count'] }}</span>
                            <span class="mini-label">Lehrer deiner Klasse</span>
                        </article>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Lehrer</h2>
                </div>
            </div>

            @if($assignedTeachers->isEmpty())
                <p class="empty-copy">Aktuell ist noch kein Lehrerprofil deiner Klasse zugeordnet.</p>
            @else
                <div class="teacher-grid" data-expandable-teacher-grid>
                    @foreach($assignedTeachers as $teacher)
                        <a
                            class="tile"
                            href="{{ route('student.teachers.show', $teacher['slug']) }}"
                            data-teacher-tile
                        >
                            <span class="tile-code">{{ $teacher['short'] }}</span>
                            <span class="tile-title">{{ $teacher['name'] }}</span>
                            <span class="status-chip {{ $teacher['free_slots'] > 0 ? 'is-free' : 'is-booked' }}">
                                {{ $teacher['free_slots'] > 0 ? $teacher['free_slots'].' freie Termine' : 'Aktuell keine freien Termine' }}
                            </span>
                        </a>
                    @endforeach
                </div>
                <div class="button-row" style="justify-content: center; margin-top: 14px;">
                    <button
                        type="button"
                        class="ghost-button teacher-grid-toggle-desktop"
                        data-teacher-grid-toggle
                        aria-expanded="false"
                        hidden
                    >
                        Mehr Lehrer
                    </button>
                    <a
                        class="ghost-button teacher-grid-link-mobile"
                        href="{{ route('student.teachers.index') }}"
                    >
                        Alle anzeigen
                    </a>
                </div>
                <style>
                    [data-teacher-tile][hidden] {
                        display: none;
                    }

                    .teacher-grid-link-mobile {
                        display: none;
                    }

                    @media (max-width: 860px) {
                        .teacher-grid-toggle-desktop {
                            display: none !important;
                        }

                        .teacher-grid-link-mobile {
                            display: inline-flex;
                        }
                    }
                </style>
            @endif
        </section>
    </div>

    <style>
        .student-dashboard-screen,
        .student-dashboard-screen > *,
        .student-dashboard-screen .hero-grid,
        .student-dashboard-screen .summary-grid,
        .student-dashboard-screen .teacher-grid {
            min-width: 0;
        }

        @media (max-width: 640px) {
            .student-dashboard-screen {
                gap: 12px;
            }

            .student-dashboard-screen .panel {
                padding: 12px;
                border-width: 2px;
            }

            .student-dashboard-screen .panel-header {
                margin-bottom: 10px;
            }

            .student-dashboard-screen .hero-grid {
                display: block;
            }

            .student-dashboard-screen .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
            }

            .student-dashboard-screen .stat-card {
                min-width: 0;
                min-height: 104px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 12px 8px;
                text-align: center;
            }

            .student-dashboard-screen .number {
                font-size: 1.9rem;
            }

            .student-dashboard-screen .mini-label {
                font-size: 0.8rem;
                line-height: 1.25;
                overflow-wrap: anywhere;
            }

            .student-dashboard-screen .teacher-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 10px;
            }

            .student-dashboard-screen .tile {
                width: 100%;
                min-width: 0;
                min-height: 116px;
                padding: 13px 10px;
            }

            .student-dashboard-screen .tile-title,
            .student-dashboard-screen .status-chip {
                max-width: 100%;
                overflow-wrap: anywhere;
                white-space: normal;
                text-align: center;
            }

            .student-dashboard-screen .teacher-grid-link-mobile {
                width: 100%;
                min-height: 44px;
            }
        }

        @media (max-width: 340px) {
            .student-dashboard-screen .summary-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .student-dashboard-screen .stat-card {
                min-height: 88px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const grid = document.querySelector('[data-expandable-teacher-grid]');
            const toggle = document.querySelector('[data-teacher-grid-toggle]');

            if (!grid || !toggle) {
                return;
            }

            const tiles = Array.from(grid.querySelectorAll('[data-teacher-tile]'));
            let expanded = false;
            let resizeFrame = null;

            const updateGrid = () => {
                tiles.forEach((tile) => {
                    tile.hidden = false;
                });

                if (expanded || tiles.length === 0) {
                    toggle.hidden = true;
                    return;
                }

                const firstRowTop = tiles[0].offsetTop;
                const columns = tiles.findIndex((tile) => tile.offsetTop > firstRowTop);
                const columnCount = columns === -1 ? tiles.length : columns;
                const visibleCount = columnCount * 2;

                tiles.forEach((tile, index) => {
                    tile.hidden = index >= visibleCount;
                });

                toggle.hidden = tiles.length <= visibleCount;
            };

            toggle.addEventListener('click', () => {
                expanded = true;
                toggle.setAttribute('aria-expanded', 'true');
                updateGrid();
            });

            window.addEventListener('resize', () => {
                if (expanded) {
                    return;
                }

                window.cancelAnimationFrame(resizeFrame);
                resizeFrame = window.requestAnimationFrame(updateGrid);
            });

            updateGrid();
        });
    </script>
@endsection
