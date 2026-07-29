<?php

namespace App\Http\Controllers;

use App\Services\NamiokobanaGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Native\Mobile\Facades\Device;

class NamiokobanaGameController extends Controller
{
    private const SESSION_KEY = 'namiokobana_game';

    public function __construct(private readonly NamiokobanaGame $game) {}

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teams' => ['required', 'array', 'size:2'],
            'teams.*' => ['required', 'string', 'max:24'],
            'duration' => ['required', Rule::in([30, 45, 60])],
            'rounds' => ['required', 'integer', 'min:1', 'max:10'],
            'wordSource' => ['sometimes', Rule::in(['players', 'app'])],
        ]);

        $validated['wordSource'] ??= 'players';

        try {
            $state = $this->game->start($validated['teams'], $validated);
        } catch (InvalidArgumentException $exception) {
            return $this->gameError($exception);
        }

        $request->session()->forget('alias_game');
        $request->session()->put(self::SESSION_KEY, $state);

        return $this->stateResponse($state);
    }

    public function word(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:32'],
        ]);

        return $this->mutate(
            $request,
            fn (array $state): array => $this->game->setSecretWord($state, $validated['word']),
        );
    }

    public function reveal(Request $request): JsonResponse
    {
        return $this->mutate($request, fn (array $state): array => $this->game->reveal($state));
    }

    public function begin(Request $request): JsonResponse
    {
        return $this->mutate($request, fn (array $state): array => $this->game->beginGuessing($state));
    }

    public function finish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'result' => ['required', Rule::in(['correct', 'missed'])],
        ]);

        $response = $this->mutate(
            $request,
            fn (array $state): array => $this->game->finishTurn($state, $validated['result']),
        );

        if ($validated['result'] === 'correct') {
            Device::vibrate();
        }

        return $response;
    }

    public function next(Request $request): JsonResponse
    {
        return $this->mutate($request, fn (array $state): array => $this->game->nextTurn($state));
    }

    public function quit(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['state' => null]);
    }

    /**
     * @param  callable(array<string, mixed>): array<string, mixed>  $callback
     */
    private function mutate(Request $request, callable $callback): JsonResponse
    {
        try {
            $state = $callback($request->session()->get(self::SESSION_KEY, []));
        } catch (InvalidArgumentException $exception) {
            return $this->gameError($exception);
        }

        $request->session()->put(self::SESSION_KEY, $state);

        return $this->stateResponse($state);
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function stateResponse(array $state): JsonResponse
    {
        return response()->json(['state' => $this->game->publicState($state)]);
    }

    private function gameError(InvalidArgumentException $exception): JsonResponse
    {
        $messages = [
            'teams_invalid' => 'ნამიოკობანასთვის ორი გუნდი დაამატე.',
            'settings_invalid' => 'თამაშის პარამეტრები არასწორია.',
            'word_invalid' => 'შეიყვანე ერთი მოკლე სიტყვა ან ფრაზა.',
            'deck_invalid' => 'ავტომატური რეჟიმისთვის სიტყვები ვერ მოიძებნა.',
            'phase_invalid' => 'ეს მოქმედება ახლა შეუძლებელია.',
            'result_invalid' => 'პასუხის ტიპი არასწორია.',
        ];

        return response()->json([
            'message' => $messages[$exception->getMessage()] ?? 'რაღაც ვერ გამოვიდა. სცადე თავიდან.',
            'code' => $exception->getMessage(),
        ], 409);
    }
}
