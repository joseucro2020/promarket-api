<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Address;
use App\Models\User;
use App\Models\Estado;
use App\Models\Municipality;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id;
        if (!$userId) {
            return response()->json([]);
        }

        $addresses = Address::where('user_id', $userId)->get()->map(function ($address) {
            $address->address_line_1 = $address->address;
            $address->address_line_2 = $address->address_line2;
            $address->lng = $address->long;
            return $address;
        });
        return response()->json($addresses);
    }

    public function store(Request $request)
    {
        $userId = $request->user()?->id;
        if (!$userId) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $validated = $request->validate([
            'type' => 'nullable|string',
            'title' => 'required|string',
            'address_line_1' => 'required|string',
            'address_line_2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'name' => 'nullable|string',
            'country_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'is_default' => 'boolean',
        ]);

        $addressData = [
            'user_id' => $userId,
            'type' => $validated['type'] ?? 'home',
            'title' => $validated['title'],
            'address' => $validated['address_line_1'],
            'address_line2' => $validated['address_line_2'] ?? '',
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'lat' => $validated['lat'] ?? 0,
            'long' => $validated['lng'] ?? 0, // Mapeado a 'long'
            'name' => $validated['name'] ?? ($request->user()?->name ?? ''),
            'country_code' => $validated['country_code'] ?? null,
            'phone' => $validated['phone'] ?? ($request->user()?->telefono ?? ''),
            'email' => $validated['email'] ?? ($request->user()?->email ?? null),
            'is_default' => !empty($validated['is_default']) ? 1 : 0,
            'tag' => 1,
            'reference' => ''
        ];

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $userId)->update(['is_default' => 0]);
        }

        $address = Address::create($addressData);
        return response()->json($address, 201);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()?->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'type' => 'nullable|string',
            'title' => 'nullable|string',
            'address_line_1' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'name' => 'nullable|string',
            'country_code' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'is_default' => 'boolean',
        ]);

        $addressData = [];
        if (isset($validated['type'])) $addressData['type'] = $validated['type'];
        if (isset($validated['title'])) $addressData['title'] = $validated['title'];
        if (isset($validated['address_line_1'])) $addressData['address'] = $validated['address_line_1'];
        if (isset($validated['address_line_2'])) $addressData['address_line2'] = $validated['address_line_2'];
        if (isset($validated['city'])) $addressData['city'] = $validated['city'];
        if (isset($validated['state'])) $addressData['state'] = $validated['state'];
        if (isset($validated['zip'])) $addressData['zip'] = $validated['zip'];
        if (isset($validated['lat'])) $addressData['lat'] = $validated['lat'];
        if (isset($validated['lng'])) $addressData['long'] = $validated['lng'];
        if (isset($validated['name'])) $addressData['name'] = $validated['name'];
        if (isset($validated['country_code'])) $addressData['country_code'] = $validated['country_code'];
        if (isset($validated['phone'])) $addressData['phone'] = $validated['phone'];
        if (isset($validated['email'])) $addressData['email'] = $validated['email'];
        if (isset($validated['is_default'])) $addressData['is_default'] = $validated['is_default'] ? 1 : 0;

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $address->user_id)->update(['is_default' => 0]);
        }

        $address->update($addressData);
        return response()->json($address);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()?->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $address->delete();
        return response()->json(null, 204);
    }

    /**
     * Valida si una dirección tiene cobertura de despacho y calcula el tiempo estimado de entrega.
     */
    public function checkCoverage(Request $request)
    {
        $stateName = $request->input('state');
        $cityName = $request->input('city');
        $addressFull = $request->input('address', '');
        $lat = $request->input('lat');
        $lng = $request->input('lng');

        // Coordenadas base del Almacén / Tienda Principal ProMarket (La Isabelica, Valencia, Carabobo)
        $storeLat = 10.1704;
        $storeLng = -67.9708;

        $distanceKm = null;
        if (!empty($lat) && !empty($lng)) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($lat - $storeLat);
            $dLon = deg2rad($lng - $storeLng);
            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($storeLat)) * cos(deg2rad($lat)) *
                 sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distanceKm = round($earthRadius * $c, 1);
        }

        // 1. Verificación por Estado y Municipio si están en la base de datos
        $isStateCovered = true;
        $isMunicipalityCovered = true;

        try {
            if ($stateName) {
                $estado = Estado::where('nombre', 'LIKE', '%' . trim($stateName) . '%')->first();
                if ($estado && (int)$estado->status === 0) {
                    $isStateCovered = false;
                }
            }

            if ($cityName) {
                $municipality = Municipality::where('name', 'LIKE', '%' . trim($cityName) . '%')->first();
                if ($municipality && (int)$municipality->status === 0) {
                    $isMunicipalityCovered = false;
                }
            }
        } catch (\Exception $e) {
            // Si la base de datos aún no está migrada o hay un fallo de conexión temporal, se usa la distancia GPS
        }

        // Si el estado o municipio está explícitamente desactivado en el dashboard:
        if (!$isStateCovered || !$isMunicipalityCovered) {
            return response()->json([
                'has_coverage' => false,
                'distance_km' => $distanceKm,
                'message' => 'Lo sentimos, todavía no hacemos entregas en esta zona. Selecciona otra dirección.'
            ]);
        }

        // Si la distancia supera los 25 km del centro de distribución:
        if ($distanceKm !== null && $distanceKm > 25) {
            return response()->json([
                'has_coverage' => false,
                'distance_km' => $distanceKm,
                'message' => 'Tu ubicación se encuentra a ' . $distanceKm . ' km, fuera del radio máximo de entrega rápida.'
            ]);
        }

        // Cálculo dinámico del tiempo estimado según la distancia
        if ($distanceKm !== null) {
            if ($distanceKm <= 3) {
                $estimatedTime = '15-25 mins';
            } elseif ($distanceKm <= 7) {
                $estimatedTime = '20-35 mins';
            } elseif ($distanceKm <= 15) {
                $estimatedTime = '30-45 mins';
            } else {
                $estimatedTime = '45-60 mins';
            }
        } else {
            $estimatedTime = '20-35 mins';
        }

        return response()->json([
            'has_coverage' => true,
            'distance_km' => $distanceKm,
            'estimated_time' => $estimatedTime,
            'store_name' => 'ProMarket - Sede Principal',
            'message' => '¡Excelente! Contamos con cobertura de entrega inmediata en tu zona.'
        ]);
    }

    /**
     * Retorna la información de la(s) sede(s) física(s) de ProMarket para Recogida (Pickup).
     */
    public function getPickupLocations()
    {
        return response()->json([
            [
                'id' => 1,
                'name' => 'ProMarket - Sede Principal',
                'code' => 'PM #01',
                'brand' => 'promarket',
                'address' => 'Av. Henry Ford, Sector La Isabelica, Valencia, Carabobo, 2003',
                'city' => 'Valencia',
                'state' => 'Carabobo',
                'country' => 'Venezuela',
                'lat' => 10.1704,
                'lng' => -67.9708,
                'phone' => '+58 412-1234567',
                'schedule_badge' => 'ABIERTO HOY',
                'schedule_text' => 'Lunes a Domingo: 8:00 AM - 10:00 PM',
                'alcohol_status' => 'Disponible hasta las 9:00 PM',
                'map_image' => 'https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800&auto=format&fit=crop&q=80',
                'notes' => 'Estacionamiento gratuito disponible. Retira en el counter de Express Pickup.'
            ]
        ]);
    }
}
