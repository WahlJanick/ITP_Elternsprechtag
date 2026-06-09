<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Meine Termine</h2>
                </div>

                <button type="button" class="print-button" onclick="window.print()">Termine drucken</button>
            </div>

            <?php if($bookings->isEmpty()): ?>
                <p class="empty-copy">Du hast aktuell noch keine gebuchten Termine.</p>
            <?php else: ?>
                <div class="list-stack">
                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title"><?php echo e($booking['teacher_name']); ?> / <?php echo e($booking['room']); ?> / <?php echo e($booking['time_label']); ?></p>
                                <p class="meta-copy"><?php echo e($booking['date_label']); ?> / Klasse <?php echo e($booking['class_name']); ?></p>
                            </div>

                            <div class="list-row-actions">
                                <span class="status-chip is-booked">Gebucht</span>
                                <form method="POST" action="<?php echo e(route('student.bookings.cancel', $booking['id'])); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="danger-button">Stornieren</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Schüler Gebuchte Termine',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Start', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/student/bookings.blade.php ENDPATH**/ ?>