<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q', '');

        if (empty(trim($query))) {
            return response()->json([
                'categories' => [],
                'products' => []
            ]);
        }

        $products = Product::where('status', '1')
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', '%' . $query . '%')
                  ->orWhere('search_text', 'LIKE', '%' . $query . '%');
            })
            ->limit(10)
            ->get(['id', 'name']);

        $categories = Category::where('status', '1')
            ->where('name', 'LIKE', '%' . $query . '%')
            ->limit(5)
            ->get(['id', 'name', 'image', 'icon2']);

        return response()->json([
            'categories' => $categories,
            'products' => $products
        ]);
    }
}
