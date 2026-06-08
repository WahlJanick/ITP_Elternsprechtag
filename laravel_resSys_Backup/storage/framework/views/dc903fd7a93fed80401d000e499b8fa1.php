<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title"><?php echo e($teacher['name']); ?> (<?php echo e($teacher['short']); ?>)</h2>
                    <div class="button-row" style="margin-top: 12px;">
                        <span class="badge"><?php echo e($teacher['display_classes']); ?></span>
                        <span class="badge"><?php echo e($teacher['timeslot_duration_label']); ?></span>
                        <?php if($teacher['duration_changed']): ?>
                            <span class="status-chip">Termindauer geändert</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.teachers.update', $teacher['slug'])); ?>" class="stack">
                <?php echo csrf_field(); ?>

                <div class="teacher-grid admin-teacher-form-grid">
                    <div class="field compact-field">
                        <label for="first_name">Vorname</label>
                        <input
                            id="first_name"
                            type="text"
                            name="first_name"
                            value="<?php echo e(old('first_name', $teacher['first_name'])); ?>"
                            required
                        />
                    </div>

                    <div class="field compact-field">
                        <label for="last_name">Nachname</label>
                        <input
                            id="last_name"
                            type="text"
                            name="last_name"
                            value="<?php echo e(old('last_name', $teacher['last_name'])); ?>"
                            required
                        />
                    </div>

                    <div class="field compact-field compact-field-short">
                        <label for="kuerzel">Kürzel</label>
                        <input
                            id="kuerzel"
                            type="text"
                            name="kuerzel"
                            value="<?php echo e(old('kuerzel', $teacher['kuerzel'])); ?>"
                            placeholder="z.B. MM"
                        />
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Lehrer speichern</button>
                    <a href="<?php echo e(route('admin.teachers.appointments', $teacher['slug'])); ?>" class="ghost-button">Terminübersicht</a>
                    <a href="<?php echo e(route('admin.dashboard')); ?>#teachers" class="ghost-button">Zur Lehrerübersicht</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Elternsprechtag</span>
                    <h2 class="panel-title">Termindauer einstellen</h2>
                    <p class="panel-subtitle">Aktiver Elternsprechtag: <?php echo e($parentDayLabel); ?></p>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.teachers.duration.update', $teacher['slug'])); ?>" class="stack">
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
                            value="<?php echo e(old('timeslot_duration', $teacher['timeslot_duration'] ?? 10)); ?>"
                            required
                            <?php if(! $canEditParentDay): ?> disabled <?php endif; ?>
                        />
                        <?php if($canEditParentDay): ?>
                            <button type="submit" class="button">Speichern</button>
                        <?php else: ?>
                            <span class="badge">Nur Ansicht</span>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="hint">Diese Termindauer gilt nur fuer den aktuell gewaehlten Elternsprechtag.</p>
            </form>
        </section>

        <section class="panel" id="classes">
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Schulklassen</h2>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.teachers.classes.update', $teacher['slug'])); ?>" class="stack">
                <?php echo csrf_field(); ?>

                <?php if($classOptions->isEmpty()): ?>
                    <p class="empty-copy">Es sind noch keine Klassen verfügbar.</p>
                <?php else: ?>
                    <div class="class-subject-grid">
                        <?php $__currentLoopData = $classOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($isChecked = in_array($className, old('classes', $teacher['classes']), true)); ?>
                            <div class="class-subject-row">
                                <label class="class-chip">
                                    <input
                                        type="checkbox"
                                        name="classes[]"
                                        value="<?php echo e($className); ?>"
                                        <?php echo e($isChecked ? 'checked' : ''); ?>

                                    />
                                    <span><?php echo e($className); ?></span>
                                </label>
                                <input
                                    type="text"
                                    name="class_subjects[<?php echo e($className); ?>]"
                                    value="<?php echo e(old('class_subjects.'.$className, $classSubjectMap[$className] ?? '')); ?>"
                                    placeholder="Faecher, z.B. Mathe, Physik"
                                    class="class-subject-input"
                                />
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

                <div class="field">
                    <label for="additional_classes">Weitere Klassen</label>
                    <input
                        id="additional_classes"
                        type="text"
                        name="additional_classes"
                        value="<?php echo e(old('additional_classes')); ?>"
                        placeholder="z.B. 3AHIT, 4AHIT"
                    />
                </div>

                <div class="button-row">
                    <button type="submit" class="button">Klassen speichern</button>
                </div>
            </form>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Admin Lehrer-Detail',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehreraktivitäten', 'href' => route('admin.dashboard').'#teacher-activity'],
        ['label' => 'Datumfestlegung', 'href' => route('admin.dashboard').'#parent-day'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Terminübersicht', 'route' => 'admin.teachers.appointments', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.appointments'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/teacher-show.blade.php ENDPATH**/ ?>