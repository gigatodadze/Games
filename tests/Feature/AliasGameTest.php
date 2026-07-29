<?php

namespace Tests\Feature;

use Tests\TestCase;

class AliasGameTest extends TestCase
{
    public function test_home_screen_describes_the_alias_game(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('ალიასი')
            ->assertSee('ახსენი სიტყვა');
    }

    public function test_a_team_turn_can_be_played_and_reviewed(): void
    {
        $this->postJson('/alias/start', [
            'teams' => ['მთვარე', 'მზე'],
            'duration' => 60,
            'mode' => 'rounds',
            'rounds' => 1,
            'targetScore' => 30,
            'skipPenalty' => -1,
            'category' => 'easy',
        ])
            ->assertOk()
            ->assertJsonPath('state.phase', 'handoff')
            ->assertJsonPath('state.teams.0.name', 'მთვარე');

        $this->postJson('/alias/turn/start')
            ->assertOk()
            ->assertJsonPath('state.phase', 'playing');

        $this->postJson('/alias/word/mark', ['result' => 'correct'])
            ->assertOk()
            ->assertJsonPath('state.turnScore', 1);

        $this->postJson('/alias/turn/finish')
            ->assertOk()
            ->assertJsonPath('state.phase', 'summary')
            ->assertJsonPath('state.teams.0.score', 1);

        $this->postJson('/alias/turn/review', ['index' => 0, 'result' => 'skipped'])
            ->assertOk()
            ->assertJsonPath('state.turnScore', -1)
            ->assertJsonPath('state.teams.0.score', -1);

        $this->postJson('/alias/next')
            ->assertOk()
            ->assertJsonPath('state.currentTeam', 1)
            ->assertJsonPath('state.phase', 'handoff');
    }
}
