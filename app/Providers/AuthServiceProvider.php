<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
  /**
   * The model to policy mappings for the application.
   *
   * @var array<class-string, class-string>
   */
  protected $policies = [
    // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    \App\Models\EmailApplication::class => \App\Policies\EmailApplicationPolicy::class,
    \App\Models\LoanApplication::class => \App\Policies\LoanApplicationPolicy::class,
  ];

  /**
   * Register any authentication / authorization services.
   */
  public function boot(): void
  {
    $this->registerPolicies();

    // Implicitly grant "Admin" role all permission checks using can()
    Gate::before(function ($user, $ability) {
      if ($user->hasRole('Admin')) {
        return true;
      }
    });
  }
}
