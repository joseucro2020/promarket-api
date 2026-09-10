<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpecialCategory;
use Illuminate\Http\Request;

class SpecialCategoryController extends Controller
{
    public function index()
    {
        $filteredCategories = \Illuminate\Support\Facades\Cache::remember('special_categories_full', now()->addMinutes(30), function () {
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
                    ->limit($category->slider_quantity ?: 15);
                }]);
            });

            // Filtrar categorías que no tienen productos
            return $categories->filter(function($category) {
                return $category->products->count() > 0;
            })->values()->toArray();
        });

        return response()->json($filteredCategories);
    }
}
