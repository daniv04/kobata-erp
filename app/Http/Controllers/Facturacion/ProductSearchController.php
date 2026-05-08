<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Products;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSearchController
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $categoryId = $request->query('category_id');

        $products = Products::with('category')
            ->where('is_active', true)
            ->when(strlen($query) >= 2, fn ($q) => $q->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('sku', 'ilike', "%{$query}%")
                    ->orWhere('cabys_code', 'ilike', "%{$query}%");
            }))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (Products $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'cabys_code' => $p->cabys_code,
                'sale_price' => $p->sale_price,
                'tax_percentage' => $p->tax_percentage ?? 13,
                'category' => $p->category?->name,
            ]);

        return response()->json($products);
    }
}
