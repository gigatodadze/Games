<?php

namespace App\Http\Controllers;

use App\Data\GeorgianWords;
use App\Services\AliasGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;
use Native\Mobile\Facades\Device;

class AliasGameController extends Controller
{
    private const SESSION_KEY = 'alias_game';

    public function __construct(private readonly AliasGame $game) {}

    public function index(Request $request): View
    {
        $state = $request->session()->get(self::SESSION_KEY, []);

        return view('game', [
            'bootstrap' => [
                'currentGame' => $state === [] ? null : $this->game->publicState($state),
                'categories' => GeorgianWords::metadata(),
            ],
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teams' => ['required', 'array', 'min:2', 'max:4'],
            'teams.*' => ['required', 'string', 'max:24'],
            'duration' => ['required', Rule::in([45, 60, 90])],
            'mode' => ['required', Rule::in(['rounds', 'points'])],
            'rounds' => ['required', 'integer', 'min:1', 'max:10'],
            'targetScore' => ['required', 'integer', 'min:10', 'max:100'],
            'skipPenalty' => ['required', Rule::in([0, -1, '0', '-1'])],
            'category' => ['required', Rule::in(array_keys(GeorgianWords::categories()))],
        ]);

        try {
            $state = $this->game->start($validated['teams'], $validated);
        } catch (InvalidArgumentException $exception) {
            return $this->gameError($exception);
        }

        $request->session()->put(self::SESSION_KEY, $state);

        return $this->stateResponse($state);
    }

    public function startTurn(Request $request): JsonResponse
    {
        return $this->mutate($request, fn (array $state): array => $this->game->startTurn($state));
    }

    public function mark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'result' => ['required', Rule::in(['correct', 'skipped'])],
        ]);

        $response = $this->mutate(
            $request,
            fn (array $state): array => $this->game->markWord($state, $validated['result']),
        );

        Device::vibrate();

        return $response;
    }

    public function finishTurn(Request $request): JsonResponse
    {
        return $this->mutate($request, fn (array $state): array => $this->game->finishTurn($state));
    }

    public function review(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'index' => ['required', 'integer', 'min:0'],
            'result' => ['required', Rule::in(['correct', 'skipped'])],
        ]);

        return $this->mutate(
            $request,
            fn (array $state): array => $this->game->reviewWord(
                $state,
                (int) $validated['index'],
                $validated['result'],
            ),
        );
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
            'teams_invalid' => 'დაამატე მინიმუმ ორი გუნდი.',
            'settings_invalid' => 'თამაშის პარამეტრები არასწორია.',
            'deck_invalid' => 'არჩეულ ლექსიკონში სიტყვები ვერ მოიძებნა.',
            'phase_invalid' => 'ეს მოქმედება ახლა შეუძლებელია.',
            'result_invalid' => 'პასუხის ტიპი არასწორია.',
            'review_invalid' => 'ამ სიტყვის შედეგი ვერ შეიცვალა.',
        ];

        return response()->json([
            'message' => $messages[$exception->getMessage()] ?? 'რაღაც ვერ გამოვიდა. სცადე თავიდან.',
            'code' => $exception->getMessage(),
        ], 409);
    }
}
