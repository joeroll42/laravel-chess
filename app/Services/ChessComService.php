<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class ChessComService
{
    protected string $baseUrl = 'https://api.chess.com/pub';

    public function fetchMonthlyGames(Challenge $challenge, int $year, int $month): Collection
    {
        dd($challenge);
    }

}
