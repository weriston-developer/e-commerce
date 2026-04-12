<?php

namespace Database\Seeders;

use App\Domain\Enums\UserRole;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar categorias
        $this->call(CategorySeeder::class);

        // Criar usuário ADMIN
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Administrador',
            'email' => 'admin@ecommerce.com',
            'password' => Hash::make('admin123'),
            'role' => UserRole::ADMIN,
        ]);

        // Criar usuário comum de teste
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Usuário Teste',
            'email' => 'user@ecommerce.com',
            'password' => Hash::make('user123'),
            'role' => UserRole::USER,
        ]);

        // Opcional: criar mais usuários para testes
        // User::factory(10)->create();
    }
}
