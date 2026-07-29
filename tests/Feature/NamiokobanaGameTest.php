<?php

namespace Tests\Feature;

use Tests\TestCase;

class NamiokobanaGameTest extends TestCase
{
    public function test_home_screen_offers_both_games(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('აირჩიე თამაში')
            ->assertSee('ალიასი')
            ->assertSee('ნამიოკობანა');
    }

    public function test_a_namiokobana_turn_can_be_played(): void
    {
        $this->postJson('/namiokobana/start', [
            'teams' => ['მთვარე', 'მზე'],
            'duration' => 30,
            'rounds' => 1,
            'wordSource' => 'players',
        ])
            ->assertOk()
            ->assertJsonPath('state.phase', 'word_entry')
            ->assertJsonPath('state.setterTeam', 1);

        $this->postJson('/namiokobana/word', ['word' => 'ქოლგა'])
            ->assertOk()
            ->assertJsonPath('state.phase', 'handoff')
            ->assertJsonPath('state.secretWord', '');

        $this->postJson('/namiokobana/reveal')
            ->assertOk()
            ->assertJsonPath('state.phase', 'reveal')
            ->assertJsonPath('state.secretWord', 'ქოლგა');

        $this->postJson('/namiokobana/begin')
            ->assertOk()
            ->assertJsonPath('state.phase', 'guessing')
            ->assertJsonPath('state.secretWord', 'ქოლგა');

        $this->postJson('/namiokobana/finish', ['result' => 'correct'])
            ->assertOk()
            ->assertJsonPath('state.phase', 'summary')
            ->assertJsonPath('state.teams.0.score', 1)
            ->assertJsonPath('state.secretWord', 'ქოლგა');
    }

    public function test_the_app_can_choose_a_hidden_word_automatically(): void
    {
        $this->postJson('/namiokobana/start', [
            'teams' => ['მთვარე', 'მზე'],
            'duration' => 30,
            'rounds' => 1,
            'wordSource' => 'app',
        ])
            ->assertOk()
            ->assertJsonPath('state.phase', 'handoff')
            ->assertJsonPath('state.settings.wordSource', 'app')
            ->assertJsonPath('state.secretWord', '');

        $response = $this->postJson('/namiokobana/reveal')
            ->assertOk()
            ->assertJsonPath('state.phase', 'reveal');

        $this->assertNotSame('', $response->json('state.secretWord'));
    }
}
