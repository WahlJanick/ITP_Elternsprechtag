<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Buchung</span>
                    <h2 class="panel-title">Lehrer, die dich unterrichten</h2>
                    <p class="panel-subtitle">
                        <?php if($currentClass): ?>
                            Klasse <?php echo e($currentClass); ?>. Lehrer ohne freie Slots bleiben sichtbar.
                        <?php else: ?>
                            Wähle einen Lehrer aus.
                        <?php endif; ?>
                    </p>
                </div>
                <a class="button" href="<?php echo e(route('student.bookings')); ?>">Meine Termine</a>
            </div>

            <?php if($teachers->isEmpty()): ?>
                <p class="empty-copy">Für deine aktuelle Ansicht sind keine Lehrer hinterlegt.</p>
            <?php else: ?>
                <div class="teacher-grid">
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
    'pageTitle' => 'Schüler-Lehrerübersicht',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Start', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/student/teachers.blade.php ENDPATH**/ ?>