<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <h2 class="eyebrow eyebrow-heading">Lehrertermine</h2>
                    <div class="button-row" style="margin-top: 12px;">
                        <span class="badge"><?php echo e($teacher['display_classes']); ?></span>
                        <span class="badge"><?php echo e($teacher['timeslot_duration_label']); ?></span>
                        <?php if($teacher['duration_changed']): ?>
                            <span class="status-chip">Termindauer geändert</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="button-row">
                    <a class="ghost-button" href="<?php echo e(route('admin.teachers.show', $teacher['slug'])); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L15 11"></path>
                        </svg>
                        Lehrer bearbeiten
                    </a>
                    <?php if($canEditParentDay): ?>
                        <a class="button" href="<?php echo e(route('admin.timeslots.create', ['teacher' => $teacher['reference_id']])); ?>">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                <path d="M16 2v4"></path>
                                <path d="M8 2v4"></path>
                                <path d="M3 10h18"></path>
                                <path d="M12 13v6"></path>
                                <path d="M9 16h6"></path>
                            </svg>
                            Termine generieren
                        </a>
                    <?php else: ?>
                        <span class="badge">Nur Ansicht</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if($appointments->isEmpty()): ?>
                <p class="empty-copy">Für diesen Lehrer wurden noch keine Termine angelegt.</p>
            <?php else: ?>
                <div class="appointment-filter" data-appointment-filters>
                    <button type="button" class="filter-button is-active" data-appointment-filter="all">Alle</button>
                    <button type="button" class="filter-button" data-appointment-filter="free">Frei</button>
                    <button type="button" class="filter-button" data-appointment-filter="booked">Gebucht</button>
                </div>

                <div class="appointments-list">
                    <?php $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="appointment-row" data-appointment-status="<?php echo e($appointment['is_reserved'] ? 'booked' : 'free'); ?>">
                            <div class="appointment-time"><?php echo e($appointment['time_label']); ?></div>

                            <div class="appointment-main">
                                <?php if($appointment['is_reserved']): ?>
                                    <p class="appointment-title"><?php echo e($appointment['student_name']); ?></p>
                                    <p class="appointment-meta"><?php echo e($appointment['class_name']); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="appointment-actions">
                                <span class="status-chip <?php echo e($appointment['is_reserved'] ? 'is-booked' : 'is-free'); ?>">
                                    <?php echo e($appointment['is_reserved'] ? 'Gebucht' : 'Frei'); ?>

                                </span>
                                <?php if($canEditParentDay): ?>
                                    <a class="ghost-button" href="<?php echo e(route('admin.timeslots.edit', $appointment['id'])); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                        </svg>
                                        Bearbeiten
                                    </a>
                                    <?php if($appointment['is_reserved']): ?>
                                        <form method="POST" action="<?php echo e(route('admin.timeslots.release', $appointment['id'])); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="danger-button">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <path d="M15 9l-6 6"></path>
                                                    <path d="M9 9l6 6"></path>
                                                </svg>
                                                Stornieren
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="<?php echo e(route('admin.timeslots.destroy', $appointment['id'])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="danger-button">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                                <path d="M5 6l1 14h12l1-14"></path>
                                            </svg>
                                            Löschen
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Admin Terminübersicht Lehrer',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [
        ['label' => 'Übersicht', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
        ['label' => 'Lehrer', 'href' => route('admin.dashboard').'#teachers'],
        ['label' => 'Bearbeiten', 'route' => 'admin.teachers.show', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.show'],
        ['label' => 'Termine', 'route' => 'admin.teachers.appointments', 'params' => [$teacher['slug']], 'active_exact' => 'admin.teachers.appointments'],
    ],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/teacher-appointments.blade.php ENDPATH**/ ?>