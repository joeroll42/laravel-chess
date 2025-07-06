<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlatformController extends Controller
{
    /**
     * Display a listing of platforms.
     */
    public function index()
    {
        return response()->json(Platform::all());
    }

    /**
     * Store a newly created platform.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|unique:platforms,name',
            'link'   => 'required|url',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $platform = Platform::create($validated);

        return response()->json($platform, 201);
    }

    /**
     * Display the specified platform.
     */
    public function show(string $id)
    {
        $platform = Platform::findOrFail($id);

        return response()->json($platform);
    }

    /**
     * Update the specified platform.
     */
    public function update(Request $request, string $id)
    {
        $platform = Platform::findOrFail($id);

        $validated = $request->validate([
            'name'   => ['sometimes', 'string', Rule::unique('platforms')->ignore($platform->id)],
            'link'   => 'sometimes|url',
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $platform->update($validated);

        return response()->json($platform);
    }

    /**
     * Remove the specified platform.
     */
    public function destroy(string $id)
    {
        $platform = Platform::findOrFail($id);
        $platform->delete();

        return response()->json(['message' => 'Platform deleted successfully.']);
    }
}
