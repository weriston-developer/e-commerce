<?php

namespace App\Providers;

use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\Repositories\CategoryRepository;
use App\Infrastructure\Persistence\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * Aqui fazemos o binding das interfaces com suas implementações
     * (Inversão de Dependência - SOLID)
     */
    public function register(): void
    {
        // ========================================
        // REPOSITORIES BINDINGS
        // ========================================
        
        // User Repository
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        // Product Repository
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );

        // Category Repository
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
