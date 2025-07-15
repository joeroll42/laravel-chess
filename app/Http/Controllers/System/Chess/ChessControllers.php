<?php

namespace App\Http\Controllers\System\Chess;

use App\Classes\Chess\User;
use App\Http\Controllers\Controller;
use App\Models\User as UserModel;
use Illuminate\Http\Request;

class ChessControllers extends Controller
{
    /**
     * Entry point to demo archives + this month's games.
     */
    public function entry(Request $request)
    {
        $username = 'akingvonfan';
        $user     = UserModel::where('chess_com_link', $username)->firstOrFail();


        // 1) archives in ['year'=>..., 'month'=>...] form
        $archives = User::getArchives($user);

        // 2) this month’s games
        $games    = User::getMonthlyGames($user,start_date:'2025-06-01',end_date:'2025-07-13',start_time:'00:00:00',end_time:'23:59:59' );

        return response()->json([
            'user' => User::fromApiResponse($user),
            'archives' => $archives,
            'games'    => $games,
        ]);
    }
}
