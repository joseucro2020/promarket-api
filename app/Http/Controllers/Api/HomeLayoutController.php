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
        $layout = [];
        
        $layout[] = ['type' => 'TopCategories'];
        $layout[] = ['type' => 'HeroBanners'];

        $specialCategories = collect($this->getSpecialCategories());

        $layout[] = [
            'type' => 'BestSellersCarousel',
            'description' => 'Productos populares comprados por otros clientes frecuentes.',
            'data' => $this->getBestSellers()
        ];
        
        $layout[] = [
            'type' => 'InfoCarousel',
            'data' => [
                ['image' => '/assets/banners/promo1.png'],
                ['image' => '/assets/banners/promo2.png'],
                ['image' => '/assets/banners/promo3.png'],
            ]
        ];

        // Buscar la categoría "Farmacia" por su ID (3)
        $farmaciaCategory = $specialCategories->firstWhere('id', 3);
        
        if ($farmaciaCategory) {
            $layout[] = [
                'type' => 'SingleSpecialCategory',
                'description' => $farmaciaCategory['description'] ?? 'Compra nuestra NUEVA variedad ampliada. Entregado fresco o gratis.',
                'products_count' => count($farmaciaCategory['products'] ?? []),
                'data' => $farmaciaCategory
            ];
            
            // Remover "Farmacia" de la colección para no repetirla abajo
            $specialCategories = $specialCategories->reject(function ($cat) {
                return $cat['id'] == 3;
            })->values();
        }

        // Restaurar el Carrusel Principal con TODAS las categorías restantes
        $layout[] = [
            'type' => 'SpecialCategoriesCarousel',
            'title' => 'SHOP CATEGORIES',
            'data' => $specialCategories->map(function ($cat) {
                return [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'image' => $cat['image_app'] ?? $cat['image']
                ];
            })->values()->toArray()
        ];

        // Nuevo bloque: Widget de Texto Dinámico
        $layout[] = [
            'type' => 'TextWidget',
            'data' => [
                'text' => "5,000+ PRODUCTS\nIN YOUR POCKET.",
                'fontSize' => '24px',
                'color' => '#111',
                'align' => 'center',
                'fontWeight' => '900',
                'fontStyle' => 'italic',
                'padding' => '24px 16px'
            ]
        ];

         // Buscar la categoría "Bebidas" por su ID (4)
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
        
        // Nuevo bloque: Ofertas Exclusivas
        $layout[] = [
            'type' => 'ExclusiveOffersWidget',
            'title' => 'OFERTAS EXCLUSIVAS',
            'subtitle' => 'Nuestros mejores precios para tus primeros 3 pedidos.',
            'data' => $this->getExclusiveOffers()
        ];

       

        // Buscar la categoría "Bebidas" por su ID (4)
        $bebidasCategory = $specialCategories->firstWhere('id', 105);
        
        if ($bebidasCategory) {
            $layout[] = [
                'type' => 'SingleSpecialCategory',
                'description' => '',
                'products_count' => count($bebidasCategory['products'] ?? []),
                'data' => $bebidasCategory
            ];
            
            // Remover "Bebidas" de la colección para no repetirla abajo
            $specialCategories = $specialCategories->reject(function ($cat) {
                return $cat['id'] == 105;
            })->values();
        }

         // Nuevo bloque: Widget de Texto Dinámico
        $layout[] = [
            'type' => 'TextWidget',
            'data' => [
                'text' => "BUILT FOR SPEED.",
                'fontSize' => '24px',
                'color' => '#111',
                'align' => 'center',
                'fontWeight' => '900',
                'fontStyle' => 'italic',
                'padding' => '24px 16px'
            ]
        ];

        // Añadir el resto de las categorías especiales al final
        // foreach ($specialCategories as $category) {
        //     $layout[] = [
        //         'type' => 'SingleSpecialCategory',
        //         'description' => $category['description'] ?? 'Explora nuestra selección especial y descubre grandes ofertas cada semana.',
        //         'products_count' => count($category['products'] ?? []),
        //         'data' => $category
        //     ];
        // }

        // Pon aquí todos los IDs de las categorías que quieres mostrar como SingleSpecialCategory
        // (Nota: Quitamos 3, 104 y 105 porque ya los estás agregando manualmente más arriba)
        $idsDestacados = [55, 22]; 
        
        foreach ($idsDestacados as $id) {
            $categoria = $specialCategories->firstWhere('id', $id);
            
            if ($categoria) {
                $layout[] = [
                    'type' => 'SingleSpecialCategory',
                    'description' => $categoria['description'] ?? '',
                    'products_count' => count($categoria['products'] ?? []),
                    'data' => $categoria
                ];
                
                // Remover la categoría de la colección para no repetirla abajo
                $specialCategories = $specialCategories->reject(function ($cat) use ($id) {
                    return $cat['id'] == $id;
                })->values();
            }
        }

          // Nuevo bloque: Widget de Texto Dinámico
        $layout[] = [
            'type' => 'TextWidget',
            'data' => [
                'text' => "POWERED BY NATURE,\n DELIVERED FRESH DAILYS",
                'fontSize' => '24px',
                'color' => '#111',
                'align' => 'center',
                'fontWeight' => '900',
                'fontStyle' => 'italic',
                'padding' => '24px 16px'
            ]
        ];

        // Pon aquí todos los IDs de las categorías que quieres mostrar como SingleSpecialCategory
        // (Nota: Quitamos 3, 104 y 105 porque ya los estás agregando manualmente más arriba)
        $idsDestacados = [23, 39]; 
        
        foreach ($idsDestacados as $id) {
            $categoria = $specialCategories->firstWhere('id', $id);
            
            if ($categoria) {
                $layout[] = [
                    'type' => 'SingleSpecialCategory',
                    'description' => $categoria['description'] ?? '',
                    'products_count' => count($categoria['products'] ?? []),
                    'data' => $categoria
                ];
                
                // Remover la categoría de la colección para no repetirla abajo
                $specialCategories = $specialCategories->reject(function ($cat) use ($id) {
                    return $cat['id'] == $id;
                })->values();
            }
        }

          // Nuevo bloque: Widget de Texto Dinámico
        $layout[] = [
            'type' => 'TextWidget',
            'data' => [
                'text' => "READY TO SHOP?",
                'fontSize' => '24px',
                'color' => '#111',
                'align' => 'center',
                'fontWeight' => '900',
                'fontStyle' => 'italic',
                'padding' => '24px 16px'
            ]
        ];

        // Pon aquí todos los IDs de las categorías que quieres mostrar como SingleSpecialCategory
        // (Nota: Quitamos 3, 104 y 105 porque ya los estás agregando manualmente más arriba)
        $idsDestacados = [87, 77]; 
        
        foreach ($idsDestacados as $id) {
            $categoria = $specialCategories->firstWhere('id', $id);
            
            if ($categoria) {
                $layout[] = [
                    'type' => 'SingleSpecialCategory',
                    'description' => $categoria['description'] ?? '',
                    'products_count' => count($categoria['products'] ?? []),
                    'data' => $categoria
                ];
                
                // Remover la categoría de la colección para no repetirla abajo
                $specialCategories = $specialCategories->reject(function ($cat) use ($id) {
                    return $cat['id'] == $id;
                })->values();
            }
        }

         // Nuevo bloque: Banner con Imagen y URL
        $layout[] = [
            'type' => 'ImageBannerWidget',
            'data' => [
                'imageUrl' => '/assets/banners/features_banner.png',
                'linkUrl' => '/categories/ofertas'
            ]
        ];

        // Pon aquí todos los IDs de las categorías que quieres mostrar como SingleSpecialCategory
        // (Nota: Quitamos 3, 104 y 105 porque ya los estás agregando manualmente más arriba)
        $idsDestacados = [15, 16]; 
        
        foreach ($idsDestacados as $id) {
            $categoria = $specialCategories->firstWhere('id', $id);
            
            if ($categoria) {
                $layout[] = [
                    'type' => 'SingleSpecialCategory',
                    'description' => $categoria['description'] ?? '',
                    'products_count' => count($categoria['products'] ?? []),
                    'data' => $categoria
                ];
                
                // Remover la categoría de la colección para no repetirla abajo
                $specialCategories = $specialCategories->reject(function ($cat) use ($id) {
                    return $cat['id'] == $id;
                })->values();
            }
        }

          // Nuevo bloque: Widget de Texto Dinámico
        $layout[] = [
            'type' => 'TextWidget',
            'data' => [
                'text' => "SOON YOU WILL FIND MORE CATEGORIES!",
                'fontSize' => '24px',
                'color' => '#111',
                'align' => 'center',
                'fontWeight' => '900',
                'fontStyle' => 'italic',
                'padding' => '24px 16px'
            ]
        ];

        $idsDestacados = [9, 23]; 
        
        foreach ($idsDestacados as $id) {
            $categoria = $specialCategories->firstWhere('id', $id);
            
            if ($categoria) {
                $layout[] = [
                    'type' => 'SingleSpecialCategory',
                    'description' => $categoria['description'] ?? '',
                    'products_count' => count($categoria['products'] ?? []),
                    'data' => $categoria
                ];
                
                // Remover la categoría de la colección para no repetirla abajo
                $specialCategories = $specialCategories->reject(function ($cat) use ($id) {
                    return $cat['id'] == $id;
                })->values();
            }
        }


        

        return response()->json($layout);
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

    /**
     * Obtiene productos aleatorios y les inyecta valores de descuento "falsos"
     * para propósitos de diseño visual de Ofertas Exclusivas.
     *
     * @return array
     */
    private function getExclusiveOffers()
    {
        $products = Product::where('status', '2')
            ->with(['mainImage', 'amounts'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return $products->map(function ($product) {
            $arrayProduct = $product->toArray();
            
            // Inyectar datos de descuento simulados
            $arrayProduct['original_price'] = round($product->price_1 * 1.5, 2);
            $arrayProduct['discount_percentage'] = rand(10, 75);

            return $arrayProduct;
        })->toArray();
    }
}
