<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Address;
use App\Models\User;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? User::first()?->id;
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

        $userId = $request->user()?->id ?? User::first()?->id;

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
            'name' => $validated['name'] ?? '',
            'country_code' => $validated['country_code'] ?? null,
            'phone' => $validated['phone'] ?? '',
            'email' => $validated['email'] ?? null,
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

    public function destroy(Address $address)
    {
        $address->delete();
        return response()->json(null, 204);
    }
}
