<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Categories;
use Illuminate\Http\JsonResponse;

class CategoriesController
{
    public function __invoke(): JsonResponse
    {
        $categories = Categories::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }
}
