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

        // Criar usuário CUSTOMER de teste
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Cliente Teste',
            'email' => 'customer@ecommerce.com',
            'password' => Hash::make('customer123'),
            'role' => UserRole::CUSTOMER,
        ]);

        // Opcional: criar mais usuários para testes
        // User::factory(10)->create();
    }
}
