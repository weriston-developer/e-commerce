<?php

namespace App\Providers;

use App\Domain\Enums\UserRole;
use App\Infrastructure\Persistence\Models\User;
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
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gate para verificar se é admin
        Gate::define('admin', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        // Gate para deletar usuários (somente admin)
        Gate::define('delete-users', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        // Gate para gerenciar produtos (somente admin)
        Gate::define('manage-products', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });

        // Gate para gerenciar categorias (somente admin)
        Gate::define('manage-categories', function (User $user) {
            return $user->role === UserRole::ADMIN;
        });
    }
}
