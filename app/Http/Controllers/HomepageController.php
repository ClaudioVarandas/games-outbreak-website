<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        $games = Game::with('genres', 'themes')
            ->whereBetween('first_release_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereIn('category', [0, 2 , 8])
            ->where([
                'is_parent' => 1,
                'is_featured' => 1,
            ])
            ->orderBy('first_release_date')
            ->orderBy('name')
            ->get();

        $weekGames = Game::with('genres', 'themes')
            ->whereBetween('first_release_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereIn('category', [0, 2, 8])
            ->where([
                'is_parent' => 1
            ])
            ->orderBy('first_release_date', 'desc')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->first_release_date)->format('d-m-Y');
            });

        return view('home', compact('games', 'weekGames'));
    }
}
