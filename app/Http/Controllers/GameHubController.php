<?php

namespace App\Http\Controllers;

use App\Data\GeorgianWords;
use App\Services\AliasGame;
use App\Services\NamiokobanaGame;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameHubController extends Controller
{
    public function __construct(
        private readonly AliasGame $alias,
        private readonly NamiokobanaGame $namiokobana,
    ) {}

    public function index(Request $request): View
    {
        $aliasState = $request->session()->get('alias_game', []);
        $namiState = $request->session()->get('namiokobana_game', []);

        return view('game', [
            'bootstrap' => [
                'aliasGame' => $aliasState === [] ? null : $this->alias->publicState($aliasState),
                'namiokobanaGame' => $namiState === [] ? null : $this->namiokobana->publicState($namiState),
                'categories' => GeorgianWords::metadata(),
            ],
        ]);
    }
}
