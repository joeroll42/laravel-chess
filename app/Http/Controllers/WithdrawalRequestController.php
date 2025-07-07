<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class WithdrawalRequestController extends Controller
{
    public function view(Request $request, $id)
    {
        $userId = auth()->id();

        $withdrawalRequest = WithdrawalRequest::with(['initiatorUser', 'moderator', 'transaction'])->findOrFail($id);

        // Determine if the user is the initiator or the moderator
        $viewAs = $withdrawalRequest->moderator_account_id === $userId
            ? 'moderator'
            : 'requestor';

        // Tag the view_as field
        $withdrawalRequest->setAttribute('view_as', $viewAs);

        return Inertia::render('Player/wallet/WithdrawalDetails', [
            'withdrawalRequest' => $withdrawalRequest
        ]);
    }

    public function confirmReceipt($id): RedirectResponse
    {
        $request = WithdrawalRequest::with('transaction')->findOrFail($id);

        // ✅ Ensure only the initiator can confirm
        if (auth()->id() !== $request->initiator) {
            abort(403, 'Unauthorized');
        }

        DB::transaction(function () use ($request) {
            $transaction = $request->transaction;

            // ✅ Mark transaction as complete
            $transaction->update([
                'confirmation_status' => 1,
                'transaction_stage' => 'confirmed',
                'transaction_complete_status' => 1,
            ]);

            // ✅ Mark request as completed
            $request->update([
                'request_status' => 'completed',
            ]);

            // ✅ Update user balances
            $initiator = User::findOrFail($transaction->transaction_origin);
            $moderator = User::findOrFail($transaction->transaction_destination);

            $initiator->balance -= $transaction->amount;
            $moderator->balance += $transaction->amount;

            $initiator->save();
            $moderator->save();
        });

        return redirect()->route('wallet.withdrawal-details', $id)
            ->with('success', 'Withdrawal confirmed, balances updated, and request marked as completed.');
    }



    public function markAsSent($id): RedirectResponse
    {
        $withdrawal = WithdrawalRequest::with('transaction')->findOrFail($id);

        if (auth()->id() !== $withdrawal->moderator_account_id) {
            abort(403, 'Unauthorized');
        }

        $withdrawal->transaction->update([
            'delivery_confirmation_status' => true,
            'transaction_stage' => 'marked_sent',
        ]);

        return redirect()->back()->with('success', 'Marked as sent.');
    }


}
