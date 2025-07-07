<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Inertia\Response;

class WalletController extends Controller
{
    /**
     * @return Response
     */
    public function main(): Response
    {
        $user = Auth::user();

        // All token-related transactions (purchase, create challenge, accept challenge)
        $tokenTransactions = Transaction::where('transaction_origin', $user->id)
            ->whereIn('request_type', ['token_purchase', 'token_deduction', 'token_use'])
            ->latest()
            ->get()
            ->map(function ($tx) {
                $notes = json_decode($tx->transaction_notes ?? '{}', true);
                $action = match ($tx->request_type) {
                    'token_purchase'  => 'Purchase',
                    'token_deduction' => 'Create Challenge',
                    'token_use'       => 'Accept Challenge',
                    default           => 'Token Activity'
                };

                return [
                    'tokens' => $notes['tokens'] ?? 0,
                    'note' => $notes['note'] ?? $action,
                    'date' => Carbon::parse($tx->created_at)->format('M d, Y'),
                ];
            });


        // Deposit / Withdrawal transactions

        $depositTransactions = Transaction::where('transaction_origin', $user->id)
            ->where(function ($q) {
                $q->where('request_type', 'deposit')
                    ->orWhere(function ($sub) {
                        $sub->where('request_type', 'withdrawal')
                            ->whereIn('request_id', function ($query) {
                                $query->select('id')
                                    ->from('withdrawal_requests')
                                    ->where('request_status', 'completed');
                            });
                    });
            })
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

        $orders = WithdrawalRequest::with(['moderator', 'initiatorUser', 'transaction'])
            ->where(function ($q) use ($userId) {
                $q->where('moderator_account_id', $userId)
                    ->orWhere('initiator', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) use ($userId) {
                $viewAs = match (true) {
                    $order->moderator_account_id === $userId => 'moderator',
                    $order->initiator === $userId => 'requestor',
                    default => 'unknown',
                };

                return [
                    'id' => $order->id,
                    'notes' => $order->notes,
                    'status' => $order->request_status,
                    'moderator' => $order->moderator,     // uses Eloquent relationship
                    'initiatorUser' => $order->initiatorUser,     // uses Eloquent relationship
                    'transaction' => $order->transaction, // assumed relationship
                    'created_at' => $order->created_at,
                    'view_as' => $viewAs,
                ];
            });

        return Inertia::render('Player/wallet/ActivePeers', [
            'moderators' => $moderators,
            'orders' => $orders,
        ]);
    }

    public function request(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10',
            'phone' => 'required|string',
            'peer_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        // 1. Pending withdrawals (already requested, not completed)
        $pendingTotal = Transaction::where('transaction_origin', $user->id)
            ->where('request_type', 'withdrawal')
            ->where('transaction_complete_status', false)
            ->sum('amount');

        // 2. Locked stake in active matches
        $lockedStake = Challenge::whereNotIn('challenge_status', ['won', 'loss', 'draw'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('opponent_id', $user->id);
            })
            ->sum('stake');

        // 3. Calculate withdrawable balance
        $availableBalance = $user->balance - $pendingTotal - $lockedStake;

        // 4. Ensure available balance is enough
        if ($validated['amount'] > $availableBalance) {
            return response()->json([
                'message' => 'Your available balance is locked by active matches or pending withdrawals.',
            ], 422);
        }

        // 5. Create Withdrawal Request
        $withdrawalRequest = WithdrawalRequest::create([
            'initiator' => $user->id,
            'moderator_account_id' => $validated['peer_id'],
            'request_status' => 'pending',
            'notes' => json_encode(['phone' => $validated['phone']]),
        ]);

        // 6. Create Transaction record
        Transaction::create([
            'request_type' => 'withdrawal',
            'request_id' => $withdrawalRequest->id,
            'transaction_origin' => $user->id,
            'transaction_destination' => $validated['peer_id'],
            'amount' => $validated['amount'],
            'currency' => 'KES',
            'delivery_confirmation_status' => false,
            'transaction_stage' => 'initiated',
            'confirmation_status' => false,
            'transaction_complete_status' => false,
            'transaction_notes' => json_encode([
                'notes' => '',
                'chat' => '',
            ]),
        ]);

        return response()->json(['message' => 'Withdrawal request submitted successfully.']);
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
