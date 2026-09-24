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
        $addresses = Address::where('user_id', $userId)->get();
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
        $validated['user_id'] = $userId;

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address = Address::create($validated);
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

        if (!empty($validated['is_default'])) {
            Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        }

        $address->update($validated);
        return response()->json($address);
    }

    public function destroy(Address $address)
    {
        $address->delete();
        return response()->json(null, 204);
    }
}
