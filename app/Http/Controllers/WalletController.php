<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Inertia\Response;
use App\Models\Platform;

class WalletController extends Controller
{
    /**
     * @return Response
     */
    public function main(): Response
    {
        $user = Auth::user();

        // Token transactions
        $tokenTransactions = Transaction::where('transaction_origin', $user->id)
            ->where('request_type', 'token_purchase')
            ->latest()
            ->get()
            ->map(function ($tx) {
                $notes = json_decode($tx->transaction_notes ?? '{}', true);
                return [
                    'tokens' => $notes['tokens'] ?? 0,
                    'note' => $notes['note'] ?? null,
                    'date' => Carbon::parse($tx->created_at)->format('M d, Y'),
                ];
            });

        // Deposit / Withdrawal transactions
        $depositTransactions = Transaction::where('transaction_origin', $user->id)
            ->whereIn('request_type', ['deposit', 'withdrawal'])
            ->latest()
            ->get()
            ->map(function ($tx) {
                return [
                    'type' => $tx->request_type,
                    'amount' => $tx->amount,
                    'date' => Carbon::parse($tx->created_at)->format('F d, Y'),
                ];
            });

        // Match result transactions (e.g. betting or challenge system)
        $matchTransactions = Transaction::with(['challenge.user', 'challenge.opponent'])
            ->whereIn('request_type', ['stake_win_credit', 'stake_loss_debit'])
            ->where(fn ($q) => $q->where('transaction_origin', $user->id)->orWhere('transaction_destination', $user->id))
            ->latest()
            ->get()
            ->groupBy('request_id')
            ->map(function ($txs) use ($user) {
                $tx = $txs->firstWhere('transaction_destination', $user->id)
                    ?? $txs->firstWhere('transaction_origin', $user->id)
                    ?? $txs->first();

                if (!$challenge = $tx->challenge) return null;

                $opponent = $challenge->user_id === $user->id ? $challenge->opponent : $challenge->user;

                return [
                    'opponent' => $opponent?->name ?? 'Unknown',
                    'result'   => $tx->transaction_destination === $user->id ? 'Win' : ($tx->transaction_origin === $user->id ? 'Loss' : 'N/A'),
                    'tokens'   => $challenge->tokens,
                    'amount'   => abs($tx->amount),
                    'type'     => $tx->transaction_destination === $user->id ? 'credit' : 'debit',
                    'tag'      => $tx->transaction_destination === $user->id ? 'Match Win' : 'Match Loss',
                    'date'     => $tx->created_at->format('M d, Y'),
                ];
            })
            ->filter()
            ->values();





        return Inertia::render('Player/wallet/Main', [
            'wallet' => [
                'balance' => $user->balance,
                'tokens' => $user->token_balance ?? 0, // or $user->tokens if it's a column
                'transactions' => $tokenTransactions,
                'depositTransactions' => $depositTransactions,
                'matchTransactions' => $matchTransactions,
            ],
        ]);
    }


    public function index(): Response
    {
        $userId = auth()->id();

        $moderators = User::whereJsonContains('roles', 'moderator')
            ->where('id', '!=', $userId)
            ->select('id', 'name', 'phone', 'balance')
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'max' => $user->balance,
            ]);

        // ✅ Ensure roles is always treated as an array
        $roles = (array)auth()->user()->roles;
        $isModerator = in_array('moderator', $roles);

        $orders = WithdrawalRequest::with(['moderator', 'initiator', 'transaction'])
            ->when($isModerator, fn($q) => $q->where('moderator_account_id', $userId))
            ->when(!$isModerator, fn($q) => $q->where('initiator', $userId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) use ($isModerator) {
                return [
                    'id' => $order->id,
                    'notes' => $order->notes,
                    'status' => $order->request_status,
                    'moderator' => $order->moderator,
                    'initiator' => User::findOrFail($order->initiator),
                    'transaction' => $order->transaction,
                    'created_at' => $order->created_at,
                    'view_as' => $isModerator ? 'moderator' : 'requestor', // ✅ add tag
                ];
            });

        return Inertia::render('Player/wallet/ActivePeers', [
            'moderators' => $moderators,
            'orders' => $orders,
        ]);
    }



    public function store_challenge(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'stake'        => 'required|numeric|min:10',
            'platform'     => 'required|exists:platforms,name',
            'timeControl'  => 'required|string',
        ]);

        $user = Auth::user();

        // Calculate total stake already locked in pending/anomaly challenges
        $lockedStake = Challenge::where('user_id', $user->id)
            ->whereIn('challenge_status', ['pending', 'anomaly'])
            ->sum('stake');

        $availableBalance = $user->balance - $lockedStake;

        // Ensure they have enough balance to stake
        if ($availableBalance < $validated['stake']) {
            return redirect()->back()->withErrors([
                'stake' => 'Insufficient free balance to create this challenge.'
            ]);
        }

        // Calculate required tokens (1 token per 10 KES)
        $requiredTokens = ceil($validated['stake'] / 10);

        if ($user->tokens < $requiredTokens) {
            return redirect()->back()->withErrors([
                'stake' => "You need at least {$requiredTokens} tokens to create this challenge."
            ]);
        }

        $platform = Platform::where('name', $validated['platform'])->firstOrFail();

        DB::transaction(function () use ($validated, $platform, $user, $requiredTokens) {
            // Deduct tokens from user
            $user->tokens -= $requiredTokens;
            $user->save();

            // Create challenge
            $challenge = Challenge::create([
                'user_id'       => $user->id,
                'request_state' => 'pending',
                'stake'         => $validated['stake'],
                'tokens'        => $requiredTokens,
                'platform_id'   => $platform->id,
                'time_control'  => $validated['timeControl'],
            ]);

            // Record token deduction as a transaction
            Transaction::create([
                'request_type' => 'token_deduction',
                'request_id' => $challenge->id,
                'transaction_origin' => $user->id,
                'transaction_destination' => $user->id,
                'amount' => $requiredTokens * 10, // visible KES equivalent
                'currency' => 'KES',
                'delivery_confirmation_status' => true,
                'transaction_stage' => 'confirmed',
                'confirmation_status' => true,
                'transaction_complete_status' => true,
                'transaction_notes' => json_encode([
                    'tokens' => $requiredTokens,
                    'amount' => $requiredTokens * 10,
                    'note' => "Token deduction for challenge ID {$challenge->id}",
                ]),
            ]);
        });

        return redirect()->route('matches.active')
            ->with('success', 'Challenge created!');
    }

    public function buyTokens(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $cost = $validated['amount'] * 1; // e.g., 1 KES per token

        if ($user->balance < $cost) {
            return response()->json(['message' => 'Insufficient balance.'], 422);
        }

        // Deduct balance and record transaction
        $user->balance -= $cost;
        $user->token_balance += $validated['amount'];
        $user->save();

        Transaction::create([
            'request_type' => 'token_purchase',
            'request_id' => 0,
            'transaction_origin' => $user->id,
            'transaction_destination' => $user->id,
            'amount' => $cost,
            'currency' => 'KES',
            'delivery_confirmation_status' => true,
            'transaction_stage' => 'confirmed',
            'confirmation_status' => true,
            'transaction_complete_status' => true,
            'transaction_notes' => json_encode([
                'tokens' => $validated['amount'],
                'note' => 'Token purchase from balance',
            ]),
        ]);

        return response()->json(['message' => 'Tokens purchased successfully.']);
    }
}
