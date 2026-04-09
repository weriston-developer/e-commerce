<?php

namespace App\Domain\Rules;

use App\Domain\Interfaces\CategoryRepositoryInterface;

class CategoryExistsRule
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    /**
     * Verifica se a categoria existe
     *
     * @param string $categoryUuid
     * @return bool
     */
    public function validate(string $categoryUuid): bool
    {
        $category = $this->categoryRepository->findByUuid($categoryUuid);
        
        return $category !== null;
    }
}