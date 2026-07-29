<?php

namespace Tests\Unit;

use App\Services\NamiokobanaGame;
use PHPUnit\Framework\TestCase;

class NamiokobanaGameTest extends TestCase
{
    private NamiokobanaGame $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->game = new NamiokobanaGame;
    }

    public function test_it_starts_with_two_teams_and_waits_for_an_opponent_word(): void
    {
        $state = $this->game->start(
            ['მთვარე', 'მზე'],
            ['duration' => 45, 'rounds' => 3],
        );

        $this->assertSame(NamiokobanaGame::PHASE_WORD_ENTRY, $state['phase']);
        $this->assertSame('მთვარე', $state['teams'][0]['name']);
        $this->assertSame(45, $state['settings']['duration']);
    }

    public function test_secret_word_is_hidden_until_the_explainer_reveals_it(): void
    {
        $state = $this->game->start(['ერთი', 'ორი']);
        $state = $this->game->setSecretWord($state, 'ქოლგა');

        $this->assertSame('', $this->game->publicState($state)['secretWord']);

        $state = $this->game->reveal($state);

        $this->assertSame('ქოლგა', $this->game->publicState($state)['secretWord']);
    }

    public function test_app_mode_chooses_hidden_words_and_skips_word_entry(): void
    {
        $state = $this->game->start(
            ['ერთი', 'ორი'],
            ['rounds' => 1, 'wordSource' => 'app'],
            ['ქოლგა', 'მატარებელი'],
        );

        $this->assertSame(NamiokobanaGame::PHASE_HANDOFF, $state['phase']);
        $this->assertSame('app', $this->game->publicState($state)['settings']['wordSource']);
        $this->assertSame('', $this->game->publicState($state)['secretWord']);

        $state = $this->game->reveal($state);
        $this->assertSame('ქოლგა', $this->game->publicState($state)['secretWord']);

        $state = $this->game->beginGuessing($state, 1000.0);
        $state = $this->game->finishTurn($state, 'missed', 1001.0);
        $state = $this->game->nextTurn($state);

        $this->assertSame(NamiokobanaGame::PHASE_HANDOFF, $state['phase']);
        $state = $this->game->reveal($state);
        $this->assertSame('მატარებელი', $this->game->publicState($state)['secretWord']);
    }

    public function test_correct_guess_scores_a_point_and_rotates_teams(): void
    {
        $state = $this->game->start(['ერთი', 'ორი'], ['rounds' => 1]);
        $state = $this->game->setSecretWord($state, 'ქოლგა');
        $state = $this->game->reveal($state);
        $state = $this->game->beginGuessing($state, 1000.0);
        $state = $this->game->finishTurn($state, 'correct', 1005.0);

        $this->assertSame(1, $state['teams'][0]['score']);
        $this->assertSame(NamiokobanaGame::PHASE_SUMMARY, $state['phase']);

        $state = $this->game->nextTurn($state);

        $this->assertSame(1, $state['current_team']);
        $this->assertSame(NamiokobanaGame::PHASE_WORD_ENTRY, $state['phase']);
    }

    public function test_timeout_forces_a_missed_result(): void
    {
        $state = $this->game->start(['ერთი', 'ორი'], ['duration' => 30]);
        $state = $this->game->setSecretWord($state, 'ქოლგა');
        $state = $this->game->reveal($state);
        $state = $this->game->beginGuessing($state, 1000.0);
        $state = $this->game->finishTurn($state, 'correct', 1030.0);

        $this->assertSame('missed', $state['turn']['result']);
        $this->assertSame(0, $state['teams'][0]['score']);
    }

    public function test_it_finishes_after_both_teams_receive_equal_turns(): void
    {
        $state = $this->game->start(['ერთი', 'ორი'], ['rounds' => 1]);

        foreach (['correct', 'missed'] as $index => $result) {
            $state = $this->game->setSecretWord($state, $index === 0 ? 'ქოლგა' : 'მატარებელი');
            $state = $this->game->reveal($state);
            $state = $this->game->beginGuessing($state, 1000.0 + $index * 10);
            $state = $this->game->finishTurn($state, $result, 1001.0 + $index * 10);
            $state = $this->game->nextTurn($state);
        }

        $public = $this->game->publicState($state);
        $this->assertSame(NamiokobanaGame::PHASE_FINISHED, $state['phase']);
        $this->assertSame(['ერთი'], $public['winnerNames']);
    }
}
