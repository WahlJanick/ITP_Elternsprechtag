<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\TeacherParentDaySetting;
use App\Models\Timeslot;
use App\Models\User;
use App\Models\Room;
use App\Models\ParentDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
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

    private const DEFAULT_TEACHER_EMAIL_DOMAIN = 'htlwy.at';

    public function studentDashboard()
    {
        $this->ensureStudent();

        $assignedTeachers = $this->teachersForCurrentStudent();
        $teachers = $assignedTeachers->filter(fn (array $teacher) => $teacher['free_slots'] > 0)->values();
        $bookings = $this->currentStudentBookings();
        $parentDays = $this->availableParentDaysForCurrentUser();

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
            'parentDays' => $parentDays,
            'activeParentDay' => $this->currentParentDay(),
        ]);
    }

    public function studentTeachers()
    {
        $this->ensureStudent();

        return view('student.teachers', [
            'teachers' => $this->teachersForCurrentStudent(),
            'currentClass' => $this->currentStudentClass(),
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $this->currentParentDay(),
        ]);
    }

    public function studentTeacherShow(string $teacher)
    {
        $this->ensureStudent();

        $teachers = $this->teachersForCurrentStudent();
        $selectedTeacher = $teachers->firstWhere('slug', $teacher)
            ?? $this->allTeacherProfiles()->firstWhere('slug', $teacher);
        $parentDay = $this->currentParentDay();

        abort_if(! $selectedTeacher, 404);

        $student = $this->studentRecordOrNull();
        $alreadyBooked = $student !== null && Timeslot::query()
            ->where('teacher_id', $selectedTeacher['reference_id'])
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
            ->exists();

        $teacherRoom = $this->getTeacherRoom($selectedTeacher['reference_id']);
        $freeSlots = $this->freeSlotsForTeacher($selectedTeacher);
        $slotRange = $freeSlots->pluck('label')->filter();
        $bookingNotice = match (true) {
            $alreadyBooked => sprintf(
                'Du hast bei diesem Lehrer am %s bereits einen Termin gebucht.',
                $this->parentDayLabel()
            ),
            $slotRange->isEmpty() => sprintf(
                'Am %s sind derzeit keine freien Termine verfügbar.',
                $this->parentDayLabel()
            ),
            $slotRange->count() === 1 => sprintf(
                'Freier Termin am %s um %s Uhr. Pro Lehrer kannst du einen Termin buchen.',
                $this->parentDayLabel(),
                $slotRange->first()
            ),
            default => sprintf(
                'Freie Termine am %s von %s bis %s Uhr. Pro Lehrer kannst du einen Termin buchen.',
                $this->parentDayLabel(),
                $slotRange->first(),
                $slotRange->last()
            ),
        };

        return view('student.teacher-show', [
            'teacher' => $selectedTeacher,
            'teachers' => $this->getAdjacentTeachers($teachers, $selectedTeacher['slug']),
            'freeSlots' => $freeSlots,
            'alreadyBooked' => $alreadyBooked,
            'teacherRoom' => $teacherRoom,
            'bookingNotice' => $bookingNotice,
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $parentDay,
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
            ->when($timeslot->parent_day_id, fn ($query) => $query->where('parent_day_id', $timeslot->parent_day_id))
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
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $this->currentParentDay(),
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
        $this->ensureTeacherDurationColumnsExist();

        $teacher = $this->currentTeacher();
        $appointments = collect();
        $parentDay = $this->currentParentDay();
        $parentDays = $this->availableParentDaysForCurrentUser();
        $currentDuration = null;

        if ($teacher !== null) {
            $currentDuration = $this->resolveTeacherDuration($teacher, $parentDay);
            $dateLabel = $this->parentDayLabel();
            $appointments = Timeslot::query()
                ->with('student')
                ->where('teacher_id', $teacher->teacher_id)
                ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
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
            'currentDuration' => $currentDuration,
            'hasTeacherMapping' => $teacher !== null,
            'parentDays' => $parentDays,
            'activeParentDay' => $parentDay,
        ]);
    }

    public function teacherTimeslotDurationUpdate(Request $request): RedirectResponse
    {
        $this->ensureTeacher();
        $this->ensureTeacherDurationColumnsExist();

        $validated = $request->validate([
            'timeslot_duration' => 'required|integer|min:5|max:120',
        ]);

        $teacher = $this->currentTeacher();
        $parentDay = $this->currentParentDay();

        abort_if(! $teacher, 404);
        abort_if(! $parentDay, 404);

        $newDuration = (int) $validated['timeslot_duration'];

        $setting = TeacherParentDaySetting::firstOrNew([
            'teacher_id' => $teacher->teacher_id,
            'parent_day_id' => $parentDay->id,
        ]);

        if ((int) $setting->timeslot_duration !== $newDuration) {
            $setting->fill([
                'timeslot_duration' => $newDuration,
                'duration_changed_at' => Carbon::now(),
                'duration_changed_by_teacher' => true,
            ]);
            $setting->save();
        }

        return redirect()
            ->route('teacher.dashboard')
            ->with('success', 'Termindauer wurde gespeichert.');
    }

    public function adminDashboard()
    {
        $this->ensureAdmin();
        $this->ensureTeacherDurationColumnsExist();
        $this->ensureSchoolClassesTableExists();
        $this->ensureRoomsTableExists();

        $parentDay = $this->currentParentDay();
        $parentDays = Schema::hasTable('parent_days')
            ? ParentDay::query()->orderBy('date')->get()
            : collect();
        $teachers = $this->allTeacherProfiles();
        $teacherDurationChanges = $teachers->filter(fn (array $teacher) => $teacher['duration_changed']);
        $schoolClasses = SchoolClass::query()->orderBy('name')->get();
        $rooms = Room::query()->orderBy('name')->get();
        $timeslotBaseQuery = Timeslot::query();

        if ($parentDay) {
            $timeslotBaseQuery->where('parent_day_id', $parentDay->id);
        }

        $hasTimeslots = (clone $timeslotBaseQuery)->exists();

        $stats = [
            'students' => Student::count(),
            'teachers' => $teachers->count(),
            'free_slots' => (clone $timeslotBaseQuery)->where('is_reserved', false)->count(),
            'booked_slots' => (clone $timeslotBaseQuery)->where('is_reserved', true)->count(),
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
            'parentDays' => $parentDays,
            'activeParentDay' => $parentDay,
            'parentDayValue' => $parentDay ? $parentDay->date->format('Y-m-d') : '',
            'parentDayLabel' => $parentDay ? $parentDay->date->format('d/m/Y') : '--/--/----',
            'canGenerateTimeslots' => ! $hasTimeslots,
            'canEditParentDay' => $this->canEditParentDay($parentDay),
        ]);
    }

    public function adminParentDayUpdate(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'parent_day' => 'required|date',
        ]);

        $dateValue = Carbon::parse($validated['parent_day'])->toDateString();
        $parentDay = ParentDay::query()->firstOrCreate([
            'date' => $dateValue,
        ]);

        session(['parent_day_id' => $parentDay->id]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Elternsprechtag wurde gespeichert.');
    }

    public function adminParentDayDelete(ParentDay $parentDay): RedirectResponse
    {
        $this->ensureAdmin();

        $parentDay->delete();

        if (session('parent_day_id') === $parentDay->id) {
            session()->forget('parent_day_id');
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Elternsprechtag wurde gelöscht.');
    }

    public function parentDaySelect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_day_id' => 'required|integer',
        ]);

        $availableParentDays = $this->availableParentDaysForCurrentUser();
        $selectedParentDay = $availableParentDays->firstWhere('id', (int) $validated['parent_day_id']);

        if (! $selectedParentDay) {
            return redirect()
                ->back()
                ->with('error', 'Dieser Elternsprechtag kann nicht ausgewählt werden.');
        }

        session(['parent_day_id' => $selectedParentDay->id]);

        return redirect()
            ->back()
            ->with('parent_day_switched', true);
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
        $this->ensureRoomsTableExists();

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
        $this->ensureRoomsTableExists();

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

        $parentDay = $this->currentParentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst einen Elternsprechtag anlegen.');
        }

        if (! $this->canEditParentDay($parentDay)) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Der Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr möglich.');
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
            $teacherModel = null;

            if (! $alreadyExisted || blank($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }

            if ($entry['name_provided'] || blank($user->name) || $user->name === $user->email) {
                $user->name = $displayName;
            }

            $user->is_teacher = true;
            $teacherWasCreated = false;

            if ($user->teacher_id === null) {
                $teacherModel = Teacher::create([
                    'teacher_id' => $this->nextTeacherId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $this->shortCode($displayName),
                    'classes' => $normalizedClasses,
                ]);

                $user->teacher_id = $teacherModel->teacher_id;
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

                $teacherModel = $teacherModel instanceof Teacher
                    ? $teacherModel->fill($updateData)
                    : Teacher::find($user->teacher_id);
            }

            $user->save();


            if ($teacherWasCreated) {
                $this->upsertTeacherParentDaySetting(
                    $user->teacher_id,
                    $parentDay,
                    (int) $validated['timeslot_duration'],
                    false
                );

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

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);

        return view('admin.teacher-show', [
            'teacher' => $selectedTeacher,
            'classOptions' => $this->availableClassOptions($selectedTeacher),
            'parentDayLabel' => $this->parentDayLabel(),
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $this->currentParentDay(),
            'canEditParentDay' => $this->canEditParentDay($this->currentParentDay()),
        ]);
    }

    public function adminTeacherAppointments(string $teacher)
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        return view('admin.teacher-appointments', [
            'teacher' => $selectedTeacher,
            'appointments' => $this->adminAppointmentsForTeacher($selectedTeacher),
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $this->currentParentDay(),
            'canEditParentDay' => $this->canEditParentDay($this->currentParentDay()),
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

    public function adminTeacherDurationUpdate(Request $request, string $teacher): RedirectResponse
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        $parentDay = $this->currentParentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst einen Elternsprechtag auswählen.');
        }

        if (! $this->canEditParentDay($parentDay)) {
            return redirect()
                ->route('admin.teachers.show', $selectedTeacher['slug'])
                ->with('error', 'Der Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr möglich.');
        }

        $validated = $request->validate([
            'timeslot_duration' => 'required|integer|min:5|max:120',
        ]);

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);

        $this->upsertTeacherParentDaySetting(
            $teacherModel->teacher_id,
            $parentDay,
            (int) $validated['timeslot_duration'],
            false
        );

        return redirect()
            ->route('admin.teachers.show', $selectedTeacher['slug'])
            ->with('success', 'Termindauer wurde gespeichert.');
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

    public function adminTeacherActivityDelete(string $teacher): RedirectResponse
    {
        $this->ensureAdmin();
        $this->ensureTeacherDurationColumnsExist();
        $parentDay = $this->currentParentDay();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);
        abort_if(! $parentDay, 404);

        $teacherModel = Teacher::findOrFail($selectedTeacher['reference_id']);
        TeacherParentDaySetting::query()
            ->where('teacher_id', $teacherModel->teacher_id)
            ->where('parent_day_id', $parentDay->id)
            ->update([
                'duration_changed_at' => null,
                'duration_changed_by_teacher' => false,
            ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "Lehreraktivität für {$teacherModel->full_name} wurde gelöscht.");
    }

    public function adminTeacherActivitiesDeleteAll(): RedirectResponse
    {
        $this->ensureAdmin();
        $this->ensureTeacherDurationColumnsExist();
        $parentDay = $this->currentParentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Kein Elternsprechtag ausgewählt.');
        }

        TeacherParentDaySetting::query()
            ->where('parent_day_id', $parentDay->id)
            ->where('duration_changed_by_teacher', true)
            ->update([
                'duration_changed_at' => null,
                'duration_changed_by_teacher' => false,
            ]);

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Alle Lehreraktivitäten wurden gelöscht.');
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
        $this->ensureTeacherDurationColumnsExist();

        $parentDay = $this->currentParentDay();
        $settingsByTeacher = $parentDay
            ? TeacherParentDaySetting::query()
                ->where('parent_day_id', $parentDay->id)
                ->get()
                ->keyBy('teacher_id')
            : collect();

        return Teacher::query()
            ->withCount([
                'timeslots as free_slots' => fn ($query) => $query
                    ->where('is_reserved', false)
                    ->when($parentDay, fn ($sub) => $sub->where('parent_day_id', $parentDay->id)),
                'timeslots as booked_slots' => fn ($query) => $query
                    ->where('is_reserved', true)
                    ->when($parentDay, fn ($sub) => $sub->where('parent_day_id', $parentDay->id)),
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function (Teacher $teacher) use ($settingsByTeacher) {
                $setting = $settingsByTeacher->get($teacher->teacher_id);
                $duration = $setting?->timeslot_duration ?? $teacher->timeslot_duration;
                $durationChanged = (bool) ($setting?->duration_changed_by_teacher ?? false);
                $durationChangedLabel = $setting?->duration_changed_at
                    ? $setting->duration_changed_at->format('d/m/Y H:i')
                    : null;

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
                    'duration_changed' => $durationChanged,
                    'duration_changed_label' => $durationChangedLabel,
                ];
            })
            ->values();
    }

    private function resolveTeacherDuration(Teacher $teacher, ?ParentDay $parentDay): ?int
    {
        if (! $parentDay) {
            return $teacher->timeslot_duration;
        }

        $setting = TeacherParentDaySetting::query()
            ->where('teacher_id', $teacher->teacher_id)
            ->where('parent_day_id', $parentDay->id)
            ->first();

        return $setting?->timeslot_duration ?? $teacher->timeslot_duration;
    }

    private function upsertTeacherParentDaySetting(
        int $teacherId,
        ParentDay $parentDay,
        ?int $duration,
        bool $changedByTeacher
    ): TeacherParentDaySetting {
        $setting = TeacherParentDaySetting::firstOrNew([
            'teacher_id' => $teacherId,
            'parent_day_id' => $parentDay->id,
        ]);

        $setting->timeslot_duration = $duration;
        $setting->duration_changed_by_teacher = $changedByTeacher;
        $setting->duration_changed_at = $changedByTeacher ? Carbon::now() : null;
        $setting->save();

        return $setting;
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
        $parentDay = $this->currentParentDay();

        if ($student === null) {
            return collect();
        }

        $dateLabel = $this->parentDayLabel();

        return Timeslot::query()
            ->with('teacher')
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
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
        $parentDay = $this->currentParentDay();

        return Timeslot::query()
            ->with('student')
            ->where('teacher_id', $teacher['reference_id'])
            ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
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
        $parentDay = $this->currentParentDay();

        return Timeslot::query()
            ->where('teacher_id', $teacher['reference_id'])
            ->where('is_reserved', false)
            ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
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
        $parentDay = $this->currentParentDay();
        $firstSlot = Timeslot::query()
            ->where('teacher_id', $teacherId)
            ->when($parentDay, fn ($query) => $query->where('parent_day_id', $parentDay->id))
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

        // 2 Lehrer davor und 2 danach, ohne den aktuell geöffneten Lehrer.
        $start = max(0, $currentIndex - 2);
        $end = min($allTeachers->count() - 1, $currentIndex + 2);

        return $allTeachers
            ->slice($start, $end - $start + 1)
            ->reject(fn (array $teacher) => $teacher['slug'] === $currentSlug)
            ->values();
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
        $this->ensureSchoolClassesTableExists();

        $classes = SchoolClass::query()->orderBy('name')->pluck('name');

        if ($classes->isEmpty()) {
            return collect(self::DEFAULT_SCHOOL_CLASSES);
        }

        return $classes;
    }

    private function ensureSchoolClassesExist(array $classNames): void
    {
        $this->ensureSchoolClassesTableExists();

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

    private function ensureSchoolClassesTableExists(): void
    {
        if (Schema::hasTable('school_classes')) {
            return;
        }

        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique();
        });

        SchoolClass::insert(
            collect(self::DEFAULT_SCHOOL_CLASSES)
                ->map(fn (string $name) => ['name' => $name])
                ->all()
        );
    }

    private function ensureRoomsTableExists(): void
    {
        if (Schema::hasTable('rooms')) {
            return;
        }

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
        });
    }

    private function ensureTeacherDurationColumnsExist(): void
    {
        if (! Schema::hasTable('teachers')) {
            return;
        }

        if (! Schema::hasColumn('teachers', 'timeslot_duration')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->unsignedInteger('timeslot_duration')->nullable();
            });
        }

        if (! Schema::hasColumn('teachers', 'duration_changed_at')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dateTime('duration_changed_at')->nullable();
            });
        }

        if (! Schema::hasColumn('teachers', 'duration_changed_by_teacher')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->boolean('duration_changed_by_teacher')->default(false);
            });
        }
    }

    private function currentParentDay(): ?ParentDay
    {
        if (! Schema::hasTable('parent_days')) {
            return null;
        }

        ParentDay::ensureDefaultFromLegacy();

        $parentDays = $this->availableParentDaysForCurrentUser();

        if ($parentDays->isEmpty()) {
            session()->forget('parent_day_id');
            return null;
        }

        $selectedId = session('parent_day_id');
        $selected = $selectedId ? $parentDays->firstWhere('id', $selectedId) : null;

        if (! $selected) {
            $selected = $parentDays->first();
            session(['parent_day_id' => $selected->id]);
        }

        return $selected;
    }

    private function availableParentDaysForCurrentUser(): Collection
    {
        if (! Schema::hasTable('parent_days')) {
            return collect();
        }

        ParentDay::ensureDefaultFromLegacy();

        $query = ParentDay::query()->orderBy('date');
        $user = auth()->user();

        if (! $user?->isAdminUser() && ($user?->isStudentUser() || $user?->isTeacherUser())) {
            $query->whereDate('date', '>=', Carbon::today()->toDateString());
        }

        return $query->get();
    }

    private function parentDayLabel(): string
    {
        $parentDay = $this->currentParentDay();

        return $parentDay ? $parentDay->date->format('d/m/Y') : '--/--/----';
    }


    private function canEditParentDay(?ParentDay $parentDay): bool
    {
        if (! $parentDay) {
            return false;
        }

        return $parentDay->date->isToday() || $parentDay->date->isFuture();
    }

    public function adminTeacherImport(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        set_time_limit(300);

        $validated = $request->validate([
            'teacher_file' => 'required|file|mimes:xlsx,xls',
            'parent_day_ids' => 'required|array|min:1',
            'parent_day_ids.*' => 'required|integer|distinct|exists:parent_days,id',
            'timeslot_duration' => 'required|integer|min:5|max:120',
            'timeslot_start' => 'required|date_format:H:i',
            'timeslot_end' => 'required|date_format:H:i|after:timeslot_start',
            'timeslot_room' => 'required|string|max:255',
        ]);

        $selectedParentDays = ParentDay::query()
            ->whereIn('id', $validated['parent_day_ids'])
            ->orderBy('date')
            ->get();

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

        [$headerRow, $rows] = $this->locateImportHeaderRow($rows);
        $headerMap = $headerRow ? $this->mapImportHeaders($headerRow) : [];

        $colFullName = $this->resolveImportColumn($headerMap, [
            'lehrer', 'teacher', 'fullname', 'full name', 'lehrername', 'teacher_name',
        ]);
        $colFirst = $this->resolveImportColumn($headerMap, [
            'vorname', 'first_name', 'firstname', 'first name', 'given name',
        ]);
        $colLast = $this->resolveImportColumn($headerMap, [
            'nachname', 'last_name', 'lastname', 'last name', 'surname', 'family name',
        ]);
        $colKuerzel = $this->resolveImportColumn($headerMap, [
            'kuerzel', 'kürzel', 'name', 'short', 'code', 'abbreviation',
        ]);
        $colEmail = $this->resolveImportColumn($headerMap, [
            'email', 'e-mail', 'mail', 'email address', 'emailadresse',
        ]);
        $classColumns = $this->resolveImportColumns($headerMap, [
            'klassen', 'klassenliste', 'classes', 'class', 'unterricht', 'fach',
        ]);

        // Standardformat ohne erkannte Kopfzeile:
        // A Kürzel, B Nachname, C Vorname, D Klassen.
        if (! $headerRow) {
            $colKuerzel = 'A';
            $colLast = 'B';
            $colFirst = 'C';
            $classColumns = ['D'];
        }

        if ((! $colFullName && (! $colFirst || ! $colLast)) || (! $colKuerzel && ! $colEmail)) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Die Excel-Datei muss Kürzel, Nachname und Vorname oder entsprechende benannte Spalten enthalten.');
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $createdTimeslotCount = 0;
        $existingTimeslotCount = 0;

        foreach ($rows as $row) {
            $firstName = trim((string) ($colFirst ? ($row[$colFirst] ?? '') : ''));
            $lastName = trim((string) ($colLast ? ($row[$colLast] ?? '') : ''));
            $fullName = trim((string) ($colFullName ? ($row[$colFullName] ?? '') : ''));
            $kuerzel = trim((string) ($colKuerzel ? ($row[$colKuerzel] ?? '') : ''));
            $email = trim((string) ($colEmail ? ($row[$colEmail] ?? '') : ''));
            $classRaw = $this->extractClassColumnValues($row, $classColumns);

            if ($firstName === '' && $lastName === '' && $fullName === '' && $kuerzel === '' && $email === '') {
                continue;
            }

            if (($firstName === '' || $lastName === '') && $fullName !== '') {
                [$firstName, $lastName] = $this->splitName($fullName);
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

            $teacher = $kuerzel !== ''
                ? Teacher::query()
                    ->whereRaw('LOWER(kuerzel) = ?', [Str::lower($kuerzel)])
                    ->first()
                : null;

            if (! $teacher) {
                $teacherId = User::query()->where('email', $email)->value('teacher_id');
                $teacher = $teacherId ? Teacher::find($teacherId) : null;
            }

            $teacherWasCreated = $teacher === null;

            if ($teacherWasCreated) {
                $teacher = Teacher::create([
                    'teacher_id' => $this->nextTeacherId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $kuerzel !== ''
                        ? Str::upper($kuerzel)
                        : $this->shortCode("{$firstName} {$lastName}"),
                    'classes' => $normalizedClasses,
                ]);
            } else {
                $teacher->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $kuerzel !== '' ? Str::upper($kuerzel) : $teacher->kuerzel,
                    'classes' => $normalizedClasses,
                ]);
            }

            $userForTeacher = User::query()->where('teacher_id', $teacher->teacher_id)->first();
            $userForEmail = User::query()->where('email', $email)->first();

            if ($userForTeacher && $userForEmail && ! $userForTeacher->is($userForEmail)) {
                $skippedCount++;
                continue;
            }

            $user = $userForTeacher ?? $userForEmail ?? User::make(['email' => $email]);
            $alreadyExisted = $user->exists;

            if (! $alreadyExisted || blank($user->password)) {
                $user->password = Hash::make(Str::random(32));
            }

            $user->name = trim("{$firstName} {$lastName}");
            $user->email = $email;
            $user->is_teacher = true;
            $user->teacher_id = $teacher->teacher_id;
            $user->save();

            foreach ($selectedParentDays as $parentDay) {
                $this->upsertTeacherParentDaySetting(
                    $teacher->teacher_id,
                    $parentDay,
                    (int) $validated['timeslot_duration'],
                    false
                );

                $timeslotResult = $this->createGeneratedTimeslotsForTeacher(
                    $teacher->teacher_id,
                    $parentDay,
                    $validated['timeslot_start'],
                    $validated['timeslot_end'],
                    (int) $validated['timeslot_duration'],
                    $validated['timeslot_room']
                );

                $createdTimeslotCount += $timeslotResult['created'];
                $existingTimeslotCount += $timeslotResult['skipped'];
            }

            if ($alreadyExisted) {
                $updatedCount++;
            } else {
                $createdCount++;
            }
        }

        return redirect()
            ->route('admin.dashboard')
            ->with(
                'success',
                "Import abgeschlossen. Lehrer neu: {$createdCount}, aktualisiert: {$updatedCount}, "
                ."übersprungen: {$skippedCount}. Termine neu: {$createdTimeslotCount}, "
                ."bereits vorhanden: {$existingTimeslotCount}."
            );
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
            if ($key === '') {
                continue;
            }

            if (isset($headerMap[$key])) {
                return $headerMap[$key];
            }
        }

        foreach ($aliases as $alias) {
            $key = Str::of($alias)->lower()->trim()->value();
            if ($key === '') {
                continue;
            }

            foreach ($headerMap as $label => $column) {
                if (Str::contains($label, $key)) {
                    return $column;
                }
            }
        }

        return null;
    }

    private function resolveImportColumns(array $headerMap, array $aliases): array
    {
        $columns = [];

        foreach ($headerMap as $label => $column) {
            foreach ($aliases as $alias) {
                $key = Str::of($alias)->lower()->trim()->value();

                if ($key !== '' && ($label === $key || Str::contains($label, $key))) {
                    $columns[] = $column;
                    break;
                }
            }
        }

        return array_values(array_unique($columns));
    }

    private function locateImportHeaderRow(array $rows): array
    {
        $headerAliases = [
            'vorname', 'nachname', 'first_name', 'last_name', 'firstname', 'lastname',
            'lehrer', 'teacher', 'name', 'fullname', 'lehrername', 'teacher_name',
            'kuerzel', 'kürzel', 'email', 'e-mail', 'mail', 'klassen', 'classes', 'class',
        ];

        foreach (array_slice($rows, 0, 10, true) as $rowNumber => $row) {
            $headerMap = $this->mapImportHeaders($row);
            $knownHeaders = collect($headerAliases)
                ->map(fn (string $alias) => Str::of($alias)->lower()->trim()->value())
                ->filter(fn (string $alias) => isset($headerMap[$alias]));

            if ($knownHeaders->count() >= 2) {
                return [$row, array_filter(
                    $rows,
                    fn ($key) => $key > $rowNumber,
                    ARRAY_FILTER_USE_KEY
                )];
            }
        }

        return [null, $rows];
    }

    private function extractClassColumnValues(array $row, array $columns): string
    {
        return collect($columns)
            ->map(fn (string $column) => trim((string) ($row[$column] ?? '')))
            ->filter()
            ->implode(' ');
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
        ParentDay $parentDay,
        string $startTime,
        string $endTime,
        int $durationInMinutes,
        string $room
    ): void {
        $current = $parentDay->date->copy()->setTimeFromTimeString($startTime);
        $end = $parentDay->date->copy()->setTimeFromTimeString($endTime);
        $timeslots = [];
        $nextId = $this->nextTimeslotId();

        while ($current->copy()->addMinutes($durationInMinutes)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($durationInMinutes);

            $timeslots[] = array_merge([
                'id' => $nextId++,
                'teacher_id' => $teacherId,
                'parent_day_id' => $parentDay->id,
                'student_id' => null,
                'starts_at' => $current->toDateTimeString(),
                'ends_at' => $slotEnd->toDateTimeString(),
                'room' => $room,
                'is_reserved' => false,
            ], $this->timeslotDayPayload($parentDay));

            $current = $slotEnd;
        }

        if ($timeslots !== []) {
            Timeslot::insert($timeslots);
        }
    }

    private function createGeneratedTimeslotsForTeacher(
        int $teacherId,
        ParentDay $parentDay,
        string $startTime,
        string $endTime,
        int $slotLengthInMinutes,
        string $room
    ): array {
        $start = $parentDay->date->copy()->setTimeFromTimeString($startTime);
        $end = $parentDay->date->copy()->setTimeFromTimeString($endTime);
        $current = $start->copy();
        $candidates = [];

        while ($current->copy()->addMinutes($slotLengthInMinutes)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($slotLengthInMinutes);
            $candidates[] = [
                'starts_at' => $current->copy(),
                'ends_at' => $slotEnd,
            ];

            $current = $slotEnd;
        }

        if ($candidates === []) {
            return ['created' => 0, 'skipped' => 0];
        }

        $existingStarts = Timeslot::query()
            ->where('teacher_id', $teacherId)
            ->where('parent_day_id', $parentDay->id)
            ->whereIn('starts_at', collect($candidates)->map(fn (array $slot) => $slot['starts_at']->toDateTimeString())->all())
            ->pluck('starts_at')
            ->map(fn ($value) => Carbon::parse($value)->toDateTimeString())
            ->all();

        $existingStarts = array_flip($existingStarts);
        $timeslots = [];
        $nextId = $this->nextTimeslotId();
        $skipped = 0;

        foreach ($candidates as $slot) {
            $startsAt = $slot['starts_at']->toDateTimeString();

            if (isset($existingStarts[$startsAt])) {
                $skipped++;
                continue;
            }

            $timeslots[] = array_merge([
                'id' => $nextId++,
                'teacher_id' => $teacherId,
                'parent_day_id' => $parentDay->id,
                'student_id' => null,
                'starts_at' => $startsAt,
                'ends_at' => $slot['ends_at']->toDateTimeString(),
                'room' => $room,
                'is_reserved' => false,
            ], $this->timeslotDayPayload($parentDay));
        }

        if ($timeslots !== []) {
            Timeslot::insert($timeslots);
        }

        return [
            'created' => count($timeslots),
            'skipped' => $skipped,
        ];
    }

    private function hasEnoughTimeForSlot(string $startTime, string $endTime, int $slotLengthInMinutes): bool
    {
        $start = Carbon::createFromFormat('H:i', $startTime);
        $end = Carbon::createFromFormat('H:i', $endTime);

        return $start->copy()->addMinutes($slotLengthInMinutes)->lte($end);
    }

    private function teacherSlug(Teacher $teacher): string
    {
        return Str::slug($teacher->full_name.'-'.$teacher->teacher_id);
    }

    private function timeslotDayPayload(ParentDay $parentDay): array
    {
        if (! Schema::hasColumn('timeslots', 'day')) {
            return [];
        }

        return ['day' => $parentDay->date->toDateString()];
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
        $this->ensureRoomsTableExists();

        $teacherId = $request->input('teacher');
        $teacher = Teacher::find($teacherId);
        $parentDay = $this->currentParentDay();

        abort_if(!$teacher, 404);

        return view('admin.timeslot-form', [
            'teacher' => $teacher,
            'teacherSlug' => $this->teacherSlug($teacher),
            'preselectedTeacherId' => $teacher->teacher_id,
            'teachers' => Teacher::all(),
            'students' => Student::all(),
            'rooms' => Room::query()->orderBy('name')->get(),
            'parentDayLabel' => $this->parentDayLabel(),
            'parentDayValue' => $parentDay?->date->format('Y-m-d') ?? '',
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $parentDay,
            'suggestedTimeslotDuration' => $this->resolveTeacherDuration($teacher, $parentDay) ?? 10,
            'isEdit' => false,
            'canEditParentDay' => $this->canEditParentDay($parentDay),
        ]);
    }

    public function adminTimeslotStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_id' => 'required|integer|exists:teachers,teacher_id',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'nullable|required_without:duration_minutes|date_format:H:i|after:starts_at',
            'duration_minutes' => 'nullable|required_without:ends_at|integer|min:5|max:600',
            'slot_length_minutes' => 'required|integer|min:5|max:120',
            'room' => 'required|string|max:255',
        ]);

        $parentDay = $this->currentParentDay();
        $teacher = Teacher::findOrFail($validated['teacher_id']);

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst einen Elternsprechtag anlegen.');
        }

        if (! $this->canEditParentDay($parentDay)) {
            return redirect()
                ->route('admin.teachers.appointments', $this->teacherSlug($teacher))
                ->with('error', 'Der Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr möglich.');
        }

        $endsAt = $validated['ends_at'] ?? Carbon::createFromFormat('H:i', $validated['starts_at'])
            ->addMinutes((int) $validated['duration_minutes'])
            ->format('H:i');

        if (! $this->hasEnoughTimeForSlot($validated['starts_at'], $endsAt, (int) $validated['slot_length_minutes'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Die Terminlaenge passt nicht in das gewaehlte Zeitfenster.');
        }

        $this->upsertTeacherParentDaySetting(
            $teacher->teacher_id,
            $parentDay,
            (int) $validated['slot_length_minutes'],
            false
        );

        $result = $this->createGeneratedTimeslotsForTeacher(
            $teacher->teacher_id,
            $parentDay,
            $validated['starts_at'],
            $endsAt,
            (int) $validated['slot_length_minutes'],
            $validated['room']
        );

        $message = "{$result['created']} Termine wurden generiert.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} bestehende Termine wurden übersprungen.";
        }

        return redirect()
            ->route('admin.teachers.appointments', $this->teacherSlug($teacher))
            ->with('success', $message);
    }

    public function adminTimeslotsGenerate(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,teacher_id',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'slot_length_minutes' => 'required|integer|min:5|max:120',
            'room' => 'required|string|max:255',
        ]);

        $parentDay = $this->currentParentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst einen Elternsprechtag anlegen.');
        }

        if (! $this->canEditParentDay($parentDay)) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Der Elternsprechtag ist bereits vorbei. Bearbeiten ist nicht mehr moeglich.');
        }

        if (! $this->hasEnoughTimeForSlot($validated['starts_at'], $validated['ends_at'], (int) $validated['slot_length_minutes'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Die Terminlaenge passt nicht in das gewaehlte Zeitfenster.');
        }

        $teacherIds = collect($validated['teacher_ids'])
            ->map(fn ($teacherId) => (int) $teacherId)
            ->unique()
            ->values();
        $teachers = Teacher::query()
            ->whereIn('teacher_id', $teacherIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($teachers as $teacher) {
            $this->upsertTeacherParentDaySetting(
                $teacher->teacher_id,
                $parentDay,
                (int) $validated['slot_length_minutes'],
                false
            );

            $result = $this->createGeneratedTimeslotsForTeacher(
                $teacher->teacher_id,
                $parentDay,
                $validated['starts_at'],
                $validated['ends_at'],
                (int) $validated['slot_length_minutes'],
                $validated['room']
            );

            $created += $result['created'];
            $skipped += $result['skipped'];
        }

        $message = "{$created} Termine fuer {$teachers->count()} Lehrer wurden generiert.";
        if ($skipped > 0) {
            $message .= " {$skipped} bestehende Termine wurden uebersprungen.";
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', $message);
    }

    public function adminTimeslotEdit(Timeslot $timeslot)
    {
        $this->ensureAdmin();
        $this->ensureRoomsTableExists();
        $parentDay = $timeslot->parentDay ?? $this->currentParentDay();
        $canEditParentDay = $this->canEditParentDay($parentDay);

        return view('admin.timeslot-form', [
            'timeslot' => $timeslot,
            'teacher' => $timeslot->teacher,
            'teacherSlug' => $this->teacherSlug($timeslot->teacher),
            'teachers' => Teacher::all(),
            'students' => Student::all(),
            'rooms' => Room::query()->orderBy('name')->get(),
            'parentDayLabel' => $parentDay?->date->format('d/m/Y') ?? '--/--/----',
            'parentDayValue' => $parentDay?->date->format('Y-m-d') ?? '',
            'parentDays' => $this->availableParentDaysForCurrentUser(),
            'activeParentDay' => $parentDay,
            'suggestedTimeslotDuration' => $this->resolveTeacherDuration($timeslot->teacher, $parentDay) ?? 10,
            'isEdit' => true,
            'canEditParentDay' => $canEditParentDay,
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

        $parentDay = $timeslot->parentDay ?? $this->currentParentDay();

        if (! $parentDay) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Bitte zuerst einen Elternsprechtag anlegen.');
        }

        $startTime = $parentDay->date->copy()->setTimeFromTimeString($validated['starts_at']);
        $endTime = $parentDay->date->copy()->setTimeFromTimeString($validated['ends_at']);

        $timeslot->update(array_merge([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'room' => $validated['room'],
            'is_reserved' => $validated['is_reserved'] ?? false,
            'parent_day_id' => $parentDay->id,
        ], $this->timeslotDayPayload($parentDay)));

        return redirect()->route('admin.teachers.show', $this->teacherSlug($timeslot->teacher))
            ->with('success', 'Termin wurde aktualisiert.');
    }

    public function adminTimeslotDestroy(Timeslot $timeslot)
    {
        $this->ensureAdmin();

        $teacherSlug = $this->teacherSlug($timeslot->teacher);
        $timeslot->delete();

        return redirect()->route('admin.teachers.show', $teacherSlug)
            ->with('success', 'Termin wurde gelöscht.');
    }
}
