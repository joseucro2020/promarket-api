<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SpecialCategory;
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
        // 1. Obtener la configuración dinámica de la base de datos
        $configRecord = DB::table('app_configurations')->where('key', 'home_layout_json')->first();
        
        $savedLayout = [];
        if ($configRecord && $configRecord->value) {
            $savedLayout = json_decode($configRecord->value, true);
        }

        // Si no hay configuración aún, retornar un array vacío (o un valor por defecto seguro)
        if (empty($savedLayout) || !is_array($savedLayout)) {
            return response()->json([]);
        }

        // 2. Precargar las categorías especiales 
        $specialCategories = collect($this->getSpecialCategories());
        
        // Haremos un seguimiento de las categorías usadas para poder excluirlas del carrusel final (opcional)
        $usedCategoryIds = [];

        $finalLayout = [];

        // 3. Iterar dinámicamente sobre el JSON guardado en el Dashboard
        foreach ($savedLayout as $block) {
            $type = $block['type'] ?? '';

            switch ($type) {
                // Bloques estáticos que no requieren datos adicionales de la base de datos
                case 'TopCategories':
                case 'HeroBanners':
                case 'TextWidget':
                case 'ImageBannerWidget':
                    $finalLayout[] = $block;
                    break;
                
                // Carrusel de Información / Promociones fijas
                case 'InfoCarousel':
                    $finalLayout[] = [
                        'type' => 'InfoCarousel',
                        'data' => $block['data'] ?? []
                    ];
                    break;

                // Carrusel de Productos Más Vendidos
                case 'BestSellersCarousel':
                    $finalLayout[] = [
                        'type' => 'BestSellersCarousel',
                        'title' => !empty($block['data']['title']) ? $block['data']['title'] : 'LOS MÁS VENDIDOS',
                        'description' => !empty($block['data']['description']) ? $block['data']['description'] : 'Productos populares comprados por otros clientes frecuentes.',
                        'data' => $this->getBestSellers()
                    ];
                    break;
                
                // Categoría Especial (Extrae la data dinámica de $specialCategories basándose en el ID configurado)
                case 'SingleSpecialCategory':
                    $categoryId = $block['data']['id'] ?? null;
                    if ($categoryId) {
                        $categoria = $specialCategories->firstWhere('id', $categoryId);
                        if ($categoria) {
                            $usedCategoryIds[] = $categoryId;
                            
                            $customTitle = !empty($block['data']['title']) ? $block['data']['title'] : $categoria['name'];
                            $customDesc = !empty($block['data']['description']) ? $block['data']['description'] : ($categoria['description'] ?? '');
                            
                            $categoria['name'] = $customTitle;

                            $finalLayout[] = [
                                'type' => 'SingleSpecialCategory',
                                'title' => $customTitle,
                                'description' => $customDesc,
                                'products_count' => count($categoria['products'] ?? []),
                                'data' => $categoria
                            ];
                        }
                    }
                    break;

                // Ofertas Exclusivas
                case 'ExclusiveOffersWidget':
                    $finalLayout[] = [
                        'type' => 'ExclusiveOffersWidget',
                        'title' => 'OFERTAS EXCLUSIVAS',
                        'subtitle' => 'Nuestros mejores precios para tus primeros 3 pedidos.',
                        'data' => $this->getExclusiveOffers()
                    ];
                    break;
                
                // Carrusel de Categorías (Excluimos las que ya se mostraron como Single)
                case 'SpecialCategoriesCarousel':
                    // Filtramos las categorías para que no salgan repetidas
                    $filteredCategories = $specialCategories->reject(function ($cat) use ($usedCategoryIds) {
                        return in_array($cat['id'], $usedCategoryIds);
                    })->values();

                    $finalLayout[] = [
                        'type' => 'SpecialCategoriesCarousel',
                        'title' => 'SHOP CATEGORIES',
                        'data' => $filteredCategories->map(function ($cat) {
                            return [
                                'id' => $cat['id'],
                                'name' => $cat['name'],
                                'image' => $cat['image_app'] ?? $cat['image']
                            ];
                        })->toArray()
                    ];
                    break;

                default:
                    // Si el admin envía un bloque desconocido, lo pasamos tal cual (fallback)
                    $finalLayout[] = $block;
                    break;
            }
        }

        return response()->json($finalLayout);
    }

    /**
     * Retorna el Layout SDUI de la vista de Mi Bolsa (Bag)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bagLayout()
    {
        $layout = [];
        $specialCategories = collect($this->getSpecialCategories());

        // Buscar la categoría "Bebidas" por su ID (104) para Tendencia en Miami
        $bebidasCategory = $specialCategories->firstWhere('id', 104);
        
        if ($bebidasCategory) {
            $layout[] = [
                'type' => 'SingleSpecialCategory',
                'description' => '',
                'products_count' => count($bebidasCategory['products'] ?? []),
                'data' => $bebidasCategory
            ];
            
            // Remover "Bebidas" de la colección para no repetirla abajo
            $specialCategories = $specialCategories->reject(function ($cat) {
                return $cat['id'] == 104;
            })->values();
        }

        // Novedades en Miami - podemos usar otra categoría o los Best Sellers
        $layout[] = [
            'type' => 'BestSellersCarousel',
            'description' => 'Novedades en Miami',
            'data' => $this->getBestSellers()
        ];

        return response()->json($layout);
    }

    private function getSpecialCategories()
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

            return $categories->filter(function($category) {
                return $category->products->count() > 0;
            })->values()->toArray();
        });

        return $filteredCategories;
    }

    /**
     * Obtiene los productos más vendidos extrayendo el top 10
     * desde la tabla de detalles de compras.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getBestSellers()
    {
        // 1. Obtenemos los IDs agrupando compras completadas (status 3) de productos activos
        $bestSellersIds = DB::table('purchase_details')
            ->join('product_amount', 'purchase_details.product_amount_id', '=', 'product_amount.id')
            ->join('product_colors', 'product_amount.product_color_id', '=', 'product_colors.id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('products', 'product_colors.product_id', '=', 'products.id')
            ->where('purchases.status', 3) // STATUS_COMPLETED
            ->where('products.status', '1')
            ->select('product_colors.product_id', DB::raw('SUM(purchase_details.quantity) as total_sold'))
            ->groupBy('product_colors.product_id')
            ->orderByDesc('total_sold')
            ->limit(15) // Extraemos un poco más de 10 por si algunos no tienen stock
            ->pluck('product_id');

        if ($bestSellersIds->isEmpty()) {
            return [];
        }

        // 2. Cargamos los productos garantizando que tengan stock disponible
        $bestSellers = Product::whereIn('id', $bestSellersIds)
            ->whereHas('amounts', function ($q) {
                $q->where('amount', '>', 0);
            })
            ->with(['mainImage', 'amounts', 'offers', 'discounts'])
            ->get();

        // 3. Reordenamos y mapeamos los precios calculando descuentos si los tienen
        return $bestSellers->sortBy(function($product) use ($bestSellersIds) {
            return array_search($product->id, $bestSellersIds->toArray());
        })->take(10)->values()->map(function ($product) {
            $arrayProduct = $product->toArray();
            
            // Determinar si aplica oferta o descuento
            $offer = $product->offer;
            $discount = $product->discount;
            
            $percentage = 0;
            if ($offer && $offer->percentage) {
                $percentage = $offer->percentage;
            } elseif ($discount && $discount->percentage) {
                $percentage = $discount->percentage;
            }

            // Mapeo real de precios
            $arrayProduct['original_price'] = round($product->price_1, 2);
            $arrayProduct['discount_percentage'] = $percentage;
            $arrayProduct['current_price'] = round($product->price_1 - ($product->price_1 * ($percentage / 100)), 2);

            return $arrayProduct;
        })->toArray();
    }

    private function getExclusiveOffers()
    {
        // Obtener productos que tienen ofertas o descuentos activos, stock disponible y estatus 1
        $products = Product::where('status', '1')
            ->where(function ($q) {
                $q->whereHas('offersActive', function ($q) {
                    $q->whereDate('start', '<=', date('Y-m-d'))
                      ->whereDate('end', '>=', date('Y-m-d'));
                })->orWhereHas('discountsActive', function ($q) {
                    $q->whereDate('start', '<=', date('Y-m-d'))
                      ->whereDate('end', '>=', date('Y-m-d'));
                });
            })
            ->whereHas('amounts', function ($q) {
                $q->where('amount', '>', 0);
            })
            ->with(['mainImage', 'amounts', 'offers', 'discounts'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return $products->map(function ($product) {
            $arrayProduct = $product->toArray();
            
            // Determinar si aplica oferta o descuento
            $offer = $product->offer;
            $discount = $product->discount;
            
            $percentage = 0;
            if ($offer && $offer->percentage) {
                $percentage = $offer->percentage;
            } elseif ($discount && $discount->percentage) {
                $percentage = $discount->percentage;
            }

            // Mapeo real de precios
            $arrayProduct['original_price'] = round($product->price_1, 2);
            $arrayProduct['discount_percentage'] = $percentage;
            $arrayProduct['current_price'] = round($product->price_1 - ($product->price_1 * ($percentage / 100)), 2);

            return $arrayProduct;
        })->toArray();
    }
}
