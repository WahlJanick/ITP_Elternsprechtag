<?php $__env->startSection('content'); ?>
    <div class="stack">
        <?php if($teacher): ?>
            <section class="panel">
                <div class="panel-header">
                    <div>
                        <span class="eyebrow">Einstellungen</span>
                        <h2 class="panel-title">Termindauer</h2>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('teacher.timeslot-duration.update')); ?>" class="stack">
                    <?php echo csrf_field(); ?>
                    <div class="field field-inline">
                        <label for="timeslot_duration">Dauer in Minuten</label>
                        <div class="field-inline-row">
                            <input
                                id="timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                step="5"
                                value="<?php echo e(old('timeslot_duration', $currentDuration ?? $teacher->timeslot_duration ?? 10)); ?>"
                                required
                            />
                            <button type="submit" class="button">Speichern</button>
                        </div>
                    </div>
                    <p class="hint">Wenn du die Termindauer änderst, wird die Änderung für die Administration markiert.</p>
                </form>
            </section>
        <?php endif; ?>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Lehreransicht</span>
                    <h2 class="panel-title">Gebuchte Termine</h2>
                    <p class="panel-subtitle">
                        Lehrer sehen hier alle für sie angelegten Termine inklusive Schüler, Klasse, Raum und Zeit.
                    </p>
                    <?php if($teacher): ?>
                        <div class="button-row" style="margin-top: 12px;">
                            <span class="badge"><?php echo e($teacher->full_name); ?></span>
                            <?php if($teacher->kuerzel): ?>
                                <span class="badge"><?php echo e($teacher->kuerzel); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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
                <div class="list-stack">
                    <?php $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title"><?php echo e($appointment['student_name']); ?> / <?php echo e($appointment['class_name']); ?> / <?php echo e($appointment['room']); ?> / <?php echo e($appointment['time_label']); ?></p>
                                <p class="meta-copy"><?php echo e($appointment['date_label']); ?></p>
                            </div>

                            <span class="status-chip <?php echo e($appointment['is_reserved'] ? 'is-booked' : 'is-free'); ?>">
                                <?php echo e($appointment['is_reserved'] ? 'Gebucht' : 'Frei'); ?>

                            </span>
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
    'navLinks' => [
        ['label' => 'Termine', 'route' => 'teacher.dashboard', 'active' => 'teacher.dashboard'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/teacher/dashboard.blade.php ENDPATH**/ ?>