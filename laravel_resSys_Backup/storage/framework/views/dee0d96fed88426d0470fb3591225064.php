<?php $__env->startSection('content'); ?>
    <div class="stack">
        <?php if($teacher): ?>
            <section class="panel compact-settings-panel">
                <div class="teacher-settings-line">
                    <form method="POST" action="<?php echo e(route('teacher.timeslot-duration.update')); ?>" class="compact-settings-form">
                        <?php echo csrf_field(); ?>
                        <label for="timeslot_duration"><strong>Termindauer</strong></label>
                        <input
                            id="timeslot_duration"
                            type="number"
                            name="timeslot_duration"
                            min="5"
                            step="5"
                            value="<?php echo e(old('timeslot_duration', $currentDuration ?? $teacher->timeslot_duration ?? 10)); ?>"
                            aria-label="Termindauer in Minuten"
                            required
                        />
                        <span class="hint">Minuten</span>
                        <button type="submit" class="button button-compact">Speichern</button>
                    </form>

                    <span class="teacher-page-room <?php echo e($teacherRoom === '' ? 'is-unassigned' : ''); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                            <path d="M9 9h.01M15 9h.01M9 13h.01M15 13h.01"></path>
                        </svg>
                        <?php echo e($teacherRoom !== '' ? $teacherRoom : 'Kein Raum'); ?>

                    </span>
                </div>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Termine</h2>
                </div>

                <button type="button" class="print-button" onclick="window.print()">Termine drucken</button>
            </div>

            <?php if(! $hasTeacherMapping): ?>
                <p class="empty-copy">
                    Dein Azure-Name wurde noch keinem Lehrer in der Lehrerliste zugeordnet. Sobald der Name passt,
                    siehst du hier nur deine eigenen Timeslots.
                </p>
            <?php elseif($appointments->isEmpty()): ?>
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            <?php else: ?>
                <?php
                    $onlyFreeAppointments = $appointments->every(
                        fn (array $appointment) => ! $appointment['is_reserved']
                    );
                ?>
                <div class="appointment-filter" data-appointment-filters>
                    <button type="button" class="filter-button is-active" data-appointment-filter="all">Alle</button>
                    <button type="button" class="filter-button <?php echo e($onlyFreeAppointments ? 'is-muted' : ''); ?>" data-appointment-filter="free">Frei</button>
                    <button type="button" class="filter-button <?php echo e($onlyFreeAppointments ? 'is-muted' : ''); ?>" data-appointment-filter="booked">Gebucht</button>
                </div>

                <div class="appointments-list teacher-appointments-grid">
                    <?php $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="appointment-row teacher-appointment-row" data-appointment-status="<?php echo e($appointment['is_reserved'] ? 'booked' : 'free'); ?>">
                            <div class="appointment-time"><?php echo e($appointment['time_label']); ?></div>
                            <span class="status-chip <?php echo e($appointment['is_reserved'] ? 'is-booked' : 'is-free'); ?>">
                                <?php echo e($appointment['is_reserved'] ? 'Gebucht' : 'Frei'); ?>

                            </span>
                            <?php if($appointment['is_reserved']): ?>
                                <div class="appointment-main">
                                    <p class="appointment-title"><?php echo e($appointment['student_name']); ?></p>
                                    <p class="appointment-meta"><?php echo e($appointment['class_name']); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="appointment-main appointment-empty-copy" aria-hidden="true">
                                    <p class="appointment-title">&nbsp;</p>
                                    <p class="appointment-meta">&nbsp;</p>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Lehrer-Übersicht',
    'roleTitle' => 'Lehrer-Ansicht',
    'theme' => 'teacher',
    'homeRoute' => 'teacher.dashboard',
    'navLinks' => [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/teacher/dashboard.blade.php ENDPATH**/ ?>