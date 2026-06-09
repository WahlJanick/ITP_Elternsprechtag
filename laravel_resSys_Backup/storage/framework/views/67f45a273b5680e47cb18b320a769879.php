<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Termine</h2>
                </div>

                <div class="hero-actions">
                    <a class="button" href="<?php echo e(route('student.teachers.index')); ?>">Buchen</a>
                    <?php if($summary['count'] > 0): ?>
                        <a class="ghost-button" href="<?php echo e(route('student.bookings')); ?>">Details</a>
                    <?php else: ?>
                        <span class="ghost-button is-disabled" aria-disabled="true">Details</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="hero-grid">
                <div class="summary-grid">
                        <article class="stat-card">
                            <span class="number"><?php echo e($summary['count']); ?></span>
                            <span class="mini-label">Gebuchte Termine</span>
                        </article>
                        <article class="stat-card">
                            <span class="number"><?php echo e($summary['assigned_teacher_count']); ?></span>
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

            <?php if($assignedTeachers->isEmpty()): ?>
                <p class="empty-copy">Aktuell ist noch kein Lehrerprofil deiner Klasse zugeordnet.</p>
            <?php else: ?>
                <div class="teacher-grid" data-expandable-teacher-grid>
                    <?php $__currentLoopData = $assignedTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            class="tile"
                            href="<?php echo e(route('student.teachers.show', $teacher['slug'])); ?>"
                            data-teacher-tile
                        >
                            <span class="tile-code"><?php echo e($teacher['short']); ?></span>
                            <span class="tile-title"><?php echo e($teacher['name']); ?></span>
                            <span class="status-chip <?php echo e($teacher['free_slots'] > 0 ? 'is-free' : 'is-booked'); ?>">
                                <?php echo e($teacher['free_slots'] > 0 ? $teacher['free_slots'].' freie Termine' : 'Aktuell keine freien Termine'); ?>

                            </span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="button-row" style="justify-content: center; margin-top: 14px;">
                    <button
                        type="button"
                        class="ghost-button"
                        data-teacher-grid-toggle
                        aria-expanded="false"
                        hidden
                    >
                        Mehr Lehrer
                    </button>
                </div>
                <style>
                    [data-teacher-tile][hidden] {
                        display: none;
                    }
                </style>
            <?php endif; ?>
        </section>
    </div>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Schüler-Start',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings', 'disabled' => $summary['count'] === 0],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/student/dashboard.blade.php ENDPATH**/ ?>