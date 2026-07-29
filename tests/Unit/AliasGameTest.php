<?php

namespace Tests\Unit;

use App\Services\AliasGame;
use PHPUnit\Framework\TestCase;

class AliasGameTest extends TestCase
{
    private AliasGame $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->game = new AliasGame;
    }

    public function test_it_starts_with_teams_and_waits_for_the_first_explainer(): void
    {
        $state = $this->game->start(
            ['მთვარე', 'მზე'],
            ['duration' => 45, 'rounds' => 3],
            ['სახლი', 'ზღვა'],
        );

        $this->assertSame(AliasGame::PHASE_HANDOFF, $state['phase']);
        $this->assertSame('მთვარე', $state['teams'][0]['name']);
        $this->assertSame(45, $state['settings']['duration']);
        $this->assertSame(0, $state['teams'][0]['score']);
    }

    public function test_correct_and_skipped_words_change_the_turn_score(): void
    {
        $state = $this->game->start(
            ['ერთი', 'ორი'],
            ['skipPenalty' => -1],
            ['სახლი', 'ზღვა', 'წიგნი'],
        );
        $state = $this->game->startTurn($state, 1000.0);
        $state = $this->game->markWord($state, 'correct', 1001.0);
        $state = $this->game->markWord($state, 'skipped', 1002.0);
        $state = $this->game->markWord($state, 'correct', 1003.0);
        $state = $this->game->finishTurn($state, 1004.0);

        $this->assertSame(1, $state['turn']['score']);
        $this->assertSame(1, $state['teams'][0]['score']);
        $this->assertCount(3, $state['turn']['items']);
    }

    public function test_a_late_answer_finishes_the_turn_without_counting_the_word(): void
    {
        $state = $this->game->start(
            ['ერთი', 'ორი'],
            ['duration' => 45],
            ['სახლი', 'ზღვა'],
        );
        $state = $this->game->startTurn($state, 1000.0);
        $state = $this->game->markWord($state, 'correct', 1045.0);

        $this->assertSame(AliasGame::PHASE_SUMMARY, $state['phase']);
        $this->assertSame(0, $state['turn']['score']);
        $this->assertSame([], $state['turn']['items']);
    }

    public function test_reviewing_a_word_corrects_the_team_score(): void
    {
        $state = $this->game->start(
            ['ერთი', 'ორი'],
            ['skipPenalty' => -1],
            ['სახლი', 'ზღვა'],
        );
        $state = $this->game->startTurn($state, 1000.0);
        $state = $this->game->markWord($state, 'skipped', 1001.0);
        $state = $this->game->finishTurn($state, 1002.0);
        $state = $this->game->reviewWord($state, 0, 'correct');

        $this->assertSame(1, $state['turn']['score']);
        $this->assertSame(1, $state['teams'][0]['score']);
        $this->assertSame('correct', $state['turn']['items'][0]['result']);
    }

    public function test_it_rotates_teams_and_declares_a_winner_after_equal_rounds(): void
    {
        $state = $this->game->start(
            ['ერთი', 'ორი'],
            ['rounds' => 1],
            ['სახლი', 'ზღვა', 'წიგნი', 'მთა'],
        );

        $state = $this->game->startTurn($state, 1000.0);
        $state = $this->game->markWord($state, 'correct', 1001.0);
        $state = $this->game->finishTurn($state, 1002.0);
        $state = $this->game->nextTurn($state);

        $this->assertSame(1, $state['current_team']);
        $this->assertSame(AliasGame::PHASE_HANDOFF, $state['phase']);

        $state = $this->game->startTurn($state, 1010.0);
        $state = $this->game->finishTurn($state, 1011.0);
        $state = $this->game->nextTurn($state);
        $public = $this->game->publicState($state, 1012.0);

        $this->assertSame(AliasGame::PHASE_FINISHED, $state['phase']);
        $this->assertSame(['ერთი'], $public['winnerNames']);
    }
}
