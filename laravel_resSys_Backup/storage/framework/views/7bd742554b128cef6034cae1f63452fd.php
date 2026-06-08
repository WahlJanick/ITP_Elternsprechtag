<?php $__env->startSection('content'); ?>
    <div class="stack">
        <section class="panel">
            <div class="admin-menu-launcher" role="navigation" aria-label="Admin-Menüs">
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="overview">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    Übersicht
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="parent-day">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                        <path d="M16 3v4M8 3v4M3 10h18"></path>
                    </svg>
                    Sprechtage
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="teachers">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M2 21v-2a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v2M19 8v6M22 11h-6"></path>
                    </svg>
                    Lehrer
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="organization">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 10h.01M15 10h.01M9 14h.01M15 14h.01M10 21v-3h4v3"></path>
                    </svg>
                    Klassen & Räume
                </button>
                <button type="button" class="ghost-button admin-menu-button" data-admin-menu-button="import">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3v12M7 10l5 5 5-5M5 21h14"></path>
                    </svg>
                    Import
                </button>
            </div>
        </section>

        <?php if($teacherDurationChangeCount > 0): ?>
            <section class="notification-banner" data-admin-menu="overview" hidden>
                <div class="notification-banner-copy">
                    <strong>
                        <?php echo e($teacherDurationChangeCount); ?> Lehrer <?php echo e($teacherDurationChangeCount === 1 ? 'hat' : 'haben'); ?>

                        <?php echo e($teacherDurationChangeCount === 1 ? 'seine' : 'ihre'); ?> Termindauer angepasst.
                    </strong>
                </div>

                <a class="button" href="#teacher-activity">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M22 12H2"></path>
                        <path d="M16 6l6 6-6 6"></path>
                    </svg>
                    Zu Lehreraktivitäten
                </a>
            </section>
        <?php endif; ?>

        <section class="panel section-anchor" id="dashboard" data-admin-menu="overview" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Dashboard <span class="badge"><?php echo e($parentDayLabel); ?></span></h2>
                </div>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span class="number"><?php echo e($stats['students']); ?></span>
                    <span class="mini-label">Schüler gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number"><?php echo e($stats['teachers']); ?></span>
                    <span class="mini-label">Lehrer gesamt</span>
                </article>
                <article class="stat-card">
                    <span class="number"><?php echo e($stats['free_slots']); ?></span>
                    <span class="mini-label">Freie Termine</span>
                </article>
                <article class="stat-card">
                    <span class="number"><?php echo e($stats['booked_slots']); ?></span>
                    <span class="mini-label">Gebuchte Termine</span>
                </article>
            </div>
        </section>

        <section class="panel section-anchor teacher-activity-panel" id="teacher-activity" data-admin-menu="overview" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehreraktivitäten</h2>
                </div>

                <?php if($teacherDurationChanges->isNotEmpty()): ?>
                    <div class="teacher-mini-actions teacher-activity-toolbar">
                        <form method="POST" action="<?php echo e(route('admin.teachers.activities.delete-all')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="danger-button button-compact">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 6h18"></path>
                                    <path d="M8 6V4h8v2"></path>
                                    <path d="M10 11v6"></path>
                                    <path d="M14 11v6"></path>
                                    <path d="M5 6l1 14h12l1-14"></path>
                                </svg>
                                Alle löschen
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($teacherDurationChanges->isNotEmpty()): ?>
                <div class="teacher-summary-grid">
                    <?php $__currentLoopData = $teacherDurationChanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="teacher-mini-card is-highlighted" data-activity-card>
                            <div class="teacher-mini-top">
                                <div class="list-row-copy">
                                    <p class="list-row-title"><?php echo e($teacher['name']); ?></p>
                                    <p class="meta-copy"><?php echo e($teacher['timeslot_duration_label']); ?></p>
                                </div>
                                <span class="status-chip">Geändert</span>
                            </div>
                            <p class="meta-copy">
                                <?php if($teacher['duration_changed_label']): ?>
                                    <?php echo e($teacher['duration_changed_label']); ?>

                                <?php endif; ?>
                            </p>
                            <div class="teacher-mini-actions actions-on-hover">
                                <a class="button button-compact" href="<?php echo e(route('admin.teachers.show', $teacher['slug'])); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M19 8v6"></path>
                                        <path d="M22 11h-6"></path>
                                    </svg>
                                    Lehrer öffnen
                                </a>
                                <form method="POST" action="<?php echo e(route('admin.teachers.activities.delete', $teacher['slug'])); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="danger-button button-compact">
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
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="empty-copy">Noch keine Änderungen an Termindauern gemeldet.</p>
            <?php endif; ?>
        </section>

        <section class="panel section-anchor" id="parent-day" data-admin-menu="parent-day" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Datumfestlegung</h2>
                    <p class="panel-subtitle">Lege beliebig viele Elternsprechtage an und wechsle oben in der Leiste.</p>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.parent-day.update')); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <div class="field field-inline">
                    <label for="parent_day">Datum des Elternsprechtags</label>
                    <div class="field-inline-row">
                        <input
                            id="parent_day"
                            type="date"
                            name="parent_day"
                            value="<?php echo e(old('parent_day', $parentDayValue)); ?>"
                            required
                        />
                        <button type="submit" class="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14"></path>
                                <path d="M5 12h14"></path>
                            </svg>
                            Anlegen
                        </button>
                    </div>
                </div>
            </form>

            <?php if($parentDays->isEmpty()): ?>
                <p class="empty-copy">Noch keine Elternsprechtage angelegt.</p>
            <?php else: ?>
                <div class="list-stack">
                    <?php $__currentLoopData = $parentDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="list-row is-interactive <?php echo e($activeParentDay && $activeParentDay->id === $day->id ? 'is-highlighted' : ''); ?>">
                            <div class="list-row-copy">
                                <p class="list-row-title"><?php echo e($day->date->format('d/m/Y')); ?></p>
                                <p class="meta-copy">
                                    <?php echo e($activeParentDay && $activeParentDay->id === $day->id ? 'Aktiver Elternsprechtag' : 'Nicht aktiv'); ?>

                                </p>
                            </div>
                            <div class="list-row-actions actions-on-hover">
                                <form method="POST" action="<?php echo e(route('admin.parent-days.delete', $day->id)); ?>">
                                    <?php echo csrf_field(); ?>
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
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="panel section-anchor" id="teachers" data-admin-menu="teachers" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehrer</h2>
                </div>
            </div>

            <div class="field" style="margin-bottom: 12px;">
                <label for="teacher_overview_search">Suche</label>
                <input
                    id="teacher_overview_search"
                    type="search"
                    placeholder="Name, Kürzel oder Klasse"
                    data-teacher-overview-search
                />
            </div>

            <?php if($teachers->isNotEmpty()): ?>
                <div class="list-stack">
                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article
                            class="list-row is-interactive <?php echo e($teacher['duration_changed'] ? 'is-highlighted' : ''); ?>"
                            tabindex="0"
                            data-selectable-row
                            data-search-item
                            data-search-text="<?php echo e(\Illuminate\Support\Str::lower($teacher['name'].' '.$teacher['short'].' '.$teacher['display_classes'])); ?>"
                        >
                            <div class="list-row-copy">
                                <p class="list-row-title"><?php echo e($teacher['name']); ?></p>
                                <p class="meta-copy"><?php echo e($teacher['short']); ?> · <?php echo e($teacher['display_classes']); ?></p>
                                <div class="button-row">
                                    <span class="badge"><?php echo e($teacher['timeslot_duration_label']); ?></span>
                                    <span class="badge"><?php echo e($teacher['free_slots']); ?> frei</span>
                                    <span class="badge"><?php echo e($teacher['booked_slots']); ?> gebucht</span>
                                    <?php if($teacher['duration_changed']): ?>
                                        <span class="status-chip">Termindauer geändert</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="list-row-actions actions-on-hover">
                                <a class="ghost-button" href="<?php echo e(route('admin.teachers.appointments', $teacher['slug'])); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                        <path d="M16 2v4"></path>
                                        <path d="M8 2v4"></path>
                                        <path d="M3 10h18"></path>
                                    </svg>
                                    Termine
                                </a>
                                <a class="button" href="<?php echo e(route('admin.teachers.show', $teacher['slug'])); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                                    </svg>
                                    Bearbeiten
                                </a>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="empty-copy">Noch keine Lehrer angelegt.</p>
            <?php endif; ?>
        </section>

        <section class="panel" id="teacher-create" data-admin-menu="teachers" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Lehrer anlegen</h2>
                </div>
            </div>

            <form method="POST" action="<?php echo e(route('admin.teachers.accounts.store')); ?>" class="stack wizard" data-wizard="teacher">
                <?php echo csrf_field(); ?>
                <div class="wizard-steps">
                    <div class="wizard-step-indicator is-active" data-step-indicator>1. Zugang</div>
                    <div class="wizard-step-indicator" data-step-indicator>2. Klassen</div>
                    <div class="wizard-step-indicator" data-step-indicator>3. Termine</div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="field">
                        <label for="teacher_emails">Lehrer (Vorname Nachname &lt;E-Mail&gt;)</label>
                        <input
                            id="teacher_emails"
                            type="text"
                            name="teacher_emails"
                            value="<?php echo e(old('teacher_emails', '')); ?>"
                            placeholder="Max Mustermann <max.mustermann@schule.at>, Erika Muster <erika.muster@schule.at>"
                        />
                    </div>

                    <div class="button-row">
                        <button type="button" class="button" data-step-next>Weiter</button>
                    </div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="class-grid-compact">
                        <?php $__currentLoopData = $classOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $className): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="class-chip">
                                <input
                                    type="checkbox"
                                    name="classes[]"
                                    value="<?php echo e($className); ?>"
                                    <?php echo e(in_array($className, old('classes', []), true) ? 'checked' : ''); ?>

                                />
                                <span><?php echo e($className); ?></span>
                            </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="field">
                        <label for="additional_classes">Weitere Klassen</label>
                        <input
                            id="additional_classes"
                            type="text"
                            name="additional_classes"
                            value="<?php echo e(old('additional_classes', '')); ?>"
                            placeholder="z.B. 3AHIT, 4AHIT"
                        />
                    </div>

                    <div class="button-row">
                        <button type="button" class="ghost-button" data-step-prev>Zurück</button>
                        <button type="button" class="button" data-step-next>Weiter</button>
                    </div>
                </div>

                <div class="stack wizard-step" data-step>
                    <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                        <div class="field">
                            <label for="timeslot_duration">Termin-Dauer in Minuten</label>
                            <input
                                id="timeslot_duration"
                                type="number"
                                name="timeslot_duration"
                                min="5"
                                step="5"
                                value="<?php echo e(old('timeslot_duration', '')); ?>"
                                placeholder="z.B. 10"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_parent_day">Datum des Elternsprechtags</label>
                            <input
                                id="timeslot_parent_day"
                                type="text"
                                value="<?php echo e($parentDayLabel); ?>"
                                readonly
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_start">Beginn</label>
                            <input
                                id="timeslot_start"
                                type="time"
                                name="timeslot_start"
                                value="<?php echo e(old('timeslot_start', '')); ?>"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_end">Ende</label>
                            <input
                                id="timeslot_end"
                                type="time"
                                name="timeslot_end"
                                value="<?php echo e(old('timeslot_end', '')); ?>"
                            />
                        </div>

                        <div class="field">
                            <label for="timeslot_room">Raum</label>
                            <input
                                id="timeslot_room"
                                type="text"
                                name="timeslot_room"
                                value="<?php echo e(old('timeslot_room', '')); ?>"
                                placeholder="z.B. B201"
                            />
                        </div>
                    </div>

                    <div class="button-row">
                        <button type="button" class="ghost-button" data-step-prev>Zurück</button>
                        <button type="submit" class="button">Lehrer anlegen</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="panel section-anchor" id="classes" data-admin-menu="organization" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Schulklassen</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form method="POST" action="<?php echo e(route('admin.classes.store')); ?>" class="stack">
                    <?php echo csrf_field(); ?>
                    <div class="field field-inline">
                        <label for="new_class">Neue Klasse</label>
                        <div class="field-inline-row class-create-row">
                            <input
                                id="new_class"
                                type="text"
                                name="name"
                                placeholder="z.B. 3AHIT"
                                required
                            />
                            <button type="submit" class="button">Anlegen</button>
                            <button type="button" class="ghost-button button-compact" data-open-modal="class-bulk">Mehrere hinzufügen</button>
                        </div>
                    </div>
                </form>

                <?php if($schoolClasses->isNotEmpty()): ?>
                    <div class="class-list compact-grid">
                        <?php $__currentLoopData = $schoolClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schoolClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="class-row compact-grid">
                                <form
                                    method="POST"
                                    action="<?php echo e(route('admin.classes.update', $schoolClass)); ?>"
                                    class="class-row-form"
                                    data-class-form
                                >
                                    <?php echo csrf_field(); ?>
                                    <input
                                        type="text"
                                        name="name"
                                        value="<?php echo e($schoolClass->name); ?>"
                                        class="class-input compact"
                                        data-class-input
                                    />
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="empty-copy">Noch keine Klassen angelegt.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel section-anchor" id="rooms" data-admin-menu="organization" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Räume</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form method="POST" action="<?php echo e(route('admin.rooms.store')); ?>" class="stack">
                    <?php echo csrf_field(); ?>
                    <div class="field field-inline">
                        <label for="new_room">Neuer Raum</label>
                        <div class="field-inline-row class-create-row">
                            <input
                                id="new_room"
                                type="text"
                                name="name"
                                placeholder="z.B. Raum 101"
                                required
                            />
                            <button type="submit" class="button">Anlegen</button>
                            <button type="button" class="ghost-button button-compact" data-open-modal="room-bulk">Mehrere hinzufügen</button>
                        </div>
                    </div>
                </form>

                <?php if($rooms->isNotEmpty()): ?>
                    <div class="class-list compact-grid">
                        <?php $__currentLoopData = $rooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="class-row compact-grid">
                                <form
                                    method="POST"
                                    action="<?php echo e(route('admin.rooms.update', $room)); ?>"
                                    class="class-row-form"
                                    data-room-form
                                >
                                    <?php echo csrf_field(); ?>
                                    <input
                                        type="text"
                                        name="name"
                                        value="<?php echo e($room->name); ?>"
                                        class="class-input compact"
                                        data-room-input
                                    />
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="empty-copy">Noch keine Räume angelegt.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel section-anchor" id="excel-import" data-admin-menu="import" data-collapsible data-collapsed="false" hidden>
            <div class="panel-header">
                <div>
                    <h2 class="panel-title">Excel-Import</h2>
                </div>
                <button type="button" class="ghost-button button-compact" data-collapsible-toggle>
                    <svg data-collapsible-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                    <svg data-collapsible-icon-close viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                        <path d="m18 15-6-6-6 6"></path>
                    </svg>
                    <span data-collapsible-label>Aufklappen</span>
                </button>
            </div>

            <div class="panel-body" data-collapsible-content>
                <form method="POST" action="<?php echo e(route('admin.teachers.import')); ?>" enctype="multipart/form-data" class="stack" data-import>
                    <?php echo csrf_field(); ?>

                    <div class="field">
                        <label for="teacher_file">Excel-Datei (xlsx)</label>
                        <input
                            id="teacher_file"
                            type="file"
                            name="teacher_file"
                            accept=".xlsx,.xls"
                            required
                        />
                        <p class="hint">
                            Unterstützt benannte Spalten sowie das Standardformat:
                            A Kürzel, B Nachname, C Vorname, D Klassen.
                        </p>
                    </div>

                    <?php if($canGenerateTimeslots): ?>
                        <div class="field field-inline">
                            <label for="create_timeslots">Termine direkt anlegen</label>
                            <div class="field-inline-row">
                                <label class="class-chip" style="padding: 6px 10px;">
                                    <input id="create_timeslots" type="checkbox" name="create_timeslots" />
                                    <span>Standard-Termine erzeugen</span>
                                </label>
                            </div>
                        </div>

                        <div class="teacher-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));" data-import-timeslots>
                            <div class="field">
                                <label for="import_timeslot_duration">Termin-Dauer in Minuten</label>
                                <input
                                    id="import_timeslot_duration"
                                    type="number"
                                    name="timeslot_duration"
                                    min="5"
                                    step="5"
                                    value="<?php echo e(old('timeslot_duration', '')); ?>"
                                    placeholder="z.B. 10"
                                />
                            </div>

                            <div class="field">
                                <label for="import_timeslot_parent_day">Datum des Elternsprechtags</label>
                                <input
                                    id="import_timeslot_parent_day"
                                    type="text"
                                    value="<?php echo e($parentDayLabel); ?>"
                                    readonly
                                />
                            </div>

                            <div class="field">
                                <label for="import_timeslot_start">Beginn</label>
                                <input
                                    id="import_timeslot_start"
                                    type="time"
                                    name="timeslot_start"
                                    value="<?php echo e(old('timeslot_start', '')); ?>"
                                    min="00:00"
                                    max="23:59"
                                    step="60"
                                    lang="de-AT"
                                />
                            </div>

                            <div class="field">
                                <label for="import_timeslot_end">Ende</label>
                                <input
                                    id="import_timeslot_end"
                                    type="time"
                                    name="timeslot_end"
                                    value="<?php echo e(old('timeslot_end', '')); ?>"
                                    min="00:00"
                                    max="23:59"
                                    step="60"
                                    lang="de-AT"
                                />
                            </div>

                            <div class="field">
                                <label for="import_timeslot_room">Raum</label>
                                <input
                                    id="import_timeslot_room"
                                    type="text"
                                    name="timeslot_room"
                                    value="<?php echo e(old('timeslot_room', '')); ?>"
                                    placeholder="z.B. B201"
                                />
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="button-row">
                        <button type="submit" class="button">Import starten</button>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="modal" data-modal="class-bulk" aria-hidden="true">
        <div class="modal-overlay" data-close-modal></div>
        <div class="modal-content">
            <h3 class="panel-title" style="font-size: 1.3rem;">Mehrere Klassen hinzufügen</h3>
            <form method="POST" action="<?php echo e(route('admin.classes.store')); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="bulk_classes">Klassen (kommagetrennt)</label>
                    <textarea id="bulk_classes" name="name" rows="3" placeholder="Klasse 1A, Klasse 1B, Klasse 2A"></textarea>
                </div>
                <div class="button-row">
                    <button type="submit" class="button">Hinzufügen</button>
                    <button type="button" class="ghost-button" data-close-modal>Schließen</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" data-modal="room-bulk" aria-hidden="true">
        <div class="modal-overlay" data-close-modal></div>
        <div class="modal-content">
            <h3 class="panel-title" style="font-size: 1.3rem;">Mehrere Räume hinzufügen</h3>
            <form method="POST" action="<?php echo e(route('admin.rooms.store')); ?>" class="stack">
                <?php echo csrf_field(); ?>
                <div class="field">
                    <label for="bulk_rooms">Räume (kommagetrennt)</label>
                    <textarea id="bulk_rooms" name="name" rows="3" placeholder="Raum 101, Raum 102, Raum 103"></textarea>
                </div>
                <div class="button-row">
                    <button type="submit" class="button">Hinzufügen</button>
                    <button type="button" class="ghost-button" data-close-modal>Schließen</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const adminMenuButtons = Array.from(document.querySelectorAll('[data-admin-menu-button]'));
            const adminMenus = Array.from(document.querySelectorAll('[data-admin-menu]'));
            const hashMenus = {
                dashboard: 'overview',
                'teacher-activity': 'overview',
                'parent-day': 'parent-day',
                teachers: 'teachers',
                'teacher-create': 'teachers',
                classes: 'organization',
                rooms: 'organization',
                'excel-import': 'import',
            };

            const openAdminMenu = (menuName, updateHash = false) => {
                adminMenus.forEach((menu) => {
                    menu.hidden = menu.dataset.adminMenu !== menuName;
                });

                adminMenuButtons.forEach((button) => {
                    const active = button.dataset.adminMenuButton === menuName;
                    button.classList.toggle('is-active', active);
                    button.setAttribute('aria-pressed', String(active));
                });

                if (updateHash) {
                    const target = adminMenus.find((menu) => menu.dataset.adminMenu === menuName && menu.id);
                    if (target) {
                        history.replaceState(null, '', `#${target.id}`);
                    }
                }
            };

            adminMenuButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    openAdminMenu(button.dataset.adminMenuButton, true);
                });
            });

            const initialHash = window.location.hash.slice(1);
            if (hashMenus[initialHash]) {
                openAdminMenu(hashMenus[initialHash]);
            }

            const wizard = document.querySelector('[data-wizard="teacher"]');
            if (wizard) {
                const steps = Array.from(wizard.querySelectorAll('[data-step]'));
                const indicators = Array.from(wizard.querySelectorAll('[data-step-indicator]'));
                let current = 0;

                const render = () => {
                    steps.forEach((step, index) => {
                        step.style.display = index === current ? 'grid' : 'none';
                    });
                    indicators.forEach((indicator, index) => {
                        indicator.classList.toggle('is-active', index === current);
                    });
                };

                wizard.addEventListener('click', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    if (target.closest('[data-step-next]')) {
                        current = Math.min(steps.length - 1, current + 1);
                        render();
                    }

                    if (target.closest('[data-step-prev]')) {
                        current = Math.max(0, current - 1);
                        render();
                    }
                });

                render();
            }

            const importForm = document.querySelector('[data-import]');
            if (importForm) {
                const toggle = importForm.querySelector('#create_timeslots');
                const timeslotFields = importForm.querySelectorAll('[data-import-timeslots] input');

                const sync = () => {
                    const enabled = toggle && toggle.checked;
                    timeslotFields.forEach((field) => {
                        field.disabled = !enabled;
                    });
                };

                if (toggle) {
                    toggle.addEventListener('change', sync);
                    sync();
                }
            }

            const collapsibles = document.querySelectorAll('[data-collapsible]');
            collapsibles.forEach((panel) => {
                const content = panel.querySelector('[data-collapsible-content]');
                const toggle = panel.querySelector('[data-collapsible-toggle]');
                const label = toggle?.querySelector('[data-collapsible-label]');
                const openIcon = toggle?.querySelector('[data-collapsible-icon-open]');
                const closeIcon = toggle?.querySelector('[data-collapsible-icon-close]');

                if (!content || !toggle) {
                    return;
                }

                let collapsed = panel.dataset.collapsed === 'true';

                const renderCollapse = () => {
                    content.classList.toggle('is-collapsed', collapsed);
                    if (label) {
                        label.textContent = collapsed ? 'Aufklappen' : 'Zuklappen';
                    }
                    if (openIcon) {
                        openIcon.hidden = !collapsed;
                    }
                    if (closeIcon) {
                        closeIcon.hidden = collapsed;
                    }
                    toggle.setAttribute('aria-expanded', String(!collapsed));
                };

                toggle.addEventListener('click', () => {
                    collapsed = !collapsed;
                    renderCollapse();
                });

                renderCollapse();
            });

            const openModalButtons = document.querySelectorAll('[data-open-modal]');
            const closeModalButtons = document.querySelectorAll('[data-close-modal]');

            const openModal = (name) => {
                const modal = document.querySelector(`[data-modal="${name}"]`);
                if (modal) {
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                }
            };

            const closeModal = (modal) => {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            };

            openModalButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const name = button.getAttribute('data-open-modal');
                    if (name) {
                        openModal(name);
                    }
                });
            });

            closeModalButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('[data-modal]');
                    if (modal) {
                        closeModal(modal);
                    }
                });
            });

            const classInputs = document.querySelectorAll('[data-class-input]');
            classInputs.forEach((input) => {
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        const form = input.closest('[data-class-form]');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });

            const roomInputs = document.querySelectorAll('[data-room-input]');
            roomInputs.forEach((input) => {
                input.addEventListener('blur', () => {
                    if (input.value.trim() === '') {
                        const form = input.closest('[data-room-form]');
                        if (form) {
                            form.submit();
                        }
                    }
                });
            });

            const setupSearch = (inputSelector, itemSelector) => {
                const searchInput = document.querySelector(inputSelector);
                if (!searchInput) {
                    return;
                }

                const items = Array.from(document.querySelectorAll(itemSelector));
                const syncSearch = () => {
                    const term = searchInput.value.trim().toLowerCase();
                    items.forEach((item) => {
                        const text = item.getAttribute('data-search-text') || '';
                        item.style.display = text.includes(term) ? '' : 'none';
                    });
                };

                searchInput.addEventListener('input', syncSearch);
                syncSearch();
            };

            setupSearch('[data-teacher-search]', '[data-account-search-item]');
            setupSearch('[data-teacher-overview-search]', '[data-search-item]');

            const selectableRows = document.querySelectorAll('[data-selectable-row]');
            selectableRows.forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target instanceof HTMLElement && event.target.closest('a, button, input, form, textarea, select, label')) {
                        return;
                    }

                    const isSelected = row.classList.contains('is-selected');
                    selectableRows.forEach((entry) => entry.classList.remove('is-selected'));
                    if (!isSelected) {
                        row.classList.add('is-selected');
                    }
                });

                row.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        row.click();
                    }
                });
            });

        })();
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.portal', [
    'pageTitle' => 'Admin-Übersicht',
    'roleTitle' => 'Admin-Ansicht',
    'theme' => 'admin',
    'homeRoute' => 'admin.dashboard',
    'navLinks' => [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>