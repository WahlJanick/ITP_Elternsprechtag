<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Timeslot;
use App\Models\User;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class PortalController extends Controller
{
    private const DEFAULT_SCHOOL_CLASSES = [
        '1AFME', '1AHET', '1AHIT', '1AHMBA', '1AHWIM', '1BHMBA', '1BHWIM',
        '2AAME', '2AFME', '2AHET', '2AHIT', '2AHMBA', '2AHWIM', '2BHMBA', '2BHWIM',
        '3AAME', '3AFME', '3AHET', '3AHIT', '3AHMBA', '3AHWIM', '3AKME', '3BHMBA', '3BHWIM',
        '4AAME', '4AFME', '4AHET', '4AHIT', '4AHMBA', '4AHWIM', '4AKME', '4BHMBA', '4BHWIM',
        '5AAME', '5AHET', '5AHIT', '5AHMBA', '5AHWIM', '5AKME', '5BHMBA', '5BHWIM',
        '6AAME', '6AKME',
    ];

    private const DEFAULT_TEACHER_EMAIL_DOMAIN = 'schule.at';

    public function studentDashboard()
    {
        $this->ensureStudent();

        $assignedTeachers = $this->teachersForCurrentStudent();
        $teachers = $assignedTeachers->filter(fn (array $teacher) => $teacher['free_slots'] > 0)->values();
        $bookings = $this->currentStudentBookings();

        $summary = [
            'count' => $bookings->count(),
            'teacher_names' => $bookings->pluck('teacher_name')->unique()->implode(' / '),
            'assigned_teacher_count' => $assignedTeachers->count(),
            'teacher_count' => $teachers->count(),
            'free_slot_count' => $teachers->sum('free_slots'),
        ];

        return view('student.dashboard', [
            'summary' => $summary,
            'assignedTeachers' => $assignedTeachers->take(6),
            'currentClass' => $this->currentStudentClass(),
        ]);
    }

    public function studentTeachers()
    {
        $this->ensureStudent();

        return view('student.teachers', [
            'teachers' => $this->teachersForCurrentStudent(),
            'currentClass' => $this->currentStudentClass(),
        ]);
    }

    public function studentTeacherShow(string $teacher)
    {
        $this->ensureStudent();

        $teachers = $this->teachersForCurrentStudent();
        $selectedTeacher = $teachers->firstWhere('slug', $teacher)
            ?? $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        $student = $this->studentRecordOrNull();
        $alreadyBooked = $student !== null && Timeslot::query()
            ->where('teacher_id', $selectedTeacher['reference_id'])
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->exists();

        $teacherRoom = $this->getTeacherRoom($selectedTeacher['reference_id']);

        return view('student.teacher-show', [
            'teacher' => $selectedTeacher,
            'teachers' => $this->getAdjacentTeachers($teachers, $selectedTeacher['slug']),
            'freeSlots' => $this->freeSlotsForTeacher($selectedTeacher),
            'alreadyBooked' => $alreadyBooked,
            'teacherRoom' => $teacherRoom,
        ]);
    }

    public function bookTeacherSlot(Timeslot $timeslot): RedirectResponse
    {
        $this->ensureStudent();

        return $this->processBooking($timeslot);
    }

    public function bookSlotFromForm(): RedirectResponse
    {
        $this->ensureStudent();

        $timeslotId = request()->input('timeslot_id');

        if (! $timeslotId) {
            return redirect()
                ->back()
                ->with('error', 'Bitte wähle einen Termin aus.');
        }

        $timeslot = Timeslot::find($timeslotId);

        if (! $timeslot) {
            return redirect()
                ->back()
                ->with('error', 'Termin nicht gefunden.');
        }

        return $this->processBooking($timeslot);
    }

    private function processBooking(Timeslot $timeslot): RedirectResponse
    {
        if ($timeslot->is_reserved) {
            return redirect()
                ->back()
                ->with('error', 'Dieser Termin wurde gerade schon gebucht.');
        }

        $student = $this->ensureStudentRecord();

        $alreadyBooked = Timeslot::query()
            ->where('teacher_id', $timeslot->teacher_id)
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->exists();

        if ($alreadyBooked) {
            return redirect()
                ->back()
                ->with('error', 'Du kannst bei einem Lehrer nur einen Termin buchen.');
        }

        try {
            DB::transaction(function () use ($timeslot, $student) {
                $lockedTimeslot = Timeslot::query()
                    ->lockForUpdate()
                    ->findOrFail($timeslot->getKey());

                if ($lockedTimeslot->is_reserved) {
                    throw new \RuntimeException('Timeslot already reserved.');
                }

                $lockedTimeslot->update([
                    'student_id' => $student->student_id,
                    'is_reserved' => true,
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->with('error', 'Die Buchung konnte nicht gespeichert werden. Bitte versuche es erneut.');
        }

        return redirect()
            ->route('student.bookings')
            ->with('success', 'Termin erfolgreich gebucht.');
    }

    public function studentBookings()
    {
        $this->ensureStudent();

        return view('student.bookings', [
            'bookings' => $this->currentStudentBookings(),
        ]);
    }

    public function cancelStudentBooking(Timeslot $timeslot): RedirectResponse
    {
        $this->ensureStudent();

        $student = $this->ensureStudentRecord();

        abort_if($timeslot->student_id !== $student->student_id, 403);

        $timeslot->update([
            'student_id' => null,
            'is_reserved' => false,
        ]);

        return redirect()
            ->route('student.bookings')
            ->with('success', 'Termin erfolgreich storniert.');
    }

    public function teacherDashboard()
    {
        $this->ensureTeacher();

        $teacher = $this->currentTeacher();
        $appointments = collect();

        if ($teacher !== null) {
            $dateLabel = $this->parentDayLabel();
            $appointments = Timeslot::query()
                ->with('student')
                ->where('teacher_id', $teacher->teacher_id)
                ->orderBy('starts_at')
                ->get()
                ->map(function (Timeslot $timeslot) use ($dateLabel) {
                    return [
                        'id' => $timeslot->id,
                        'student_name' => $timeslot->student?->full_name ?? 'Freier Termin',
                        'class_name' => $timeslot->student?->class_name ?? 'Offen',
                        'room' => $timeslot->room,
                        'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                        'date_label' => $dateLabel,
                        'is_reserved' => (bool) $timeslot->is_reserved,
                    ];
                });
        }

        return view('teacher.dashboard', [
            'appointments' => $appointments,
            'teacher' => $teacher,
            'hasTeacherMapping' => $teacher !== null,
        ]);
    }

    public function teacherTimeslotDurationUpdate(Request $request): RedirectResponse
    {
        $this->ensureTeacher();

        $validated = $request->validate([
            'timeslot_duration' => 'required|integer|min:5|max:120',
        ]);

        $teacher = $this->currentTeacher();

        abort_if(! $teacher, 404);

        $newDuration = (int) $validated['timeslot_duration'];

        if ($teacher->timeslot_duration !== $newDuration) {
            $teacher->update([
                'timeslot_duration' => $newDuration,
                'duration_changed_at' => Carbon::now(),
                'duration_changed_by_teacher' => true,
            ]);
        }

        return redirect()
            ->route('teacher.dashboard')
            ->with('success', 'Termindauer wurde gespeichert.');
    }

    public function adminDashboard()
    {
        $this->ensureAdmin();

        $teachers = $this->allTeacherProfiles();
        $teacherDurationChanges = $teachers->filter(fn (array $teacher) => $teacher['duration_changed']);
        $schoolClasses = SchoolClass::query()->orderBy('name')->get();
        $rooms = Room::query()->orderBy('name')->get();
        $parentDay = $this->parentDay();
        $hasTimeslots = Timeslot::query()->exists();

        $stats = [
            'students' => Student::count(),
            'teachers' => $teachers->count(),
            'free_slots' => Timeslot::query()->where('is_reserved', false)->count(),
            'booked_slots' => Timeslot::query()->where('is_reserved', true)->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'teachers' => $teachers,
            'teacherDurationChanges' => $teacherDurationChanges,
            'teacherDurationChangeCount' => $teacherDurationChanges->count(),
            'teacherAccounts' => $this->teacherAccessAccounts(),
            'classOptions' => $this->schoolClassOptions(),
            'schoolClasses' => $schoolClasses,
            'rooms' => $rooms,
            'parentDayValue' => $parentDay ? $parentDay->format('Y-m-d') : '',
            'parentDayLabel' => $parentDay ? $parentDay->format('d/m/Y') : '--/--/----',
            'canGenerateTimeslots' => ! $hasTimeslots,
        ]);
    }

    public function adminParentDayUpdate(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'parent_day' => 'required|date',
        ]);

        $parentDay = Carbon::parse($validated['parent_day'])->startOfDay();

        DB::table('settings')->updateOrInsert(
            ['key' => 'parent_day'],
            ['value' => $parentDay->toDateString()]
        );

        $timeslots = Timeslot::query()->get();

        if ($timeslots->isNotEmpty()) {
            foreach ($timeslots as $timeslot) {
                $startTime = $timeslot->starts_at?->format('H:i:s');
                $endTime = $timeslot->ends_at?->format('H:i:s');

                if (! $startTime || ! $endTime) {
                    continue;
                }

                $timeslot->update([
                    'starts_at' => $parentDay->copy()->setTimeFromTimeString($startTime),
                    'ends_at' => $parentDay->copy()->setTimeFromTimeString($endTime),
                ]);
            }
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Datum des Elternsprechtags wurde gespeichert.');
    }

    public function adminClassesStore(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:1000',
        ]);

        $classNames = $this->normalizeClassNames(
            $this->extractListEntries($validated['name'])
        );

        if ($classNames === []) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte eine gültige Klassenbezeichnung eingeben.');
        }

        $this->ensureSchoolClassesExist($classNames);

        $message = count($classNames) === 1
            ? "Klasse {$classNames[0]} wurde angelegt."
            : 'Klassen wurden angelegt.';

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $message);
    }

    public function adminClassesUpdate(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'nullable|string|max:50',
        ]);

        $name = $this->normalizeSingleClassName((string) ($validated['name'] ?? ''));

        if ($name === '') {
            $schoolClass->delete();

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Klasse wurde gelöscht.');
        }

        if ($schoolClass->name !== $name && SchoolClass::query()->where('name', $name)->exists()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', "Klasse {$name} existiert bereits.");
        }

        $schoolClass->update(['name' => $name]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Klasse wurde aktualisiert.');
    }

    public function adminClassesDelete(SchoolClass $schoolClass): RedirectResponse
    {
        $this->ensureAdmin();

        $schoolClass->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Klasse wurde gelöscht.');
    }

    public function adminRoomsStore(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:1000',
        ]);

        $roomNames = $this->normalizeRoomNames(
            $this->extractRoomNames($validated['name'])
        );

        if ($roomNames === []) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte eine gültige Raumbezeichnung eingeben.');
        }

        $existing = Room::query()->whereIn('name', $roomNames)->pluck('name')->all();
        $missing = array_values(array_diff($roomNames, $existing));

        if ($missing !== []) {
            Room::insert(array_map(fn (string $name) => ['name' => $name], $missing));
        }

        $message = count($roomNames) === 1
            ? "Raum {$roomNames[0]} wurde angelegt."
            : 'Räume wurden angelegt.';

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $message);
    }

    public function adminRoomsUpdate(Request $request, Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
        ]);

        $name = $this->normalizeRoomName((string) ($validated['name'] ?? ''));

        if ($name === '') {
            $room->delete();

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Raum wurde gelöscht.');
        }

        if ($room->name !== $name && Room::query()->where('name', $name)->exists()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', "Raum {$name} existiert bereits.");
        }

        $room->update(['name' => $name]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Raum wurde aktualisiert.');
    }

    public function adminRoomsDelete(Room $room): RedirectResponse
    {
        $this->ensureAdmin();

        $room->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Raum wurde gelöscht.');
    }

    public function adminTeacherAccountsStore(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_emails' => 'required|string|max:5000',
            'timeslot_duration' => 'required|integer|min:5|max:120',
            'timeslot_start' => 'required|date_format:H:i',
            'timeslot_end' => 'required|date_format:H:i|after:timeslot_start',
            'timeslot_room' => 'required|string|max:255',
            'classes' => 'nullable|array',
            'classes.*' => 'string|max:20',
            'additional_classes' => 'nullable|string|max:255',
        ]);

        $entries = $this->parseTeacherEntries($validated['teacher_emails']);

        if ($entries === []) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte gib mindestens eine gültige E-Mail-Adresse ein.');
        }

        $parentDay = $this->parentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst das Datum des Elternsprechtags speichern.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $normalizedClasses = $this->normalizeClassNames(array_merge(
            $validated['classes'] ?? [],
            $this->extractAdditionalClasses($validated['additional_classes'] ?? '')
        ));
        $this->ensureSchoolClassesExist($normalizedClasses);

        foreach ($entries as $entry) {
            $email = $entry['email'];
            $displayName = trim("{$entry['first_name']} {$entry['last_name']}");
            $user = User::firstOrNew(['email' => $email]);
            $alreadyExisted = $user->exists;
            $firstName = $entry['first_name'];
            $lastName = $entry['last_name'];

            if (! $alreadyExisted || blank($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }

            if ($entry['name_provided'] || blank($user->name) || $user->name === $user->email) {
                $user->name = $displayName;
            }

            $user->is_teacher = true;
            $teacherWasCreated = false;

            if ($user->teacher_id === null) {
                $teacher = Teacher::create([
                    'teacher_id' => $this->nextTeacherId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $this->shortCode($displayName),
                    'classes' => $normalizedClasses,
                ]);

                $user->teacher_id = $teacher->teacher_id;
                $teacherWasCreated = true;
            }

            if ($user->teacher_id !== null) {
                $updateData = ['classes' => $normalizedClasses];

                if ($entry['name_provided']) {
                    $updateData['first_name'] = $firstName;
                    $updateData['last_name'] = $lastName;
                }

                Teacher::query()
                    ->where('teacher_id', $user->teacher_id)
                    ->update($updateData);
            }

            $user->save();

            if ($teacherWasCreated) {
                $this->createTimeslotsForTeacher(
                    $user->teacher_id,
                    $parentDay,
                    $validated['timeslot_start'],
                    $validated['timeslot_end'],
                    (int) $validated['timeslot_duration'],
                    $validated['timeslot_room']
                );
            }

            if ($alreadyExisted) {
                $updatedCount++;
            } else {
                $createdCount++;
            }
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Lehrerzugänge gespeichert. Neu: {$createdCount}, aktualisiert: {$updatedCount}.");
    }

    public function adminTeacherAccountCreateProfile(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        abort_unless($user->is_teacher, 404);

        if ($user->teacher_id === null) {
            $derivedName = blank($user->name) ? $this->placeholderNameFromEmail($user->email) : $user->name;
            [$firstName, $lastName] = $this->splitName($derivedName);

            $teacher = Teacher::create([
                'teacher_id' => $this->nextTeacherId(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'kuerzel' => $this->shortCode($derivedName),
                'classes' => [],
            ]);

            $user->teacher_id = $teacher->teacher_id;
            $user->save();
        }

        $teacher = Teacher::findOrFail($user->teacher_id);

        return redirect()
            ->route('admin.teachers.show', Str::slug($teacher->full_name.'-'.$teacher->teacher_id))
            ->with('success', 'Lehrerprofil wurde angelegt.');
    }

    public function adminTeacherAccountDelete(User $user): RedirectResponse
    {
        $this->ensureAdmin();

        abort_unless($user->is_teacher, 404);

        if ($user->teacher_id !== null) {
            Teacher::query()->where('teacher_id', $user->teacher_id)->delete();
        }

        $user->delete();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Lehrerzugang wurde gelöscht.');
    }

    public function adminTeacherShow(string $teacher)
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        return view('admin.teacher-show', [
            'teacher' => $selectedTeacher,
            'appointments' => $this->adminAppointmentsForTeacher($selectedTeacher),
            'classOptions' => $this->availableClassOptions($selectedTeacher),
            'filters' => [
                'classes' => ['Alle Klassen', '1AHIT', '2AHIT', '3AHIT', '4AHIT', '5AHIT', '3AHMBA', '4AHMBA'],
                'rooms' => ['Alle Räume', 'B201', 'B203', 'A104', 'Lab 2', '3AHMBA', '4AHIT'],
                'times' => ['17:00', '17:10', '17:20', '17:30', '17:40', '17:50'],
            ],
        ]);
    }

    public function adminTeacherUpdate(Request $request, string $teacher): RedirectResponse
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'kuerzel' => 'nullable|string|max:20',
        ]);

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);
        $teacherModel->update([
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'kuerzel' => trim((string) ($validated['kuerzel'] ?? '')) ?: null,
        ]);

        $newSlug = Str::slug($teacherModel->full_name.'-'.$teacherModel->teacher_id);

        return redirect()
            ->route('admin.teachers.show', $newSlug)
            ->with('success', 'Lehrerdaten wurden aktualisiert.');
    }

    public function adminTeacherQuickUpdate(Request $request, string $teacher): RedirectResponse
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'kuerzel' => 'nullable|string|max:20',
            'class_list' => 'nullable|string|max:1000',
        ]);

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);
        $normalizedClasses = $this->normalizeClassNames(
            $this->extractAdditionalClasses($validated['class_list'] ?? '')
        );
        $this->ensureSchoolClassesExist($normalizedClasses);

        $teacherModel->update([
            'first_name' => trim($validated['first_name']),
            'last_name' => trim($validated['last_name']),
            'kuerzel' => trim((string) ($validated['kuerzel'] ?? '')) ?: null,
            'classes' => $normalizedClasses,
        ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Lehrer {$teacherModel->full_name} wurde aktualisiert.");
    }

    public function adminTeacherClassesUpdate(Request $request, string $teacher): RedirectResponse
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        $validated = $request->validate([
            'classes' => 'nullable|array',
            'classes.*' => 'string|max:20',
            'additional_classes' => 'nullable|string|max:255',
        ]);

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);
        $normalizedClasses = $this->normalizeClassNames(array_merge(
            $validated['classes'] ?? [],
            $this->extractAdditionalClasses($validated['additional_classes'] ?? '')
        ));

        $this->ensureSchoolClassesExist($normalizedClasses);

        $teacherModel->update([
            'classes' => $normalizedClasses,
        ]);

        return redirect()
            ->route('admin.teachers.show', Str::slug($teacherModel->full_name.'-'.$teacherModel->teacher_id))
            ->with('success', 'Klassen wurden dem Lehrer zugeteilt.');
    }

    public function adminReleaseSlot(Timeslot $timeslot): RedirectResponse
    {
        $this->ensureAdmin();

        $timeslot->update([
            'student_id' => null,
            'is_reserved' => false,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Termin wurde freigegeben.');
    }

    private function allTeacherProfiles(): Collection
    {
        return Teacher::query()
            ->withCount([
                'timeslots as free_slots' => fn ($query) => $query->where('is_reserved', false),
                'timeslots as booked_slots' => fn ($query) => $query->where('is_reserved', true),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (Teacher $teacher) {
                $duration = $teacher->timeslot_duration;

                return [
                    'slug' => Str::slug($teacher->full_name.'-'.$teacher->teacher_id),
                    'name' => $teacher->full_name,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'kuerzel' => $teacher->kuerzel,
                    'short' => $teacher->kuerzel ?: $this->shortCode($teacher->full_name),
                    'classes' => $teacher->classes ?? [],
                    'display_classes' => $teacher->classes ? implode(' / ', $teacher->classes) : 'Alle Klassen',
                    'free_slots' => (int) $teacher->free_slots,
                    'booked_slots' => (int) $teacher->booked_slots,
                    'reference_id' => $teacher->teacher_id,
                    'timeslot_duration' => $duration,
                    'timeslot_duration_label' => $duration ? $duration.' min' : 'Standard',
                    'duration_changed' => (bool) $teacher->duration_changed_by_teacher,
                    'duration_changed_label' => $teacher->duration_changed_at
                        ? $teacher->duration_changed_at->format('d/m/Y H:i')
                        : null,
                ];
            })
            ->values();
    }

    private function availableTeachersForCurrentStudent(): Collection
    {
        return $this->teachersForCurrentStudent()
            ->filter(fn (array $teacher) => $teacher['free_slots'] > 0)
            ->values();
    }

    private function teachersForCurrentStudent(): Collection
    {
        $studentClass = $this->currentStudentClass();

        return $this->allTeacherProfiles()
            ->filter(fn (array $teacher) => $this->teacherMatchesStudentClass($teacher, $studentClass))
            ->values();
    }

    private function currentStudentBookings(): Collection
    {
        $student = $this->studentRecordOrNull();

        if ($student === null) {
            return collect();
        }

        $dateLabel = $this->parentDayLabel();

        return Timeslot::query()
            ->with('teacher')
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) use ($dateLabel) {
                $teacherName = $timeslot->teacher?->full_name ?? 'Lehrer';

                return [
                    'id' => $timeslot->id,
                    'teacher_name' => $teacherName,
                    'teacher_short' => $timeslot->teacher?->kuerzel ?: $this->shortCode($teacherName),
                    'class_name' => $this->currentStudentClass() ?? $this->studentRecordOrNull()?->class_name ?? 'Unbekannt',
                    'room' => $timeslot->room,
                    'date_label' => $dateLabel,
                    'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                ];
            })
            ->values();
    }

    private function adminAppointmentsForTeacher(array $teacher): Collection
    {
        $dateLabel = $this->parentDayLabel();

        return Timeslot::query()
            ->with('student')
            ->where('teacher_id', $teacher['reference_id'])
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) use ($dateLabel) {
                return [
                    'id' => $timeslot->id,
                    'student_name' => $timeslot->student?->full_name ?? 'Freier Termin',
                    'class_name' => $timeslot->student?->class_name ?? 'Offen',
                    'room' => $timeslot->room,
                    'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                    'date_label' => $dateLabel,
                    'is_reserved' => (bool) $timeslot->is_reserved,
                ];
            })
            ->values();
    }

    private function freeSlotsForTeacher(array $teacher): Collection
    {
        $dateLabel = $this->parentDayLabel();

        return Timeslot::query()
            ->where('teacher_id', $teacher['reference_id'])
            ->where('is_reserved', false)
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) use ($dateLabel) {
                return [
                    'id' => $timeslot->id,
                    'label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                    'room' => $timeslot->room,
                    'date_label' => $dateLabel,
                ];
            })
            ->values();
    }

    private function getTeacherRoom(int $teacherId): string
    {
        $firstSlot = Timeslot::query()
            ->where('teacher_id', $teacherId)
            ->first();

        return $firstSlot?->room ?? 'Noch offen';
    }

    private function getAdjacentTeachers(Collection $teachers, string $currentSlug): Collection
    {
        $allTeachers = $teachers->values();
        $currentIndex = $allTeachers->search(fn ($t) => $t['slug'] === $currentSlug);

        if ($currentIndex === false) {
            return collect();
        }

        // 2 Lehrer davor und 2 danach
        $start = max(0, $currentIndex - 2);
        $end = min($allTeachers->count() - 1, $currentIndex + 2);

        return $allTeachers->slice($start, $end - $start + 1)->values();
    }

    private function teacherMatchesStudentClass(array $teacher, ?string $studentClass): bool
    {
        if ($studentClass === null) {
            return true;
        }

        return $teacher['classes'] === []
            || in_array($studentClass, $teacher['classes'], true);
    }

    private function availableClassOptions(array $teacher): Collection
    {
        return $this->schoolClassOptions()
            ->merge($teacher['classes'])
            ->map(fn (string $className) => $this->normalizeSingleClassName($className))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function schoolClassOptions(): Collection
    {
        $classes = SchoolClass::query()->orderBy('name')->pluck('name');

        if ($classes->isEmpty()) {
            return collect(self::DEFAULT_SCHOOL_CLASSES);
        }

        return $classes;
    }

    private function ensureSchoolClassesExist(array $classNames): void
    {
        if ($classNames === []) {
            return;
        }

        $normalized = $this->normalizeClassNames($classNames);
        $existing = SchoolClass::query()->whereIn('name', $normalized)->pluck('name')->all();
        $missing = array_values(array_diff($normalized, $existing));

        if ($missing !== []) {
            SchoolClass::insert(array_map(fn (string $name) => ['name' => $name], $missing));
        }
    }

    private function parentDay(): ?Carbon
    {
        $stored = DB::table('settings')
            ->where('key', 'parent_day')
            ->value('value');

        if ($stored) {
            return Carbon::parse($stored)->startOfDay();
        }

        $fallback = Timeslot::query()
            ->orderBy('starts_at')
            ->value('starts_at');

        if (! $fallback) {
            return null;
        }

        $resolved = Carbon::parse($fallback)->startOfDay();

        DB::table('settings')->updateOrInsert(
            ['key' => 'parent_day'],
            ['value' => $resolved->toDateString()]
        );

        return $resolved;
    }

    private function parentDayLabel(): string
    {
        $parentDay = $this->parentDay();

        return $parentDay ? $parentDay->format('d/m/Y') : '--/--/----';
    }

    public function adminTeacherImport(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_file' => 'required|file|mimes:xlsx,xls',
            'create_timeslots' => 'nullable|boolean',
            'timeslot_duration' => 'nullable|integer|min:5|max:120',
            'timeslot_start' => 'nullable|date_format:H:i',
            'timeslot_end' => 'nullable|date_format:H:i|after:timeslot_start',
            'timeslot_room' => 'nullable|string|max:255',
        ]);

        $createTimeslots = (bool) ($validated['create_timeslots'] ?? false);

        if ($createTimeslots) {
            $missing = collect(['timeslot_duration', 'timeslot_start', 'timeslot_end', 'timeslot_room'])
                ->filter(fn (string $key) => empty($validated[$key] ?? null))
                ->all();

            if ($missing !== []) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', 'Bitte alle Termin-Daten für den Import ausfüllen.');
            }

            if (! $this->parentDay()) {
                return redirect()
                    ->route('admin.dashboard')
                    ->with('error', 'Bitte zuerst das Datum des Elternsprechtags speichern.');
            }
        }

        $file = $request->file('teacher_file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Die Excel-Datei konnte nicht gelesen werden.');
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Die Excel-Datei enthält keine Daten.');
        }

        $headerRow = array_shift($rows);
        $headerMap = $this->mapImportHeaders($headerRow);

        $colFirst = $this->resolveImportColumn($headerMap, ['vorname', 'first_name', 'firstname', 'first name']);
        $colLast = $this->resolveImportColumn($headerMap, ['nachname', 'last_name', 'lastname', 'last name']);
        $colKuerzel = $this->resolveImportColumn($headerMap, ['kuerzel', 'kuerzel', 'short', 'code']);
        $colEmail = $this->resolveImportColumn($headerMap, ['email', 'e-mail', 'mail']);
        $colClasses = $this->resolveImportColumn($headerMap, ['klassen', 'klassenliste', 'classes', 'class']);

        if (! $colFirst || ! $colLast || (! $colKuerzel && ! $colEmail)) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Die Excel-Datei muss Vorname, Nachname und Kürzel oder E-Mail enthalten.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $parentDay = $createTimeslots ? $this->parentDay() : null;

        foreach ($rows as $row) {
            $firstName = trim((string) ($row[$colFirst] ?? ''));
            $lastName = trim((string) ($row[$colLast] ?? ''));
            $kuerzel = trim((string) ($colKuerzel ? ($row[$colKuerzel] ?? '') : ''));
            $email = trim((string) ($colEmail ? ($row[$colEmail] ?? '') : ''));
            $classRaw = trim((string) ($colClasses ? ($row[$colClasses] ?? '') : ''));

            if ($firstName === '' && $lastName === '' && $kuerzel === '' && $email === '') {
                continue;
            }

            if ($email === '') {
                $email = $this->emailFromKuerzel($kuerzel);
            }

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $skippedCount++;
                continue;
            }

            $email = Str::lower($email);

            if ($firstName === '' || $lastName === '' || $email === '') {
                $skippedCount++;
                continue;
            }

            $normalizedClasses = $this->normalizeClassNames(
                $this->extractAdditionalClasses($classRaw)
            );

            $this->ensureSchoolClassesExist($normalizedClasses);

            $user = User::firstOrNew(['email' => $email]);
            $alreadyExisted = $user->exists;

            if (! $alreadyExisted || blank($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }

            $user->name = trim("{$firstName} {$lastName}");
            $user->is_teacher = true;

            $teacherWasCreated = false;

            if ($user->teacher_id === null) {
                $teacher = Teacher::create([
                    'teacher_id' => $this->nextTeacherId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $kuerzel !== '' ? $kuerzel : $this->shortCode($user->name),
                    'classes' => $normalizedClasses,
                ]);

                $user->teacher_id = $teacher->teacher_id;
                $teacherWasCreated = true;
            }

            if ($user->teacher_id !== null) {
                Teacher::query()
                    ->where('teacher_id', $user->teacher_id)
                    ->update([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'kuerzel' => $kuerzel !== '' ? $kuerzel : $this->shortCode($user->name),
                        'classes' => $normalizedClasses,
                    ]);
            }

            $user->save();

            if ($teacherWasCreated && $createTimeslots && $parentDay) {
                $this->createTimeslotsForTeacher(
                    $user->teacher_id,
                    $parentDay,
                    $validated['timeslot_start'],
                    $validated['timeslot_end'],
                    (int) $validated['timeslot_duration'],
                    $validated['timeslot_room']
                );
            }

            if ($alreadyExisted) {
                $updatedCount++;
            } else {
                $createdCount++;
            }
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Import abgeschlossen. Neu: {$createdCount}, aktualisiert: {$updatedCount}, übersprungen: {$skippedCount}.");
    }

    private function teacherAccessAccounts(): Collection
    {
        return User::query()
            ->where('is_teacher', true)
            ->orderBy('name')
            ->orderBy('email')
            ->get()
            ->map(function (User $user) {
                $teacher = $user->teacher_id ? Teacher::find($user->teacher_id) : null;

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'teacher_name' => $teacher?->full_name,
                    'teacher_slug' => $teacher ? Str::slug($teacher->full_name.'-'.$teacher->teacher_id) : null,
                    'display_classes' => $teacher?->classes ? implode(' / ', $teacher->classes) : '-',
                ];
            });
    }

    private function parseTeacherEntries(string $value): array
    {
        $entries = [];
        $rawEntries = preg_split('/[\r\n,;]+/', trim($value)) ?: [];

        foreach ($rawEntries as $raw) {
            $raw = trim($raw);

            if ($raw === '') {
                continue;
            }

            if (! preg_match('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', $raw, $matches)) {
                continue;
            }

            $email = Str::lower($matches[0]);
            $namePart = trim(str_replace([$matches[0], '<', '>', '"'], '', $raw));
            $namePart = preg_replace('/\s+/', ' ', $namePart) ?? '';
            $namePart = trim($namePart);
            $nameProvided = $namePart !== '';

            if (! $nameProvided) {
                $namePart = $this->placeholderNameFromEmail($email);
            }

            [$firstName, $lastName] = $this->splitName($namePart);

            $entries[] = [
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name_provided' => $nameProvided,
            ];
        }

        return collect($entries)
            ->unique('email')
            ->values()
            ->all();
    }

    private function extractListEntries(string $value): array
    {
        return preg_split('/[\r\n,;]+/', trim($value)) ?: [];
    }

    private function extractAdditionalClasses(string $value): array
    {
        return preg_split('/[\s,;]+/', trim($value)) ?: [];
    }

    private function extractRoomNames(string $value): array
    {
        return preg_split('/[\r\n,;]+/', trim($value)) ?: [];
    }

    private function normalizeClassNames(array $classes): array
    {
        return collect($classes)
            ->map(fn ($className) => $this->normalizeSingleClassName((string) $className))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizeSingleClassName(string $className): string
    {
        $normalized = Str::upper(trim($className));
        $normalized = preg_replace('/^KLASSE\s*/', '', $normalized) ?? $normalized;

        return preg_replace('/\s+/', '', $normalized) ?? '';
    }

    private function normalizeRoomNames(array $rooms): array
    {
        return collect($rooms)
            ->map(fn ($room) => $this->normalizeRoomName((string) $room))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function normalizeRoomName(string $room): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($room)) ?? '';

        return $normalized;
    }

    private function mapImportHeaders(array $headerRow): array
    {
        $mapped = [];

        foreach ($headerRow as $column => $value) {
            $label = Str::of((string) $value)->lower()->trim()->value();
            if ($label !== '') {
                $mapped[$label] = $column;
            }
        }

        return $mapped;
    }

    private function resolveImportColumn(array $headerMap, array $aliases): ?string
    {
        foreach ($aliases as $alias) {
            $key = Str::of($alias)->lower()->trim()->value();
            if (isset($headerMap[$key])) {
                return $headerMap[$key];
            }
        }

        return null;
    }

    private function emailFromKuerzel(string $kuerzel): string
    {
        $localPart = Str::of($kuerzel)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();

        if ($localPart === '') {
            return '';
        }

        return $localPart.'@'.self::DEFAULT_TEACHER_EMAIL_DOMAIN;
    }

    private function extractEmailAddresses(string $value): array
    {
        return collect(preg_split('/[\s,;]+/', trim($value)) ?: [])
            ->map(fn (string $email) => Str::lower(trim($email)))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    private function placeholderNameFromEmail(string $email): string
    {
        $localPart = Str::before($email, '@');

        $normalized = Str::of($localPart)
            ->replace(['.', '_', '-'], ' ')
            ->replaceMatches('/\d+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title()
            ->value();

        return $normalized !== '' ? $normalized : $email;
    }

    private function nextTeacherId(): int
    {
        return ((int) Teacher::query()->max('teacher_id')) + 1;
    }

    private function nextTimeslotId(): int
    {
        return ((int) Timeslot::query()->max('id')) + 1;
    }

    private function createTimeslotsForTeacher(
        int $teacherId,
        Carbon $day,
        string $startTime,
        string $endTime,
        int $durationInMinutes,
        string $room
    ): void {
        $current = $day->copy()->setTimeFromTimeString($startTime);
        $end = $day->copy()->setTimeFromTimeString($endTime);
        $timeslots = [];
        $nextId = $this->nextTimeslotId();

        while ($current->copy()->addMinutes($durationInMinutes)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($durationInMinutes);

            $timeslots[] = [
                'id' => $nextId++,
                'teacher_id' => $teacherId,
                'student_id' => null,
                'starts_at' => $current->toDateTimeString(),
                'ends_at' => $slotEnd->toDateTimeString(),
                'room' => $room,
                'is_reserved' => false,
            ];

            $current = $slotEnd;
        }

        if ($timeslots !== []) {
            Timeslot::insert($timeslots);
        }
    }

    private function ensureStudentRecord(): Student
    {
        $this->ensureStudent();

        $user = auth()->user();
        [$firstName, $lastName] = $this->splitName($user->name);

        return Student::updateOrCreate(
            ['student_id' => $user->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'class_name' => $this->currentStudentClass() ?? 'Unbekannt',
            ]
        );
    }

    private function studentRecordOrNull(): ?Student
    {
        $user = auth()->user();

        if (! $user || ! $user->isStudentUser()) {
            return null;
        }

        return Student::find($user->id);
    }

    private function currentTeacher(): ?Teacher
    {
        $user = auth()->user();

        if (! $user || ! $user->isTeacherUser()) {
            return null;
        }

        if ($user->teacher_id !== null) {
            return Teacher::find($user->teacher_id);
        }

        $normalizedUserName = $this->normalizeComparableValue($user->name);

        return Teacher::query()
            ->get()
            ->first(function (Teacher $teacher) use ($normalizedUserName) {
                return $normalizedUserName === $this->normalizeComparableValue($teacher->full_name);
            });
    }

    private function currentStudentClass(): ?string
    {
        $rawValue = Str::upper(trim((string) auth()->user()?->klasse));
        $normalizedValue = preg_replace('/\s+/', '', $rawValue);

        return preg_match('/^\d[A-Z0-9]{3,7}$/', $normalizedValue) === 1
            ? $normalizedValue
            : null;
    }

    private function splitName(string $name): array
    {
        $parts = collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->values();

        $firstName = $parts->shift() ?? 'Azure';
        $lastName = $parts->implode(' ');

        return [$firstName, $lastName !== '' ? $lastName : 'User'];
    }

    private function shortCode(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)))->filter()->values();

        if ($parts->count() >= 2) {
            return Str::upper(Str::substr($parts[0], 0, 1).Str::substr($parts[1], 0, 1));
        }

        return Str::upper(Str::substr($name, 0, 2));
    }

    private function normalizeComparableValue(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }

    private function ensureStudent(): void
    {
        abort_if(auth()->user()?->isTeacherUser() || auth()->user()?->isAdminUser(), 403);
    }

    private function ensureTeacher(): void
    {
        abort_unless(auth()->user()?->isTeacherUser(), 403);
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->isAdminUser(), 403);
    }

    public function adminTimeslotCreate(Request $request)
    {
        $this->ensureAdmin();

        $teacherId = $request->input('teacher');
        $teacher = Teacher::find($teacherId);

        abort_if(!$teacher, 404);

        return view('admin.timeslot-form', [
            'teacher' => $teacher,
            'preselectedTeacherId' => $teacher->teacher_id,
            'teachers' => Teacher::all(),
            'students' => Student::all(),
            'rooms' => Room::query()->orderBy('name')->get(),
            'parentDayLabel' => $this->parentDayLabel(),
            'parentDayValue' => $this->parentDay()?->format('Y-m-d') ?? '',
            'isEdit' => false,
        ]);
    }

    public function adminTimeslotStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,teacher_id',
            'student_id' => 'nullable|integer|exists:students,student_id',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'room' => 'required|string|max:255',
            'is_reserved' => 'boolean',
        ]);

        $parentDay = $this->parentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst das Datum des Elternsprechtags speichern.');
        }

        $startTime = $parentDay->copy()->setTimeFromTimeString($validated['starts_at']);
        $endTime = $parentDay->copy()->setTimeFromTimeString($validated['ends_at']);

        Timeslot::create([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'room' => $validated['room'],
            'is_reserved' => $validated['is_reserved'] ?? false,
        ]);

        return redirect()->route('admin.teachers.show', Teacher::find($validated['teacher_id'])->slug)
            ->with('success', 'Termin wurde erstellt.');
    }

    public function adminTimeslotEdit(Timeslot $timeslot)
    {
        $this->ensureAdmin();

        return view('admin.timeslot-form', [
            'timeslot' => $timeslot,
            'teacher' => $timeslot->teacher,
            'teachers' => Teacher::all(),
            'students' => Student::all(),
            'rooms' => Room::query()->orderBy('name')->get(),
            'parentDayLabel' => $this->parentDayLabel(),
            'parentDayValue' => $this->parentDay()?->format('Y-m-d') ?? '',
            'isEdit' => true,
        ]);
    }

    public function adminTimeslotUpdate(Request $request, Timeslot $timeslot)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_id' => 'integer|exists:teachers,teacher_id',
            'student_id' => 'nullable|integer|exists:students,student_id',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'room' => 'required|string|max:255',
            'is_reserved' => 'boolean',
        ]);

        $parentDay = $this->parentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst das Datum des Elternsprechtags speichern.');
        }

        $startTime = $parentDay->copy()->setTimeFromTimeString($validated['starts_at']);
        $endTime = $parentDay->copy()->setTimeFromTimeString($validated['ends_at']);

        $timeslot->update([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'room' => $validated['room'],
            'is_reserved' => $validated['is_reserved'] ?? false,
        ]);

        return redirect()->route('admin.teachers.show', $timeslot->teacher->slug)
            ->with('success', 'Termin wurde aktualisiert.');
    }

    public function adminTimeslotDestroy(Timeslot $timeslot)
    {
        $this->ensureAdmin();

        $teacherSlug = $timeslot->teacher->slug;
        $timeslot->delete();

        return redirect()->route('admin.teachers.show', $teacherSlug)
            ->with('success', 'Termin wurde gelöscht.');
    }
}
