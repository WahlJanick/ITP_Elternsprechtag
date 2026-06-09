<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title"><?php echo e($teacher['name']); ?></h2>
                </div>

                <a class="close-button" href="<?php echo e(route('student.teachers.index')); ?>" aria-label="Zurück">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5"></path>
                        <path d="M12 19l-7-7 7-7"></path>
                    </svg>
                </a>
            </div>

            <div class="button-row" style="margin-bottom: 16px;">
                <span class="badge">Raum: <?php echo e($teacherRoom); ?></span>
                <span class="status-chip is-free"><?php echo e($teacher['free_slots']); ?> freie Termine</span>
            </div>

            <?php if($freeSlots->isEmpty()): ?>
                <p class="empty-copy">Dieser Lehrer hat derzeit keine freien Termine.</p>
            <?php else: ?>
                <form method="POST" action="<?php echo e(route('student.timeslots.book')); ?>" id="booking-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="teacher_id" value="<?php echo e($teacher['reference_id']); ?>">

                    <div class="slot-grid">
                        <?php $__currentLoopData = $freeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="slot-button" style="cursor: <?php echo e($alreadyBooked ? 'not-allowed' : 'pointer'); ?>; opacity: <?php echo e($alreadyBooked ? '0.6' : '1'); ?>;">
                                <input type="radio" name="timeslot_id" value="<?php echo e($slot['id']); ?>" <?php echo e($alreadyBooked ? 'disabled' : ''); ?> style="position: absolute; opacity: 0;">
                                <?php echo e($slot['label']); ?>

                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="inline-actions" style="margin-top: 24px; justify-content: space-between;">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <?php if(!$alreadyBooked): ?>
                                <button type="submit" class="button" id="book-button" disabled>Buchen</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <?php if($teachers->isNotEmpty()): ?>
            <section class="panel teacher-navigation">
                <div class="teacher-grid">
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listTeacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="tile" href="<?php echo e(route('student.teachers.show', $listTeacher['slug'])); ?>">
                            <span class="tile-code"><?php echo e($listTeacher['short']); ?></span>
                            <span class="tile-title"><?php echo e($listTeacher['name']); ?></span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php if(!$alreadyBooked): ?>
        <script>
            document.querySelectorAll('input[name="timeslot_id"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('book-button').disabled = false;
                    document.querySelectorAll('.slot-button').forEach(btn => {
                        btn.classList.remove('is-selected');
                    });
                    this.closest('.slot-button').classList.add('is-selected');
                });
            });
        </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Schüler-Lehrer-Detail',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Start', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Gebucht', 'route' => 'student.bookings', 'active' => 'student.bookings'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/student/teacher-show.blade.php ENDPATH**/ ?>