<?php

namespace App\Http\Controllers;

use App\Events\UserJoined;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        broadcast(new UserJoined(Auth::user()));
        $user = Auth::user();
        return Inertia::render('Dashboard',[
            'user' => $user,
        ]);
    }
}
