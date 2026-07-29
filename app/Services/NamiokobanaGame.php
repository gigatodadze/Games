<?php

namespace App\Services;

use App\Data\GeorgianWords;
use InvalidArgumentException;

class NamiokobanaGame
{
    public const PHASE_WORD_ENTRY = 'word_entry';

    public const PHASE_HANDOFF = 'handoff';

    public const PHASE_REVEAL = 'reveal';

    public const PHASE_GUESSING = 'guessing';

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
        $duration = (int) ($settings['duration'] ?? 30);
        $rounds = (int) ($settings['rounds'] ?? 5);
        $wordSource = (string) ($settings['wordSource'] ?? 'players');

        if (count($names) !== 2 || in_array('', $names, true)) {
            throw new InvalidArgumentException('teams_invalid');
        }

        if (! in_array($duration, [30, 45, 60], true)
            || $rounds < 1 || $rounds > 10
            || ! in_array($wordSource, ['players', 'app'], true)) {
            throw new InvalidArgumentException('settings_invalid');
        }

        $words = [];

        if ($wordSource === 'app') {
            $words = array_values(array_unique(array_filter(
                $deck ?? $this->allWords(),
                fn (mixed $word): bool => is_string($word) && trim($word) !== '',
            )));

            if (count($words) < 2) {
                throw new InvalidArgumentException('deck_invalid');
            }

            if ($deck === null) {
                shuffle($words);
            }
        }

        $state = [
            'id' => bin2hex(random_bytes(6)),
            'active' => true,
            'phase' => $wordSource === 'app' ? self::PHASE_HANDOFF : self::PHASE_WORD_ENTRY,
            'teams' => array_map(
                fn (string $name): array => ['name' => $name, 'score' => 0],
                $names,
            ),
            'current_team' => 0,
            'round' => 1,
            'settings' => [
                'duration' => $duration,
                'rounds' => $rounds,
                'word_source' => $wordSource,
            ],
            'deck' => $words,
            'deck_index' => 0,
            'turn' => null,
            'history' => [],
        ];

        return $wordSource === 'app' ? $this->prepareAutomaticTurn($state) : $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function setSecretWord(array $state, string $word): array
    {
        $this->assertPhase($state, self::PHASE_WORD_ENTRY);
        $word = preg_replace('/\s+/u', ' ', trim($word)) ?? '';

        if (! $this->validWord($word)) {
            throw new InvalidArgumentException('word_invalid');
        }

        $state['turn'] = [
            'team' => (int) $state['current_team'],
            'round' => (int) $state['round'],
            'secret_word' => $word,
            'result' => null,
        ];
        $state['phase'] = self::PHASE_HANDOFF;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function reveal(array $state): array
    {
        $this->assertPhase($state, self::PHASE_HANDOFF);
        $state['phase'] = self::PHASE_REVEAL;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function beginGuessing(array $state, ?float $now = null): array
    {
        $this->assertPhase($state, self::PHASE_REVEAL);
        $startedAt = $now ?? microtime(true);
        $state['turn']['started_at'] = $startedAt;
        $state['turn']['ends_at'] = $startedAt + (int) $state['settings']['duration'];
        $state['phase'] = self::PHASE_GUESSING;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function finishTurn(array $state, string $result, ?float $now = null): array
    {
        if (($state['phase'] ?? null) === self::PHASE_SUMMARY) {
            return $state;
        }

        $this->assertPhase($state, self::PHASE_GUESSING);

        if (! in_array($result, ['correct', 'missed'], true)) {
            throw new InvalidArgumentException('result_invalid');
        }

        $finishedAt = $now ?? microtime(true);

        if ($finishedAt >= (float) $state['turn']['ends_at']) {
            $result = 'missed';
        }

        $state['turn']['result'] = $result;
        $state['turn']['points'] = $result === 'correct' ? 1 : 0;
        $state['turn']['finished_at'] = $finishedAt;
        $state['teams'][$state['current_team']]['score'] += $state['turn']['points'];
        $state['phase'] = self::PHASE_SUMMARY;

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function nextTurn(array $state): array
    {
        $this->assertPhase($state, self::PHASE_SUMMARY);
        $lastTeam = (int) $state['current_team'] === 1;

        $state['history'][] = [
            'team' => (int) $state['current_team'],
            'round' => (int) $state['round'],
            'word' => (string) $state['turn']['secret_word'],
            'result' => (string) $state['turn']['result'],
        ];

        if ($lastTeam && (int) $state['round'] >= (int) $state['settings']['rounds']) {
            $state['active'] = false;
            $state['phase'] = self::PHASE_FINISHED;
            $state['finished_at'] = microtime(true);

            return $state;
        }

        if ($lastTeam) {
            $state['current_team'] = 0;
            $state['round']++;
        } else {
            $state['current_team'] = 1;
        }

        $state['turn'] = null;

        if (($state['settings']['word_source'] ?? 'players') === 'app') {
            return $this->prepareAutomaticTurn($state);
        }

        $state['phase'] = self::PHASE_WORD_ENTRY;

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
        $phase = (string) $state['phase'];
        $turn = is_array($state['turn'] ?? null) ? $state['turn'] : null;
        $teams = array_map(
            fn (array $team, int $index): array => [
                'index' => $index,
                'name' => (string) $team['name'],
                'score' => (int) $team['score'],
            ],
            $state['teams'],
            array_keys($state['teams']),
        );
        $winnerNames = [];

        if ($phase === self::PHASE_FINISHED) {
            $bestScore = max(array_column($teams, 'score'));
            $winnerNames = array_values(array_map(
                fn (array $team): string => $team['name'],
                array_filter($teams, fn (array $team): bool => $team['score'] === $bestScore),
            ));
        }

        return [
            'id' => (string) $state['id'],
            'active' => (bool) $state['active'],
            'phase' => $phase,
            'teams' => $teams,
            'currentTeam' => (int) $state['current_team'],
            'setterTeam' => ((int) $state['current_team'] + 1) % 2,
            'round' => (int) $state['round'],
            'settings' => [
                'duration' => (int) $state['settings']['duration'],
                'rounds' => (int) $state['settings']['rounds'],
                'wordSource' => (string) ($state['settings']['word_source'] ?? 'players'),
            ],
            'secretWord' => in_array($phase, [self::PHASE_REVEAL, self::PHASE_GUESSING, self::PHASE_SUMMARY], true)
                ? (string) ($turn['secret_word'] ?? '')
                : '',
            'result' => $phase === self::PHASE_SUMMARY ? (string) ($turn['result'] ?? '') : '',
            'remainingSeconds' => $phase === self::PHASE_GUESSING
                ? max(0, (int) ceil((float) $turn['ends_at'] - $timestamp))
                : 0,
            'endsAt' => $phase === self::PHASE_GUESSING
                ? (int) round((float) $turn['ends_at'] * 1000)
                : null,
            'history' => array_values($state['history']),
            'winnerNames' => $winnerNames,
            'isTie' => count($winnerNames) > 1,
        ];
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

    private function validWord(string $word): bool
    {
        $length = mb_strlen($word, 'UTF-8');

        return $length >= 1
            && $length <= 32
            && (bool) preg_match("/^[\p{L}\p{M}][\p{L}\p{M}\s\-’']*$/u", $word);
    }

    private function cleanName(string $name): string
    {
        return mb_substr(preg_replace('/\s+/u', ' ', trim($name)) ?? '', 0, 24);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function prepareAutomaticTurn(array $state): array
    {
        $state['turn'] = [
            'team' => (int) $state['current_team'],
            'round' => (int) $state['round'],
            'secret_word' => $this->drawWord($state),
            'result' => null,
        ];
        $state['phase'] = self::PHASE_HANDOFF;

        return $state;
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
     * @return list<string>
     */
    private function allWords(): array
    {
        return array_values(array_merge(
            ...array_values(array_map(
                fn (array $category): array => $category['words'],
                GeorgianWords::categories(),
            )),
        ));
    }
}
