<?php

namespace App\Providers;

use App\Models\ParentDay;
use App\Models\Teacher;
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
                    $parentDaysQuery->where(function ($query) use ($teacherId) {
                        $query->whereHas(
                            'timeslots',
                            fn ($timeslotQuery) => $timeslotQuery->where('teacher_id', $teacherId)
                        );

                        if (Schema::hasTable('teacher_parent_day_settings')) {
                            $query->orWhereHas(
                                'teacherSettings',
                                fn ($settingQuery) => $settingQuery->where('teacher_id', $teacherId)
                            );
                        }
                    });
                }
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
}
