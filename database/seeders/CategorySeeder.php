<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos',
                'description' => 'Produtos eletrônicos e tecnologia',
                'is_active' => true,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Roupas',
                'slug' => 'roupas',
                'description' => 'Vestuário e acessórios',
                'is_active' => true,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Livros',
                'slug' => 'livros',
                'description' => 'Livros e publicações',
                'is_active' => true,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Casa e Decoração',
                'slug' => 'casa-decoracao',
                'description' => 'Artigos para casa e decoração',
                'is_active' => true,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Esportes',
                'slug' => 'esportes',
                'description' => 'Artigos esportivos e fitness',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
