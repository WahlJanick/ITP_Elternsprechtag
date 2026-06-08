<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Startseite</span>
                    <h2 class="hero-title">Gebuchte Termine</h2>
                    <p class="hero-copy">
                        Dein Schnellzugriff auf gebuchte Termine.
                    </p>
                </div>

                <div class="hero-actions">
                    <a class="button" href="<?php echo e(route('student.teachers.index')); ?>">Buchen</a>
                    <a class="ghost-button" href="<?php echo e(route('student.bookings')); ?>">Details</a>
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
                    <span class="eyebrow">Unterricht</span>
                    <h2 class="panel-title">Diese Lehrer unterrichten dich</h2>
                    <p class="panel-subtitle">
                        <?php if($currentClass): ?>
                            Klasse <?php echo e($currentClass); ?>

                        <?php else: ?>
                            Deine Klasse konnte noch nicht automatisch erkannt werden.
                        <?php endif; ?>
                    </p>
                </div>

                <a class="ghost-button" href="<?php echo e(route('student.teachers.index')); ?>">Alle Lehrer ansehen</a>
            </div>

            <?php if($assignedTeachers->isEmpty()): ?>
                <p class="empty-copy">Aktuell ist noch kein Lehrerprofil deiner Klasse zugeordnet.</p>
            <?php else: ?>
                <div class="teacher-grid">
                    <?php $__currentLoopData = $assignedTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="tile" href="<?php echo e(route('student.teachers.show', $teacher['slug'])); ?>">
                            <span class="tile-code"><?php echo e($teacher['short']); ?></span>
                            <span class="tile-title"><?php echo e($teacher['name']); ?></span>
                            <span class="status-chip <?php echo e($teacher['free_slots'] > 0 ? 'is-free' : 'is-booked'); ?>">
                                <?php echo e($teacher['free_slots'] > 0 ? $teacher['free_slots'].' freie Termine' : 'Aktuell keine freien Termine'); ?>

                            </span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Schüler-Start',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/student/dashboard.blade.php ENDPATH**/ ?>