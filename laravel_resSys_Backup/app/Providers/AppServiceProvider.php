<?php

namespace App\Providers;

use App\Models\ParentDay;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Azure\AzureExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(SocialiteWasCalled::class, AzureExtendSocialite::class);

        View::composer('layouts.portal', function ($view) {
            $viewData = $view->getData();

            if (
                array_key_exists('parentDays', $viewData)
                && array_key_exists('activeParentDay', $viewData)
            ) {
                return;
            }

            if (! Schema::hasTable('parent_days')) {
                $view->with([
                    'parentDays' => collect(),
                    'activeParentDay' => null,
                ]);

                return;
            }

            ParentDay::ensureDefaultFromLegacy();

            $parentDaysQuery = ParentDay::query()->orderBy('date');
            $user = auth()->user();

            if (! $user?->isAdminUser() && ($user?->isStudentUser() || $user?->isTeacherUser())) {
                $parentDaysQuery->whereDate('date', '>=', now()->toDateString());
            }

            if ($user?->isTeacherUser()) {
                $teacherId = $user->teacher_id;

                if ($teacherId === null && Schema::hasTable('teachers')) {
                    $normalizedUserName = $this->normalizeName($user->name);
                    $teacherId = Teacher::query()
                        ->get()
                        ->first(
                            fn (Teacher $teacher) => $this->normalizeName($teacher->full_name) === $normalizedUserName
                        )
                        ?->teacher_id;
                }

                if ($teacherId === null) {
                    $parentDaysQuery->whereRaw('1 = 0');
                } else {
                    $parentDaysQuery->whereHas(
                        'timeslots',
                        fn ($timeslotQuery) => $timeslotQuery->where('teacher_id', $teacherId)
                    );
                }
            }

            if ($user?->isStudentUser()) {
                $studentClass = $this->studentClass($user->klasse);
                $bookableTeacherIds = Schema::hasTable('teachers')
                    ? Teacher::query()
                        ->get()
                        ->filter(function (Teacher $teacher) use ($studentClass) {
                            $classes = collect($teacher->classes ?? [])
                                ->map(fn ($className) => $this->studentClass($className))
                                ->filter()
                                ->values()
                                ->all();

                            return $studentClass === null
                                || $classes === []
                                || in_array($studentClass, $classes, true);
                        })
                        ->pluck('teacher_id')
                    : new Collection();

                $parentDaysQuery->where(function ($query) use ($bookableTeacherIds, $user) {
                    $query->whereHas(
                        'timeslots',
                        fn ($timeslotQuery) => $timeslotQuery
                            ->where('is_reserved', false)
                            ->whereIn('teacher_id', $bookableTeacherIds)
                    )->orWhereHas(
                        'timeslots',
                        fn ($timeslotQuery) => $timeslotQuery
                            ->where('is_reserved', true)
                            ->where('student_id', $user->id)
                    );
                });
            }

            $parentDays = $parentDaysQuery->get();
            $selectedId = session('parent_day_id');

            if ($selectedId && ! $parentDays->contains('id', $selectedId)) {
                $selectedId = null;
            }

            if (! $selectedId && $parentDays->isNotEmpty()) {
                $selectedId = $parentDays->first()->id;
                session(['parent_day_id' => $selectedId]);
            }

            $activeParentDay = $selectedId
                ? $parentDays->firstWhere('id', $selectedId)
                : null;

            $view->with([
                'parentDays' => $parentDays,
                'activeParentDay' => $activeParentDay,
            ]);
        });
    }

    private function normalizeName(?string $name): string
    {
        return Str::of((string) $name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    private function studentClass(mixed $className): ?string
    {
        $normalized = preg_replace('/\s+/', '', Str::upper(trim((string) $className)));

        return preg_match('/^\d[A-Z0-9]{3,7}$/', $normalized) === 1
            ? $normalized
            : null;
    }
}
