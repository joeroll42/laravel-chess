<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Platform;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;

class ChallengeController extends Controller
{
    public function index(): Response
    {
        $activeChallenges = Challenge::with(['user', 'opponent', 'platform'])
            ->where('request_state', 'pending')
            ->where('user_id', '!=', auth()->id())
            ->get();

        return Inertia::render('Player/matches/ActiveMatches', [
            'challenges' => $activeChallenges
        ]);
    }

    public function my_matches(): Response
    {
        $myId = Auth::id();

        $myChallenges = Challenge::with(['user', 'opponent', 'platform'])
            ->where(function ($query) use ($myId) {
                $query->where('user_id', $myId)
                    ->orWhere('opponent_id', $myId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Player/matches/MyChallenges', [
            'challenges' => $myChallenges,
        ]);
    }

    public function show($id): Response
    {
        $challenge = Challenge::with(['user', 'opponent', 'platform'])->find($id);

        return Inertia::render('Player/matches/ChallengeDetails', [
            'challengeDetails' => $challenge
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function contend(Request $request): RedirectResponse
    {
        $request->validate([
            'challenge_id' => 'required|exists:challenges,id',
        ]);

        $user = auth()->user();
        $challenge = Challenge::findOrFail($request->challenge_id);

        if ($challenge->user_id === $user->id) {
            throw ValidationException::withMessages(['challenge_id' => 'You cannot accept your own challenge.']);
        }

        if ($challenge->request_state !== 'pending') {
            throw ValidationException::withMessages(['challenge_id' => 'This challenge is no longer available.']);
        }

        $lockedStake = Challenge::where('user_id', $user->id)
            ->whereIn('challenge_status', ['pending', 'anomaly'])
            ->sum('stake');

        $availableBalance = $user->balance - $lockedStake;

        if ($availableBalance < $challenge->stake) {
            throw ValidationException::withMessages(['challenge_id' => 'Insufficient balance to accept this challenge.']);
        }

        $requiredTokens = $challenge->tokens;
        if ($user->token_balance < $requiredTokens) {
            throw ValidationException::withMessages(['challenge_id' => "You need at least {$requiredTokens} tokens to accept this challenge."]);
        }

        DB::transaction(function () use ($user, $challenge, $requiredTokens) {
            $user->token_balance -= $requiredTokens;
            $user->save();

            $challenge->update([
                'opponent_id' => $user->id,
                'request_state' => 'accepted',
                'accepted_at' => now(),
            ]);

            $this->deductTokensAndLogTransaction(
                user: $user,
                challenge: $challenge,
                type: 'token_use',
                note: 'Tokens used to accept challenge'
            );
        });

        return redirect()->route('matches.ready', [$challenge->id])
            ->with('success', 'Challenge accepted!');
    }

    /**
     * @throws ValidationException
     */
    public function get_results(Request $request, $id)
    {
        $challenge = Challenge::findOrFail($id);

        if (!in_array($challenge->challenge_status, ['won', 'loss', 'draw', 'anomaly'])) {
            $this->resolveMatchAndTransferStake($request, $id, 'challenger');
        }

        return redirect()->route('matches.results', [$id]);
    }

    public function resolveMatchAndTransferStake(Request $request, $challengeId, $winnerRole): JsonResponse
    {
        $validRoles = ['challenger', 'contender'];
        if (!in_array($winnerRole, $validRoles)) {
            throw ValidationException::withMessages([
                'winner' => 'Invalid winner role. Must be either challenger or contender.'
            ]);
        }


        $challenge = Challenge::with(['user', 'opponent'])->where('id', $challengeId)
            ->where('request_state', 'accepted')
            ->firstOrFail();

        $challenger = $challenge->user;
        $contender = $challenge->opponent;

        if (!$contender) {
            throw ValidationException::withMessages([
                'challenge' => 'Challenge does not yet have an opponent.'
            ]);
        }

        $winner = $winnerRole === 'challenger' ? $challenger : $contender;
        $loser  = $winnerRole === 'challenger' ? $contender : $challenger;

        DB::transaction(function () use ($challenge, $winner, $loser, $winnerRole) {
            // Credit winner
            $winner->balance += $challenge->stake;
            $loser->balance -= $challenge->stake;
            $winner->save();
            $loser->save();

            // Update challenge
            $challenge->challenge_status = $winnerRole === 'challenger' ? 'won' : 'loss';
            $challenge->save();

            // Log credit transaction
            Transaction::create([
                'request_type' => 'stake_win_credit',
                'request_id' => $challenge->id,
                'transaction_origin' => $loser->id,
                'transaction_destination' => $winner->id,
                'amount' => $challenge->stake,
                'currency' => 'KES',
                'delivery_confirmation_status' => true,
                'transaction_stage' => 'completed',
                'confirmation_status' => true,
                'transaction_complete_status' => true,
                'transaction_notes' => json_encode([
                    'note' => "Stake credited to {$winnerRole} (user_id={$winner->id}) for challenge #{$challenge->id}",
                    'challenge_id' => $challenge->id,
                    'role' => $winnerRole,
                    'action' => 'credit'
                ]),
            ]);

            // Log debit transaction
            Transaction::create([
                'request_type' => 'stake_loss_debit',
                'request_id' => $challenge->id,
                'transaction_origin' => $loser->id,
                'transaction_destination' => $winner->id,
                'amount' => -$challenge->stake,
                'currency' => 'KES',
                'delivery_confirmation_status' => true,
                'transaction_stage' => 'completed',
                'confirmation_status' => true,
                'transaction_complete_status' => true,
                'transaction_notes' => json_encode([
                    'note' => "Stake debited from loser (user_id={$loser->id}) for challenge #{$challenge->id}",
                    'challenge_id' => $challenge->id,
                    'role' => $winnerRole === 'challenger' ? 'contender' : 'challenger',
                    'action' => 'debit'
                ]),
            ]);
        });

        return response()->json([
            'message' => "Match resolved: {$winnerRole} (user_id={$winner->id}) wins challenge #{$challenge->id}."
        ]);
    }

    public function ready(Request $request, $id): Response
    {
        $challenge = Challenge::with(['user', 'opponent', 'platform'])->find($id);

        return Inertia::render('Player/matches/MatchReady', [
            'challenge' => $challenge
        ]);
    }

    public function create_challenge(): Response
    {
        return Inertia::render('Player/matches/CreateChallenge');
    }

    public function show_results(Request $request, $id): Response
    {
        $user = Auth::user();
        $challenge = Challenge::with(['user', 'opponent'])->findOrFail($id);

        // Restrict access to only participants
        if ($challenge->user_id !== $user->id && $challenge->opponent_id !== $user->id) {
            abort(403, 'Unauthorized access to this match result.');
        }

        // Determine result for logged-in user
        $status = $challenge->challenge_status; // e.g., won, loss, draw, anomaly
        $loggedInIsChallenger = $challenge->user_id === $user->id;

        $result = match ($status) {
            'draw' => 'draw',
            'anomaly', 'canceled' => 'canceled',
            'won' => $loggedInIsChallenger ? 'win' : 'loss',
            'loss' => $loggedInIsChallenger ? 'loss' : 'win',
            default => 'canceled',
        };

        return Inertia::render('Player/matches/MatchResults', [
            'result'       => $result,
            'opponent'     => $loggedInIsChallenger ? $challenge->opponent?->name : $challenge->user->name,
            'tokens'       => $challenge->tokens,
            'winnings'     => (float) $challenge->stake,
            'timeControl'  => $challenge->time_control,
            'newRank'      => 1200,
            'rankChange'   => 0,
        ]);
    }


    public function store_challenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'stake'        => 'required|numeric|min:10',
            'platform'     => 'required|exists:platforms,name',
            'timeControl'  => 'required|string',
        ]);

        $user = Auth::user();

        $lockedStake = Challenge::where('user_id', $user->id)
            ->whereIn('challenge_status', ['pending', 'anomaly'])
            ->sum('stake');

        $availableBalance = $user->balance - $lockedStake;

        if ($availableBalance < $validated['stake']) {
            throw ValidationException::withMessages(['stake' => 'Insufficient free balance to create this challenge.']);
        }

        $requiredTokens = ceil($validated['stake'] / 10);
        if ($user->token_balance < $requiredTokens) {
            throw ValidationException::withMessages(['stake' => "You need at least {$requiredTokens} tokens to create this challenge."]);
        }

        $platform = Platform::where('name', $validated['platform'])->firstOrFail();

        DB::transaction(function () use ($validated, $platform, $user, $requiredTokens) {
            $user->token_balance -= $requiredTokens;
            $user->save();

            $challenge = Challenge::create([
                'user_id'       => $user->id,
                'request_state' => 'pending',
                'stake'         => $validated['stake'],
                'tokens'        => $requiredTokens,
                'platform_id'   => $platform->id,
                'time_control'  => $validated['timeControl'],
            ]);

            $this->deductTokensAndLogTransaction(
                user: $user,
                challenge: $challenge,
                type: 'token_deduction',
                note: 'Tokens used to create challenge'
            );
        });

        return redirect()->route('matches.active')->with('success', 'Challenge created!');
    }

    protected function deductTokensAndLogTransaction(User $user, Challenge $challenge, string $type, string $note): void
    {
        Transaction::create([
            'request_type' => $type,
            'request_id' => $challenge->id,
            'transaction_origin' => $user->id,
            'transaction_destination' => $user->id,
            'amount' => $challenge->tokens * 10,
            'currency' => 'KES',
            'delivery_confirmation_status' => true,
            'transaction_stage' => 'confirmed',
            'confirmation_status' => true,
            'transaction_complete_status' => true,
            'transaction_notes' => json_encode([
                'tokens' => $challenge->tokens,
                'amount' => $challenge->tokens * 10,
                'note' => $note,
                'challenge_id' => $challenge->id,
            ]),
        ]);
    }
}
