<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WebUntisTeacherSyncService
{
    public function __construct(
        private readonly WebUntisClient $client
    ) {
    }

    public function sync(): array
    {
        if (! config('webuntis.enabled')) {
            throw new RuntimeException('WebUntis sync is disabled. Set WEBUNTIS_ENABLED=true to use it.');
        }

        $teachersPayload = $this->client->getTeachers();
        $classesPayload = $this->client->getClasses();
        $enrollmentsPayload = $this->client->getEnrollments();

        $teacherRows = $this->extractTeacherRows($teachersPayload);
        $classMap = $this->extractClassMap($classesPayload);
        $teacherClasses = $this->extractTeacherClasses($enrollmentsPayload, $classMap);

        $synced = 0;

        DB::transaction(function () use ($teacherRows, $teacherClasses, &$synced) {
            foreach ($teacherRows as $teacherRow) {
                $teacherId = $teacherRow['teacher_id'];
                $classes = $teacherClasses[$teacherId]
                    ?? $teacherClasses[$teacherRow['lookup_key']]
                    ?? $teacherClasses[$teacherRow['short_lookup_key']]
                    ?? [];

                Teacher::updateOrCreate(
                    ['teacher_id' => $teacherId],
                    [
                        'first_name' => $teacherRow['first_name'],
                        'last_name' => $teacherRow['last_name'],
                        'kuerzel' => $teacherRow['kuerzel'],
                        'classes' => array_values(array_unique($classes)),
                    ]
                );

                $synced++;
            }
        });

        return [
            'teachers' => $synced,
            'class_assignments' => collect($teacherClasses)->sum(fn (array $classes) => count($classes)),
        ];
    }

    private function extractTeacherRows(array $payload): array
    {
        return $this->extractItems($payload, ['teachers', 'teacher', 'data', 'results'])
            ->map(function (array $teacher, int $index) {
                $teacherId = $this->firstFilled($teacher, [
                    'id',
                    'teacherId',
                    'teacher_id',
                    'personId',
                    'person_id',
                    'userId',
                    'user_id',
                ]);

                if ($teacherId === null) {
                    throw new RuntimeException(sprintf('WebUntis teacher record at index %d has no usable ID.', $index));
                }

                $firstName = trim((string) ($this->firstFilled($teacher, ['foreName', 'firstName', 'first_name', 'givenName']) ?? ''));
                $lastName = trim((string) ($this->firstFilled($teacher, ['longName', 'lastName', 'last_name', 'surname', 'familyName']) ?? ''));
                $displayName = trim((string) ($this->firstFilled($teacher, ['displayName', 'name']) ?? ''));

                if ($firstName === '' && $lastName === '' && $displayName !== '') {
                    [$firstName, $lastName] = $this->splitName($displayName);
                }

                if ($firstName === '' && $lastName === '') {
                    throw new RuntimeException(sprintf('WebUntis teacher record [%s] has no usable name.', $teacherId));
                }

                $kuerzel = strtoupper(trim((string) ($this->firstFilled($teacher, [
                    'shortName',
                    'short_name',
                    'name',
                    'code',
                ]) ?? '')));

                $lookupKey = $this->normalizeLookupKey((string) $teacherId);
                $shortLookupKey = $this->normalizeLookupKey($kuerzel);

                return [
                    'teacher_id' => (int) $teacherId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'kuerzel' => $kuerzel !== '' ? $kuerzel : null,
                    'lookup_key' => $lookupKey,
                    'short_lookup_key' => $shortLookupKey,
                ];
            })
            ->all();
    }

    private function extractClassMap(array $payload): array
    {
        return $this->extractItems($payload, ['classes', 'class', 'data', 'results'])
            ->mapWithKeys(function (array $classRow) {
                $classId = $this->firstFilled($classRow, ['sourcedId', 'id', 'classId']);

                if ($classId === null) {
                    return [];
                }

                $metadata = is_array($classRow['metadata'] ?? null) ? $classRow['metadata'] : [];
                $webUntisName = $this->firstFilled($metadata, [
                    'webuntisClassName',
                    'webuntis-class-name',
                    'name',
                    'className',
                ]);

                $title = $this->firstFilled($classRow, ['title', 'name']);
                $className = strtoupper(trim((string) ($webUntisName ?? $title ?? '')));

                return $className !== ''
                    ? [(string) $classId => preg_replace('/\s+/', '', $className)]
                    : [];
            })
            ->all();
    }

    private function extractTeacherClasses(array $payload, array $classMap): array
    {
        $assignments = [];

        foreach ($this->extractItems($payload, ['enrollments', 'data', 'results']) as $enrollment) {
            $role = strtolower((string) ($this->firstFilled($enrollment, ['role', 'primaryRole']) ?? ''));

            if ($role !== 'teacher') {
                continue;
            }

            $classId = (string) ($this->firstFilled($enrollment, ['class.sourcedId', 'classId', 'class_id']) ?? '');
            $className = $classMap[$classId] ?? null;

            if (! $className) {
                continue;
            }

            $userIds = is_array($enrollment['userIds'] ?? null) ? $enrollment['userIds'] : [];
            $lookupKeys = array_filter([
                $this->normalizeLookupKey((string) ($this->firstFilled($userIds, ['person-id', 'person_id']) ?? '')),
                $this->normalizeLookupKey((string) ($this->firstFilled($userIds, ['id', 'user-id', 'user_id']) ?? '')),
                $this->normalizeLookupKey((string) ($this->firstFilled($userIds, ['short-name', 'short_name']) ?? '')),
                $this->normalizeLookupKey((string) ($this->firstFilled($enrollment, ['user.sourcedId', 'userId', 'user_id']) ?? '')),
            ]);

            foreach ($lookupKeys as $lookupKey) {
                $assignments[$lookupKey] ??= [];
                $assignments[$lookupKey][] = $className;
            }
        }

        return collect($assignments)
            ->map(fn (array $classes) => array_values(array_unique($classes)))
            ->all();
    }

    private function extractItems(array $payload, array $keys): Collection
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if (is_array($value) && array_is_list($value)) {
                return collect($value)->filter(fn ($item) => is_array($item))->values();
            }
        }

        if (array_is_list($payload)) {
            return collect($payload)->filter(fn ($item) => is_array($item))->values();
        }

        return collect();
    }

    private function firstFilled(array $source, array $keys): mixed
    {
        foreach ($keys as $key) {
            $value = data_get($source, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function splitName(string $value): array
    {
        $parts = collect(preg_split('/\s+/', trim($value)))->filter()->values();
        $firstName = (string) ($parts->shift() ?? 'Unbekannt');
        $lastName = trim($parts->implode(' '));

        return [$firstName, $lastName !== '' ? $lastName : 'Unbekannt'];
    }

    private function normalizeLookupKey(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->value();
    }
}
