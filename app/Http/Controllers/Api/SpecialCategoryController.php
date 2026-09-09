<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpecialCategory;
use Illuminate\Http\Request;

class SpecialCategoryController extends Controller
{
    public function index()
    {
        $categories = SpecialCategory::where('status', 1)
            ->orderBy('order', 'asc')
            ->get();

        $categories->each(function ($category) {
            $category->load(['products' => function ($query) use ($category) {
                $query->whereHas('amounts', function($q) {
                    $q->where('amount', '>', 0)
                      ->whereColumn('amount', '>=', 'umbral');
                })
                ->with(['mainImage', 'amounts' => function($q) {
                    $q->where('amount', '>', 0)
                      ->whereColumn('amount', '>=', 'umbral');
                }])
                ->limit($category->slider_quantity ?: 15); // Límite en BD para no saturar la memoria RAM
            }]);
        });

        // Filtrar categorías que no tienen productos
        $filteredCategories = $categories->filter(function($category) {
            return $category->products->count() > 0;
        })->values();

        return response()->json($filteredCategories);
    }
}
