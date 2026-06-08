<?php
    $isEditing = (bool) $isEdit;
    $formAction = $isEditing
        ? route('admin.timeslots.update', $timeslot->id)
        : route('admin.timeslots.store');
    $teacherValue = $isEditing && isset($timeslot) ? $timeslot->teacher_id : ($preselectedTeacherId ?? null);
    $studentValue = $isEditing && isset($timeslot) ? $timeslot->student_id : null;
    $startValue = $isEditing && isset($timeslot) ? $timeslot->starts_at->format('H:i') : '17:00';
    $endValue = $isEditing && isset($timeslot) ? $timeslot->ends_at->format('H:i') : '19:00';
    $roomValue = $isEditing && isset($timeslot) ? $timeslot->room : 'B201';
    $isReserved = $isEditing && isset($timeslot) && $timeslot->is_reserved;
    $disabledAttr = ! $canEditParentDay ? 'disabled' : '';
    $slotLengthValue = old('slot_length_minutes', $suggestedTimeslotDuration ?? 10);
?>

<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow"><?php echo e($isEditing ? 'Bearbeiten' : 'Generierung'); ?></span>
                    <h2 class="panel-title">
                        <?php if($isEditing): ?>
                            Termin bearbeiten
                            <?php if(isset($timeslot)): ?>
                                <?php echo e($timeslot->is_reserved ? '(Gebucht)' : '(Frei)'); ?>

                            <?php endif; ?>
                        <?php else: ?>
                            Termine generieren
                        <?php endif; ?>
                    </h2>
                    <p class="panel-subtitle">Aktiver Elternsprechtag: <?php echo e($parentDayLabel ?? '--/--/----'); ?></p>
                </div>

                <div class="button-row">
                    <a href="<?php echo e(route('admin.teachers.appointments', $teacherSlug)); ?>" class="ghost-button">Terminuebersicht</a>
                    <a href="<?php echo e(route('admin.teachers.show', $teacherSlug)); ?>" class="ghost-button">Lehrer bearbeiten</a>
                </div>
            </div>

            <form method="POST" action="<?php echo e($formAction); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <?php if($isEditing): ?>
                    <?php echo method_field('PUT'); ?>
                <?php endif; ?>

                <?php if(! $canEditParentDay): ?>
                    <p class="hint">Dieser Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr moeglich.</p>
                <?php endif; ?>

                <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                    <div class="field">
                        <label for="teacher_id">Lehrer *</label>
                        <select id="teacher_id" name="teacher_id" required <?php echo e($disabledAttr); ?>>
                            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($teacherOption->teacher_id); ?>" <?php echo e((string) $teacherValue === (string) $teacherOption->teacher_id ? 'selected' : ''); ?>>
                                    <?php echo e($teacherOption->full_name); ?> (<?php echo e($teacherOption->kuerzel ?: $teacherOption->first_name); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <?php if($isEditing): ?>
                        <div class="field">
                            <label for="student_id">Schueler (optional)</label>
                            <select id="student_id" name="student_id" <?php echo e($disabledAttr); ?>>
                                <option value="">Keiner</option>
                                <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($student->student_id); ?>" <?php echo e((string) $studentValue === (string) $student->student_id ? 'selected' : ''); ?>>
                                        <?php echo e($student->full_name); ?> (<?php echo e($student->class_name); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="field">
                        <label for="parent_day">Datum</label>
                        <input type="text" id="parent_day" value="<?php echo e($parentDayLabel ?? '--/--/----'); ?>" readonly />
                    </div>

                    <div class="field">
                        <label for="starts_at">Beginn *</label>
                        <input
                            type="text"
                            id="starts_at"
                            name="starts_at"
                            value="<?php echo e(old('starts_at', $startValue)); ?>"
                            inputmode="numeric"
                            pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
                            placeholder="17:00"
                            title="24h-Format, z.B. 17:00"
                            required
                            <?php echo e($disabledAttr); ?>

                        />
                    </div>

                    <div class="field">
                        <label for="ends_at">Ende *</label>
                        <input
                            type="text"
                            id="ends_at"
                            name="ends_at"
                            value="<?php echo e(old('ends_at', $endValue)); ?>"
                            inputmode="numeric"
                            pattern="([01][0-9]|2[0-3]):[0-5][0-9]"
                            placeholder="19:00"
                            title="24h-Format, z.B. 19:00"
                            required
                            <?php echo e($disabledAttr); ?>

                        />
                    </div>

                    <?php if(! $isEditing): ?>
                        <div class="field">
                            <label for="slot_length_minutes">Terminlänge *</label>
                            <input
                                type="number"
                                id="slot_length_minutes"
                                name="slot_length_minutes"
                                min="5"
                                step="5"
                                value="<?php echo e($slotLengthValue); ?>"
                                required
                                <?php echo e($disabledAttr); ?>

                            />
                        </div>
                    <?php endif; ?>

                    <div class="field">
                        <label for="room">Raum *</label>
                        <input
                            type="text"
                            id="room"
                            name="room"
                            list="room-options"
                            value="<?php echo e(old('room', $roomValue)); ?>"
                            required
                            placeholder="z.B. B201, A104"
                            <?php echo e($disabledAttr); ?>

                        />
                        <?php if(isset($rooms) && $rooms->isNotEmpty()): ?>
                            <datalist id="room-options">
                                <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($room->name); ?>"></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </datalist>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($isEditing): ?>
                    <div class="field">
                        <label class="class-chip" style="width: max-content;">
                            <input type="checkbox" name="is_reserved" <?php echo e($isReserved ? 'checked' : ''); ?> <?php echo e($disabledAttr); ?> />
                            <span>Termin als gebucht markieren</span>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="button-row">
                    <?php if($canEditParentDay): ?>
                        <button type="submit" class="button"><?php echo e($isEditing ? 'Speichern' : 'Termine generieren'); ?></button>
                    <?php else: ?>
                        <span class="badge">Nur Ansicht</span>
                    <?php endif; ?>

                    <a href="<?php echo e(route('admin.teachers.appointments', $teacherSlug)); ?>" class="ghost-button">Zurueck</a>
                </div>
            </form>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Termine',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Bearbeiten', 'route' => 'admin.teachers.show', 'params' => [$teacherSlug], 'active_exact' => 'admin.teachers.show'],
        ['label' => 'Terminuebersicht', 'route' => 'admin.teachers.appointments', 'params' => [$teacherSlug], 'active_exact' => 'admin.teachers.appointments'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/timeslot-form.blade.php ENDPATH**/ ?>