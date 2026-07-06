<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\IncidentPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('administrator') ? true : null;
        });

        Gate::policy(Incident::class, IncidentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
    }
}
