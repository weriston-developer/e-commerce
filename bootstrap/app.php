<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Removido: web routes (aplicação API-only)
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API Stateless - usando Sanctum
        $middleware->statefulApi();
        
        // Para API, retornar null (não redirecionar para login)
        $middleware->redirectGuestsTo(fn () => null);
        
        // Força todas as respostas como JSON (API-only)
        $middleware->append(\App\Http\Middleware\ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tratamento de erro de autenticação para retornar JSON
        $exceptions->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        });

        // Tratamento de erro de autorização (403 Forbidden) para retornar JSON
        $exceptions->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para acessar este recurso',
            ], 403);
        });
    })->create();
