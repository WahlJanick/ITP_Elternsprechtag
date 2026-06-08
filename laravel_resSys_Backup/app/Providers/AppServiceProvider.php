<?php

namespace App\Providers;

use App\Models\ParentDay;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
}
