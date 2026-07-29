<?php

namespace App\Services;

use App\Data\GeorgianWords;
use InvalidArgumentException;

class AliasGame
{
    public const PHASE_HANDOFF = 'handoff';

    public const PHASE_PLAYING = 'playing';

    public const PHASE_SUMMARY = 'summary';

    public const PHASE_FINISHED = 'finished';

    /**
     * @param  list<string>  $teamNames
     * @param  array<string, mixed>  $settings
     * @param  list<string>|null  $deck
     * @return array<string, mixed>
     */
    public function start(array $teamNames, array $settings = [], ?array $deck = null): array
    {
        $names = array_values(array_map(
            fn (string $name): string => $this->cleanName($name),
            $teamNames,
        ));

        if (count($names) < 2 || count($names) > 4 || in_array('', $names, true)) {
            throw new InvalidArgumentException('teams_invalid');
        }

        $duration = (int) ($settings['duration'] ?? 60);
        $mode = (string) ($settings['mode'] ?? 'rounds');
        $rounds = (int) ($settings['rounds'] ?? 3);
        $targetScore = (int) ($settings['targetScore'] ?? 30);
        $skipPenalty = (int) ($settings['skipPenalty'] ?? 0);
        $category = (string) ($settings['category'] ?? 'daily');

        if (! in_array($duration, [45, 60, 90], true)
            || ! in_array($mode, ['rounds', 'points'], true)
            || $rounds < 1 || $rounds > 10
            || $targetScore < 10 || $targetScore > 100
            || ! in_array($skipPenalty, [0, -1], true)
            || ! array_key_exists($category, GeorgianWords::categories())) {
            throw new InvalidArgumentException('settings_invalid');
        }

        $words = array_values(array_unique(array_filter(
            $deck ?? GeorgianWords::words($category),
            fn (mixed $word): bool => is_string($word) && trim($word) !== '',
        )));

        if (count($words) < 2) {
            throw new InvalidArgumentException('deck_invalid');
        }

        if ($deck === null) {
            shuffle($words);
        }

        return [
            'id' => bin2hex(random_bytes(6)),
            'active' => true,
            'phase' => self::PHASE_HANDOFF,
            'teams' => array_map(
                fn (string $name): array => ['name' => $name, 'score' => 0],
                $names,
            ),
            'current_team' => 0,
            'round' => 1,
            'settings' => [
                'duration' => $duration,
                'mode' => $mode,
                'rounds' => $rounds,
                'target_score' => $targetScore,
                'skip_penalty' => $skipPenalty,
                'category' => $category,
            ],
            'deck' => $words,
            'deck_index' => 0,
            'turn' => null,
            'completed_turns' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function startTurn(array $state, ?float $now = null): array
    {
        $this->assertPhase($state, self::PHASE_HANDOFF);
        $startedAt = $now ?? microtime(true);
        $word = $this->drawWord($state);

        $state['phase'] = self::PHASE_PLAYING;
        $state['turn'] = [
            'team' => (int) $state['current_team'],
            'round' => (int) $state['round'],
            'started_at' => $startedAt,
            'ends_at' => $startedAt + (int) $state['settings']['duration'],
            'current_word' => $word,
            'score' => 0,
            'items' => [],
        ];

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function markWord(array $state, string $result, ?float $now = null): array
    {
        $this->assertPhase($state, self::PHASE_PLAYING);

        if (($now ?? microtime(true)) >= (float) $state['turn']['ends_at']) {
            return $this->finishTurn($state, $now);
        }

        if (! in_array($result, ['correct', 'skipped'], true)) {
            throw new InvalidArgumentException('result_invalid');
        }

        $points = $result === 'correct' ? 1 : (int) $state['settings']['skip_penalty'];
        $state['turn']['items'][] = [
            'word' => (string) $state['turn']['current_word'],
            'result' => $result,
            'points' => $points,
        ];
        $state['turn']['score'] += $points;
        $state['turn']['current_word'] = $this->drawWord($state);

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function finishTurn(array $state, ?float $now = null): array
    {
        if (($state['phase'] ?? null) === self::PHASE_SUMMARY) {
            return $state;
        }

        $this->assertPhase($state, self::PHASE_PLAYING);
        $teamIndex = (int) $state['current_team'];
        $state['teams'][$teamIndex]['score'] += (int) $state['turn']['score'];
        $state['turn']['finished_at'] = $now ?? microtime(true);
        $state['phase'] = self::PHASE_SUMMARY;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function reviewWord(array $state, int $index, string $result): array
    {
        $this->assertPhase($state, self::PHASE_SUMMARY);

        if (! in_array($result, ['correct', 'skipped'], true)
            || ! isset($state['turn']['items'][$index])) {
            throw new InvalidArgumentException('review_invalid');
        }

        $oldPoints = (int) $state['turn']['items'][$index]['points'];
        $newPoints = $result === 'correct' ? 1 : (int) $state['settings']['skip_penalty'];
        $difference = $newPoints - $oldPoints;
        $teamIndex = (int) $state['current_team'];

        $state['turn']['items'][$index]['result'] = $result;
        $state['turn']['items'][$index]['points'] = $newPoints;
        $state['turn']['score'] += $difference;
        $state['teams'][$teamIndex]['score'] += $difference;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function nextTurn(array $state): array
    {
        $this->assertPhase($state, self::PHASE_SUMMARY);
        $lastTeam = (int) $state['current_team'] === count($state['teams']) - 1;

        $state['completed_turns'][] = [
            'team' => (int) $state['current_team'],
            'round' => (int) $state['round'],
            'score' => (int) $state['turn']['score'],
            'correct' => count(array_filter(
                $state['turn']['items'],
                fn (array $item): bool => $item['result'] === 'correct',
            )),
        ];

        if ($lastTeam && $this->gameShouldFinish($state)) {
            $state['active'] = false;
            $state['phase'] = self::PHASE_FINISHED;
            $state['finished_at'] = microtime(true);

            return $state;
        }

        if ($lastTeam) {
            $state['current_team'] = 0;
            $state['round']++;
        } else {
            $state['current_team']++;
        }

        $state['phase'] = self::PHASE_HANDOFF;
        $state['turn'] = null;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function publicState(array $state, ?float $now = null): array
    {
        if ($state === []) {
            return [];
        }

        $timestamp = $now ?? microtime(true);
        $turn = is_array($state['turn'] ?? null) ? $state['turn'] : null;
        $phase = (string) ($state['phase'] ?? self::PHASE_FINISHED);
        $teams = array_map(
            fn (array $team, int $index): array => [
                'index' => $index,
                'name' => (string) $team['name'],
                'score' => (int) $team['score'],
            ],
            $state['teams'] ?? [],
            array_keys($state['teams'] ?? []),
        );

        $winnerNames = [];

        if ($phase === self::PHASE_FINISHED && $teams !== []) {
            $best = max(array_column($teams, 'score'));
            $winnerNames = array_values(array_map(
                fn (array $team): string => $team['name'],
                array_filter($teams, fn (array $team): bool => $team['score'] === $best),
            ));
        }

        return [
            'id' => (string) ($state['id'] ?? ''),
            'active' => (bool) ($state['active'] ?? false),
            'phase' => $phase,
            'teams' => $teams,
            'currentTeam' => (int) ($state['current_team'] ?? 0),
            'round' => (int) ($state['round'] ?? 1),
            'settings' => [
                'duration' => (int) ($state['settings']['duration'] ?? 60),
                'mode' => (string) ($state['settings']['mode'] ?? 'rounds'),
                'rounds' => (int) ($state['settings']['rounds'] ?? 3),
                'targetScore' => (int) ($state['settings']['target_score'] ?? 30),
                'skipPenalty' => (int) ($state['settings']['skip_penalty'] ?? 0),
                'category' => (string) ($state['settings']['category'] ?? 'daily'),
            ],
            'currentWord' => $phase === self::PHASE_PLAYING ? (string) ($turn['current_word'] ?? '') : '',
            'turnScore' => (int) ($turn['score'] ?? 0),
            'turnItems' => array_values($turn['items'] ?? []),
            'remainingSeconds' => $phase === self::PHASE_PLAYING
                ? max(0, (int) ceil((float) $turn['ends_at'] - $timestamp))
                : 0,
            'endsAt' => $phase === self::PHASE_PLAYING
                ? (int) round((float) $turn['ends_at'] * 1000)
                : null,
            'completedTurns' => array_values($state['completed_turns'] ?? []),
            'winnerNames' => $winnerNames,
            'isTie' => count($winnerNames) > 1,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function gameShouldFinish(array $state): bool
    {
        if ($state['settings']['mode'] === 'points') {
            return max(array_column($state['teams'], 'score')) >= (int) $state['settings']['target_score'];
        }

        return (int) $state['round'] >= (int) $state['settings']['rounds'];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function drawWord(array &$state): string
    {
        if ((int) $state['deck_index'] >= count($state['deck'])) {
            shuffle($state['deck']);
            $state['deck_index'] = 0;
        }

        $word = (string) $state['deck'][$state['deck_index']];
        $state['deck_index']++;

        return $word;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function assertPhase(array $state, string $phase): void
    {
        if (! ($state['active'] ?? false) || ($state['phase'] ?? null) !== $phase) {
            throw new InvalidArgumentException('phase_invalid');
        }
    }

    private function cleanName(string $name): string
    {
        return mb_substr(preg_replace('/\s+/u', ' ', trim($name)) ?? '', 0, 24);
    }
}
