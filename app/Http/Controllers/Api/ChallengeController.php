<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChallengeController extends Controller
{
    /**
     * Display a listing of challenges.
     */
    public function index()
    {
        return response()->json(
            Challenge::with(['user', 'opponent', 'platform'])->get()
        );
    }

    /**
     * Store a newly created challenge.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'position'        => ['nullable', Rule::in(['challenger', 'opponent'])],
            'stake'           => 'required|numeric|min:0',
            'tokens'          => 'required|integer|min:0',
            'platform_id'     => 'required|exists:platforms,id',
            'time_control'    => 'required|string',
        ]);

        $challenge = Challenge::create([
            'user_id'         => auth()->id(),
            'request_state'   => 'pending',
            'position'        => $validated['position'] ?? null,
            'stake'           => $validated['stake'],
            'tokens'          => $validated['tokens'],
            'platform_id'     => $validated['platform_id'],
            'time_control'    => $validated['time_control'],
        ]);

        $challenge->load(['user', 'platform']);

        if ($challenge->opponent_id) {
            $challenge->load('opponent');
        }

        return response()->json($challenge, 201);

    }

    /**
     * Display the specified challenge.
     */
    public function show(string $id)
    {
        $challenge = Challenge::with(['user', 'platform'])->findOrFail($id);
        return response()->json($challenge);
    }

    /**
     * Update the specified challenge.
     */
    public function update(Request $request, string $id)
    {
        $challenge = Challenge::findOrFail($id);

        $validated = $request->validate([
            'request_state'   => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'canceled', 'disputed'])],
            'challenge_status'=> ['nullable', Rule::in(['pending', 'won', 'draw', 'loss', 'anomaly'])],
            'accepted_at'     => 'nullable|date',
            'rejected_at'     => 'nullable|date',
            'canceled_at'     => 'nullable|date',
        ]);

        $challenge->update($validated);

        return response()->json($challenge);
    }

    /**
     * Remove the specified challenge.
     */
    public function destroy(string $id)
    {
        $challenge = Challenge::findOrFail($id);
        $challenge->delete();

        return response()->json(['message' => 'Challenge deleted successfully.']);
    }
}
