<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeLayoutController extends Controller
{
    /**
     * Retorna el Layout SDUI de la vista principal (Home)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            ['type' => 'TopCategories'],
            ['type' => 'HeroBanners'],
            [
                'type' => 'BestSellersCarousel',
                'description' => 'Productos populares comprados por otros clientes frecuentes.',
                'data' => $this->getBestSellers()
            ],
            [
                'type' => 'InfoCarousel',
                'data' => [
                    ['image' => '/assets/banners/promo1.png'],
                    ['image' => '/assets/banners/promo2.png'],
                    ['image' => '/assets/banners/promo3.png'],
                ]
            ],
            ['type' => 'SpecialCategories']
        ]);
    }

    /**
     * Obtiene los productos más vendidos extrayendo el top 10
     * desde la tabla de detalles de compras.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getBestSellers()
    {
        // 1. Obtenemos los IDs de los productos más vendidos agrupando de forma segura
        $bestSellersIds = DB::table('purchase_details')
            ->join('product_amount', 'purchase_details.product_amount_id', '=', 'product_amount.id')
            ->join('product_colors', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->select('product_colors.product_id', DB::raw('SUM(purchase_details.quantity) as total_sold'))
            ->groupBy('product_colors.product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->pluck('product_id');

        if ($bestSellersIds->isEmpty()) {
            return collect([]);
        }

        // 2. Cargamos los productos con sus relaciones utilizando los IDs obtenidos
        $bestSellers = Product::whereIn('id', $bestSellersIds)
            ->with(['mainImage', 'amounts'])
            ->get();

        // 3. (Opcional) Re-ordenar la colección obtenida para que coincida con el orden de mayores ventas
        // Ya que whereIn no respeta el orden del arreglo de IDs
        $bestSellers = $bestSellers->sortBy(function($product) use ($bestSellersIds) {
            return array_search($product->id, $bestSellersIds->toArray());
        })->values();

        return $bestSellers;
    }
}
