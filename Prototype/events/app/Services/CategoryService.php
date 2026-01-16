<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all categories.
     *
     * @return Collection
     */
    public function getAllCategories(): Collection
    {
        return Category::all();
    }
}
