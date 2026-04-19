<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\Timeslot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PortalController extends Controller
{
    public function studentDashboard()
    {
        $this->ensureStudent();

        $teachers = $this->availableTeachersForCurrentStudent();
        $bookings = $this->currentStudentBookings();

        $summary = [
            'count' => $bookings->count(),
            'teacher_names' => $bookings->pluck('teacher_name')->unique()->implode(' / '),
            'teacher_count' => $teachers->count(),
            'free_slot_count' => $teachers->sum('free_slots'),
        ];

        return view('student.dashboard', [
            'summary' => $summary,
            'highlights' => $teachers->take(6),
            'currentClass' => $this->currentStudentClass(),
        ]);
    }

    public function studentTeachers()
    {
        $this->ensureStudent();

        return view('student.teachers', [
            'teachers' => $this->availableTeachersForCurrentStudent(),
            'currentClass' => $this->currentStudentClass(),
        ]);
    }

    public function studentTeacherShow(string $teacher)
    {
        $this->ensureStudent();

        $teachers = $this->availableTeachersForCurrentStudent();
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
                ->with('error', 'Bitte waehle einen Timeslot aus.');
        }

        $timeslot = Timeslot::find($timeslotId);

        if (! $timeslot) {
            return redirect()
                ->back()
                ->with('error', 'Timeslot nicht gefunden.');
        }

        return $this->processBooking($timeslot);
    }

    private function processBooking(Timeslot $timeslot): RedirectResponse
    {
        if ($timeslot->is_reserved) {
            return redirect()
                ->back()
                ->with('error', 'Dieser Timeslot wurde gerade schon gebucht.');
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
                ->with('error', 'Du kannst bei einem Lehrer nur einen Timeslot buchen.');
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
            ->with('success', 'Timeslot erfolgreich gebucht.');
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
            ->with('success', 'Timeslot erfolgreich storniert.');
    }

    public function teacherDashboard()
    {
        $this->ensureTeacher();

        $teacher = $this->currentTeacher();
        $appointments = collect();

        if ($teacher !== null) {
            $appointments = Timeslot::query()
                ->with('student')
                ->where('teacher_id', $teacher->teacher_id)
                ->orderBy('day')
                ->orderBy('starts_at')
                ->get()
                ->map(function (Timeslot $timeslot) {
                    return [
                        'id' => $timeslot->id,
                        'student_name' => $timeslot->student?->full_name ?? 'Freier Slot',
                        'class_name' => $timeslot->student?->class_name ?? 'Offen',
                        'room' => $timeslot->room,
                        'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                        'date_label' => optional($timeslot->day)->format('d.m.Y') ?? '--.--.----',
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

    public function adminDashboard()
    {
        $this->ensureAdmin();

        $teachers = $this->allTeacherProfiles();

        $stats = [
            'students' => Student::count(),
            'teachers' => $teachers->count(),
            'free_slots' => Timeslot::query()->where('is_reserved', false)->count(),
            'booked_slots' => Timeslot::query()->where('is_reserved', true)->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'teachers' => $teachers,
        ]);
    }

    public function adminTeacherShow(string $teacher)
    {
        $this->ensureAdmin();

        $selectedTeacher = $this->allTeacherProfiles()->firstWhere('slug', $teacher);

        abort_if(! $selectedTeacher, 404);

        return view('admin.teacher-show', [
            'teacher' => $selectedTeacher,
            'appointments' => $this->adminAppointmentsForTeacher($selectedTeacher),
            'filters' => [
                'classes' => ['Alle Klassen', '1AHIT', '2AHIT', '3AHIT', '4AHIT', '5AHIT', '3AHMBA', '4AHMBA'],
                'rooms' => ['Alle Raeume', 'B201', 'B203', 'A104', 'Lab 2', '3AHMBA', '4AHIT'],
                'times' => ['17:00', '17:10', '17:20', '17:30', '17:40', '17:50'],
            ],
        ]);
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
            ->with('success', 'Timeslot wurde freigegeben.');
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
                return [
                    'slug' => Str::slug($teacher->full_name.'-'.$teacher->teacher_id),
                    'name' => $teacher->full_name,
                    'short' => $teacher->kuerzel ?: $this->shortCode($teacher->full_name),
                    'classes' => $teacher->classes ?? [],
                    'display_classes' => $teacher->classes ? implode(' / ', $teacher->classes) : 'Alle Klassen',
                    'free_slots' => (int) $teacher->free_slots,
                    'booked_slots' => (int) $teacher->booked_slots,
                    'reference_id' => $teacher->teacher_id,
                ];
            })
            ->values();
    }

    private function availableTeachersForCurrentStudent(): Collection
    {
        $studentClass = $this->currentStudentClass();

        return $this->allTeacherProfiles()
            ->filter(function (array $teacher) use ($studentClass) {
                if ($teacher['free_slots'] < 1) {
                    return false;
                }

                if ($studentClass === null) {
                    return true;
                }

                return $teacher['classes'] === []
                    || in_array($studentClass, $teacher['classes'], true);
            })
            ->values();
    }

    private function currentStudentBookings(): Collection
    {
        $student = $this->studentRecordOrNull();

        if ($student === null) {
            return collect();
        }

        return Timeslot::query()
            ->with('teacher')
            ->where('student_id', $student->student_id)
            ->where('is_reserved', true)
            ->orderBy('day')
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) {
                $teacherName = $timeslot->teacher?->full_name ?? 'Lehrer';

                return [
                    'id' => $timeslot->id,
                    'teacher_name' => $teacherName,
                    'teacher_short' => $timeslot->teacher?->kuerzel ?: $this->shortCode($teacherName),
                    'class_name' => $this->currentStudentClass() ?? $this->studentRecordOrNull()?->class_name ?? 'Unbekannt',
                    'room' => $timeslot->room,
                    'date_label' => optional($timeslot->day)->format('d.m.Y') ?? '--.--.----',
                    'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                ];
            })
            ->values();
    }

    private function adminAppointmentsForTeacher(array $teacher): Collection
    {
        return Timeslot::query()
            ->with('student')
            ->where('teacher_id', $teacher['reference_id'])
            ->orderBy('day')
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) {
                return [
                    'id' => $timeslot->id,
                    'student_name' => $timeslot->student?->full_name ?? 'Freier Slot',
                    'class_name' => $timeslot->student?->class_name ?? 'Offen',
                    'room' => $timeslot->room,
                    'time_label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                    'date_label' => optional($timeslot->day)->format('d.m.Y') ?? '--.--.----',
                    'is_reserved' => (bool) $timeslot->is_reserved,
                ];
            })
            ->values();
    }

    private function freeSlotsForTeacher(array $teacher): Collection
    {
        return Timeslot::query()
            ->where('teacher_id', $teacher['reference_id'])
            ->where('is_reserved', false)
            ->orderBy('day')
            ->orderBy('starts_at')
            ->get()
            ->map(function (Timeslot $timeslot) {
                return [
                    'id' => $timeslot->id,
                    'label' => optional($timeslot->starts_at)->format('H:i') ?? '--:--',
                    'room' => $timeslot->room,
                    'date_label' => optional($timeslot->day)->format('d.m.Y') ?? '--.--.----',
                ];
            })
            ->values();
    }

    private function getTeacherRoom(int $teacherId): string
    {
        $firstSlot = Timeslot::query()
            ->where('teacher_id', $teacherId)
            ->first();

        return $firstSlot?->room ?? 'TBD';
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
            'teachers' => Teacher::all(),
            'students' => Student::all(),
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
            'day' => 'required|date',
        ]);

        // Gesamtes Datum erstellen
        $startTime = \Carbon\Carbon::parse($validated['day'] . ' ' . $validated['starts_at']);
        $endTime = \Carbon\Carbon::parse($validated['day'] . ' ' . $validated['ends_at']);

        Timeslot::create([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'room' => $validated['room'],
            'is_reserved' => $validated['is_reserved'] ?? false,
            'day' => $validated['day'],
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
            'day' => 'required|date',
        ]);

        // Gesamtes Datum aktualisieren
        $startTime = \Carbon\Carbon::parse($validated['day'] . ' ' . $validated['starts_at']);
        $endTime = \Carbon\Carbon::parse($validated['day'] . ' ' . $validated['ends_at']);

        $timeslot->update([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'starts_at' => $startTime,
            'ends_at' => $endTime,
            'room' => $validated['room'],
            'is_reserved' => $validated['is_reserved'] ?? false,
            'day' => $validated['day'],
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
