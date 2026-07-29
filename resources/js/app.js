const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const bootstrap = window.GEORGIAN_GAMES ?? {};

if (document.querySelector('[data-app]')) {
    const screens = [...document.querySelectorAll('[data-screen]')];
    const aliasRules = document.querySelector('[data-rules]');
    const namiRules = document.querySelector('[data-nami-rules]');
    const aliasSetupForm = document.querySelector('[data-alias-setup-form]');
    const aliasTeamInputs = document.querySelector('[data-alias-team-inputs]');
    const aliasAddTeamButton = document.querySelector('[data-alias-add-team]');
    const aliasFormError = document.querySelector('[data-alias-form-error]');
    const aliasPlayMessage = document.querySelector('[data-alias-play-message]');
    const aliasSummaryError = document.querySelector('[data-alias-summary-error]');
    const namiSetupForm = document.querySelector('[data-nami-setup-form]');
    const namiSetupError = document.querySelector('[data-nami-setup-error]');
    const namiWordError = document.querySelector('[data-nami-word-error]');
    const namiPlayError = document.querySelector('[data-nami-play-error]');
    const namiSummaryError = document.querySelector('[data-nami-summary-error]');
    let aliasState = bootstrap.aliasGame ?? null;
    let namiState = bootstrap.namiokobanaGame ?? null;
    let aliasTeamNames = ['ლურჯები', 'ვარდისფრები'];
    let aliasTimerId = null;
    let namiTimerId = null;
    let aliasTimerFinishing = false;
    let namiTimerFinishing = false;
    let busy = false;

    const text = (selector, value) => {
        const element = document.querySelector(selector);
        if (element) element.textContent = value;
    };

    const showScreen = (name) => {
        screens.forEach((screen) => {
            const active = screen.dataset.screen === name;
            screen.classList.toggle('is-active', active);
            screen.setAttribute('aria-hidden', active ? 'false' : 'true');
            if (active && screen.classList.contains('screen--scroll')) screen.scrollTop = 0;
        });

        if (name !== 'alias-play') stopAliasTimer();
        if (name !== 'nami-guessing') stopNamiTimer();
    };

    const post = async (url, payload = {}) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const validationMessage = Object.values(data.errors ?? {}).flat()[0];
            const error = new Error(validationMessage ?? data.message ?? 'რაღაც ვერ გამოვიდა. სცადე თავიდან.');
            error.payload = data;
            throw error;
        }

        return data;
    };

    const withBusy = async (callback) => {
        if (busy) return;
        busy = true;
        document.body.classList.add('is-busy');

        try {
            await callback();
        } finally {
            busy = false;
            document.body.classList.remove('is-busy');
        }
    };

    const teamColorClass = (index) => `team-dot--${(index % 4) + 1}`;

    const renderScores = (container, gameState) => {
        if (!container || !gameState) return;
        container.replaceChildren();

        gameState.teams.forEach((team) => {
            const row = document.createElement('div');
            row.className = 'score-row';
            if (team.index === gameState.currentTeam && gameState.phase !== 'finished') {
                row.classList.add('is-current');
            }

            const identity = document.createElement('span');
            const dot = document.createElement('i');
            dot.className = `team-dot ${teamColorClass(team.index)}`;
            dot.textContent = team.index + 1;
            const name = document.createElement('strong');
            name.textContent = team.name;
            identity.append(dot, name);

            const score = document.createElement('b');
            score.textContent = `${team.score} ქულა`;
            row.append(identity, score);
            container.append(row);
        });
    };

    const confirmExit = () => window.confirm('ნამდვილად გინდა მიმდინარე თამაშიდან გასვლა?');

    const openSheet = (sheet) => {
        sheet?.classList.add('is-open');
        sheet?.setAttribute('aria-hidden', 'false');
    };

    const closeSheet = (sheet) => {
        sheet?.classList.remove('is-open');
        sheet?.setAttribute('aria-hidden', 'true');
    };

    // Alias
    const renderAliasTeamInputs = () => {
        if (!aliasTeamInputs) return;
        aliasTeamInputs.replaceChildren();

        aliasTeamNames.forEach((name, index) => {
            const row = document.createElement('div');
            row.className = 'team-input-row';

            const badge = document.createElement('span');
            badge.className = `team-dot ${teamColorClass(index)}`;
            badge.textContent = index + 1;

            const input = document.createElement('input');
            input.type = 'text';
            input.maxLength = 24;
            input.value = name;
            input.setAttribute('aria-label', `გუნდი ${index + 1}`);
            input.addEventListener('input', () => {
                aliasTeamNames[index] = input.value;
            });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'remove-team';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `${name} გუნდის წაშლა`);
            remove.hidden = aliasTeamNames.length <= 2;
            remove.addEventListener('click', () => {
                if (aliasTeamNames.length <= 2) return;
                aliasTeamNames.splice(index, 1);
                renderAliasTeamInputs();
            });

            row.append(badge, input, remove);
            aliasTeamInputs.append(row);
        });

        if (aliasAddTeamButton) aliasAddTeamButton.hidden = aliasTeamNames.length >= 4;
    };

    const renderAliasHandoff = () => {
        const team = aliasState.teams[aliasState.currentTeam];
        text('[data-alias-handoff-team]', team.name);
        const roundLabel = aliasState.settings.mode === 'rounds'
            ? `რაუნდი ${aliasState.round} / ${aliasState.settings.rounds}`
            : `რაუნდი ${aliasState.round} · მიზანი ${aliasState.settings.targetScore}`;
        text('[data-alias-handoff-round]', roundLabel);
        renderScores(document.querySelector('[data-alias-handoff-scores]'), aliasState);
        showScreen('alias-handoff');
    };

    const updateAliasTimer = () => {
        if (!aliasState || aliasState.phase !== 'playing') return;
        const milliseconds = Math.max(0, aliasState.endsAt - Date.now());
        const seconds = Math.ceil(milliseconds / 1000);
        const progress = Math.max(0, Math.min(1, milliseconds / (aliasState.settings.duration * 1000)));
        text('[data-alias-time]', seconds);
        const bar = document.querySelector('[data-alias-time-progress]');
        if (bar) bar.style.transform = `scaleX(${progress})`;

        if (milliseconds <= 0 && !aliasTimerFinishing) {
            aliasTimerFinishing = true;
            withBusy(async () => {
                try {
                    const data = await post('/alias/turn/finish');
                    aliasState = data.state;
                    renderAliasSummary();
                } catch (error) {
                    if (aliasPlayMessage) aliasPlayMessage.textContent = error.message;
                } finally {
                    aliasTimerFinishing = false;
                }
            });
        }
    };

    const startAliasTimer = () => {
        stopAliasTimer();
        updateAliasTimer();
        aliasTimerId = window.setInterval(updateAliasTimer, 200);
    };

    function stopAliasTimer() {
        if (aliasTimerId !== null) window.clearInterval(aliasTimerId);
        aliasTimerId = null;
    }

    const renderAliasPlay = (animate = false) => {
        const team = aliasState.teams[aliasState.currentTeam];
        text('[data-alias-play-team]', team.name);
        text('[data-alias-play-round]', `რაუნდი ${aliasState.round}`);
        text('[data-alias-turn-score]', aliasState.turnScore);
        text('[data-alias-current-word]', aliasState.currentWord);
        text('[data-alias-skip-cost]', aliasState.settings.skipPenalty === -1 ? '−1 ქულა' : 'ჯარიმის გარეშე');
        if (aliasPlayMessage) aliasPlayMessage.textContent = '';

        const card = document.querySelector('[data-alias-word-card]');
        if (animate && card) {
            card.classList.remove('is-changing');
            void card.offsetWidth;
            card.classList.add('is-changing');
        }

        showScreen('alias-play');
        startAliasTimer();
    };

    const renderAliasReviewList = () => {
        const list = document.querySelector('[data-alias-review-list]');
        const empty = document.querySelector('[data-alias-empty-review]');
        if (!list || !empty) return;
        list.replaceChildren();
        empty.hidden = aliasState.turnItems.length > 0;

        aliasState.turnItems.forEach((item, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `review-item review-item--${item.result}`;
            button.dataset.aliasReviewIndex = index;

            const status = document.createElement('span');
            status.textContent = item.result === 'correct' ? '✓' : '↷';
            const word = document.createElement('strong');
            word.textContent = item.word;
            const points = document.createElement('small');
            points.textContent = item.points > 0 ? `+${item.points}` : String(item.points);
            button.append(status, word, points);
            list.append(button);
        });
    };

    const renderAliasSummary = () => {
        stopAliasTimer();
        const team = aliasState.teams[aliasState.currentTeam];
        text('[data-alias-summary-score]', aliasState.turnScore > 0 ? `+${aliasState.turnScore}` : aliasState.turnScore);
        text('[data-alias-summary-team]', team.name);
        if (aliasSummaryError) aliasSummaryError.textContent = '';
        renderAliasReviewList();
        renderScores(document.querySelector('[data-alias-summary-scores]'), aliasState);

        const nextText = document.querySelector('[data-alias-next-turn] span:first-child');
        const lastTeam = aliasState.currentTeam === aliasState.teams.length - 1;
        if (nextText) nextText.textContent = lastTeam ? 'რაუნდის დასრულება' : 'შემდეგი გუნდი';
        showScreen('alias-summary');
    };

    const renderAliasFinal = () => {
        const winners = aliasState.winnerNames ?? [];
        text('[data-alias-final-kicker]', aliasState.isTie ? 'ფრე!' : 'გამარჯვებულია');
        text('[data-alias-winner-name]', winners.join(' და ') || 'თამაში დასრულდა');
        text('[data-alias-final-copy]', aliasState.isTie
            ? 'გუნდებმა თანაბარი ქულები დააგროვეს.'
            : 'ყველაზე მეტი სიტყვა გამოიცნო!');
        renderScores(document.querySelector('[data-alias-final-scores]'), aliasState);
        showScreen('alias-final');
    };

    const renderAliasState = () => {
        if (!aliasState) {
            showScreen('alias-home');
            return;
        }

        if (aliasState.phase === 'handoff') renderAliasHandoff();
        else if (aliasState.phase === 'playing') renderAliasPlay();
        else if (aliasState.phase === 'summary') renderAliasSummary();
        else if (aliasState.phase === 'finished') renderAliasFinal();
        else showScreen('alias-home');
    };

    const quitAliasTo = async (screen) => {
        await post('/alias/quit');
        aliasState = null;
        showScreen(screen);
    };

    renderAliasTeamInputs();

    document.querySelector('[data-alias-new-game]')?.addEventListener('click', () => showScreen('alias-setup'));
    document.querySelector('[data-alias-setup-back]')?.addEventListener('click', () => showScreen('alias-home'));
    aliasAddTeamButton?.addEventListener('click', () => {
        if (aliasTeamNames.length >= 4) return;
        aliasTeamNames.push(`გუნდი ${aliasTeamNames.length + 1}`);
        renderAliasTeamInputs();
    });

    aliasSetupForm?.addEventListener('input', () => {
        const data = new FormData(aliasSetupForm);
        const mode = data.get('mode');
        document.querySelector('[data-alias-rounds-option]').hidden = mode !== 'rounds';
        document.querySelector('[data-alias-points-option]').hidden = mode !== 'points';
        text('[data-alias-rounds-value]', data.get('rounds'));
        text('[data-alias-target-value]', data.get('targetScore'));
    });

    aliasSetupForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        withBusy(async () => {
            const data = new FormData(aliasSetupForm);
            if (aliasFormError) aliasFormError.textContent = '';

            try {
                const response = await post('/alias/start', {
                    teams: aliasTeamNames.map((name) => name.trim()),
                    duration: Number(data.get('duration')),
                    mode: data.get('mode'),
                    rounds: Number(data.get('rounds')),
                    targetScore: Number(data.get('targetScore')),
                    skipPenalty: Number(data.get('skipPenalty')),
                    category: data.get('category'),
                });
                aliasState = response.state;
                namiState = null;
                renderAliasHandoff();
            } catch (error) {
                if (aliasFormError) aliasFormError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-alias-start-turn]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/alias/turn/start');
                aliasState = data.state;
                renderAliasPlay();
            } catch (error) {
                if (aliasPlayMessage) aliasPlayMessage.textContent = error.message;
                showScreen('alias-handoff');
            }
        });
    });

    document.querySelectorAll('[data-alias-mark]').forEach((button) => {
        button.addEventListener('click', () => {
            withBusy(async () => {
                try {
                    const data = await post('/alias/word/mark', { result: button.dataset.aliasMark });
                    aliasState = data.state;
                    if (aliasState.phase === 'summary') renderAliasSummary();
                    else renderAliasPlay(true);
                } catch (error) {
                    if (aliasPlayMessage) aliasPlayMessage.textContent = error.message;
                }
            });
        });
    });

    document.querySelector('[data-alias-review-list]')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-alias-review-index]');
        if (!button) return;
        const index = Number(button.dataset.aliasReviewIndex);
        const current = aliasState.turnItems[index]?.result;
        const result = current === 'correct' ? 'skipped' : 'correct';

        withBusy(async () => {
            try {
                const data = await post('/alias/turn/review', { index, result });
                aliasState = data.state;
                renderAliasSummary();
            } catch (error) {
                if (aliasSummaryError) aliasSummaryError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-alias-next-turn]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/alias/next');
                aliasState = data.state;
                renderAliasState();
            } catch (error) {
                if (aliasSummaryError) aliasSummaryError.textContent = error.message;
            }
        });
    });

    document.querySelectorAll('[data-alias-exit]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!confirmExit()) return;
            withBusy(() => quitAliasTo('hub'));
        });
    });

    document.querySelector('[data-alias-play-again]')?.addEventListener('click', () => {
        withBusy(() => quitAliasTo('alias-setup'));
    });
    document.querySelector('[data-alias-final-home]')?.addEventListener('click', () => {
        withBusy(() => quitAliasTo('hub'));
    });

    // Namiokobana
    const namiRoundLabel = () => `რაუნდი ${namiState.round} / ${namiState.settings.rounds}`;

    const renderNamiWordEntry = () => {
        const setter = namiState.teams[namiState.setterTeam];
        const guessing = namiState.teams[namiState.currentTeam];
        text('[data-nami-entry-round]', namiRoundLabel());
        text('[data-nami-setter-team]', setter.name);
        text('[data-nami-guessing-team]', guessing.name);
        if (namiWordError) namiWordError.textContent = '';
        renderScores(document.querySelector('[data-nami-entry-scores]'), namiState);
        showScreen('nami-word-entry');
    };

    const renderNamiHandoff = () => {
        text('[data-nami-handoff-round]', namiRoundLabel());
        text('[data-nami-handoff-team]', namiState.teams[namiState.currentTeam].name);
        text('[data-nami-handoff-copy]', namiState.settings.wordSource === 'app'
            ? 'სიტყვა აპმა აირჩია და დამალა. მხოლოდ ამხსნელმა დააჭიროს ღილაკს და ნახოს.'
            : 'სიტყვა მოწინააღმდეგემ შეიყვანა და დამალა. მხოლოდ ამხსნელმა დააჭიროს ღილაკს და ნახოს.');
        renderScores(document.querySelector('[data-nami-handoff-scores]'), namiState);
        showScreen('nami-handoff');
    };

    const renderNamiReveal = () => {
        text('[data-nami-secret-word]', namiState.secretWord);
        showScreen('nami-reveal');
    };

    const updateNamiTimer = () => {
        if (!namiState || namiState.phase !== 'guessing') return;
        const milliseconds = Math.max(0, namiState.endsAt - Date.now());
        const seconds = Math.ceil(milliseconds / 1000);
        const progress = Math.max(0, Math.min(1, milliseconds / (namiState.settings.duration * 1000)));
        text('[data-nami-time]', seconds);
        const bar = document.querySelector('[data-nami-time-progress]');
        if (bar) bar.style.transform = `scaleX(${progress})`;

        if (milliseconds <= 0 && !namiTimerFinishing) {
            namiTimerFinishing = true;
            withBusy(async () => {
                try {
                    const data = await post('/namiokobana/finish', { result: 'missed' });
                    namiState = data.state;
                    renderNamiSummary();
                } catch (error) {
                    if (namiPlayError) namiPlayError.textContent = error.message;
                } finally {
                    namiTimerFinishing = false;
                }
            });
        }
    };

    const startNamiTimer = () => {
        stopNamiTimer();
        updateNamiTimer();
        namiTimerId = window.setInterval(updateNamiTimer, 200);
    };

    function stopNamiTimer() {
        if (namiTimerId !== null) window.clearInterval(namiTimerId);
        namiTimerId = null;
    }

    const renderNamiGuessing = () => {
        text('[data-nami-play-team]', namiState.teams[namiState.currentTeam].name);
        text('[data-nami-play-round]', namiRoundLabel());
        text('[data-nami-guessing-word]', namiState.secretWord);
        if (namiPlayError) namiPlayError.textContent = '';
        showScreen('nami-guessing');
        startNamiTimer();
    };

    const renderNamiSummary = () => {
        stopNamiTimer();
        const correct = namiState.result === 'correct';
        text('[data-nami-result-symbol]', correct ? '✓' : '×');
        text('[data-nami-result-label]', correct ? 'გამოიცნო!' : 'ვერ გამოიცნო');
        text('[data-nami-summary-word]', namiState.secretWord);
        text('[data-nami-summary-copy]', correct ? 'გუნდს დაემატა 1 ქულა.' : 'ამ სვლაზე ქულა არ დამატებულა.');
        const symbol = document.querySelector('[data-nami-result-symbol]');
        symbol?.classList.toggle('is-missed', !correct);
        if (namiSummaryError) namiSummaryError.textContent = '';
        renderScores(document.querySelector('[data-nami-summary-scores]'), namiState);

        const nextText = document.querySelector('[data-nami-next] span:first-child');
        if (nextText) {
            const lastTeam = namiState.currentTeam === namiState.teams.length - 1;
            nextText.textContent = lastTeam ? 'რაუნდის დასრულება' : 'შემდეგი გუნდი';
        }
        showScreen('nami-summary');
    };

    const renderNamiFinal = () => {
        const winners = namiState.winnerNames ?? [];
        text('[data-nami-final-kicker]', namiState.isTie ? 'ფრე!' : 'გამარჯვებულია');
        text('[data-nami-winner-name]', winners.join(' და ') || 'თამაში დასრულდა');
        text('[data-nami-final-copy]', namiState.isTie
            ? 'გუნდებმა თანაბარი ქულები დააგროვეს.'
            : 'მინიშნებების ჩემპიონები!');
        renderScores(document.querySelector('[data-nami-final-scores]'), namiState);
        showScreen('nami-final');
    };

    const renderNamiState = () => {
        if (!namiState) {
            showScreen('nami-setup');
            return;
        }

        if (namiState.phase === 'word_entry') renderNamiWordEntry();
        else if (namiState.phase === 'handoff') renderNamiHandoff();
        else if (namiState.phase === 'reveal') renderNamiReveal();
        else if (namiState.phase === 'guessing') renderNamiGuessing();
        else if (namiState.phase === 'summary') renderNamiSummary();
        else if (namiState.phase === 'finished') renderNamiFinal();
        else showScreen('nami-setup');
    };

    const quitNamiTo = async (screen) => {
        await post('/namiokobana/quit');
        namiState = null;
        showScreen(screen);
    };

    namiSetupForm?.addEventListener('input', () => {
        const data = new FormData(namiSetupForm);
        text('[data-nami-source-note]', data.get('wordSource') === 'app'
            ? 'აპი ყოველ სვლაზე შემთხვევით ქართულ სიტყვას აირჩევს და დამალავს.'
            : 'მოწინააღმდეგე გუნდი ჩაწერს საიდუმლო სიტყვას.');
    });

    namiSetupForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        withBusy(async () => {
            const data = new FormData(namiSetupForm);
            if (namiSetupError) namiSetupError.textContent = '';

            try {
                const response = await post('/namiokobana/start', {
                    teams: [data.get('teamOne')?.trim(), data.get('teamTwo')?.trim()],
                    duration: Number(data.get('duration')),
                    rounds: Number(data.get('rounds')),
                    wordSource: data.get('wordSource'),
                });
                namiState = response.state;
                aliasState = null;
                renderNamiState();
            } catch (error) {
                if (namiSetupError) namiSetupError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-nami-word-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        withBusy(async () => {
            const data = new FormData(form);
            if (namiWordError) namiWordError.textContent = '';

            try {
                const response = await post('/namiokobana/word', { word: data.get('word')?.trim() });
                namiState = response.state;
                form.reset();
                renderNamiHandoff();
            } catch (error) {
                if (namiWordError) namiWordError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-nami-reveal]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/namiokobana/reveal');
                namiState = data.state;
                renderNamiReveal();
            } catch (error) {
                if (namiWordError) namiWordError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-nami-begin]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/namiokobana/begin');
                namiState = data.state;
                renderNamiGuessing();
            } catch (error) {
                if (namiPlayError) namiPlayError.textContent = error.message;
            }
        });
    });

    document.querySelectorAll('[data-nami-result]').forEach((button) => {
        button.addEventListener('click', () => {
            withBusy(async () => {
                try {
                    const data = await post('/namiokobana/finish', { result: button.dataset.namiResult });
                    namiState = data.state;
                    renderNamiSummary();
                } catch (error) {
                    if (namiPlayError) namiPlayError.textContent = error.message;
                }
            });
        });
    });

    document.querySelector('[data-nami-next]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/namiokobana/next');
                namiState = data.state;
                renderNamiState();
            } catch (error) {
                if (namiSummaryError) namiSummaryError.textContent = error.message;
            }
        });
    });

    document.querySelectorAll('[data-nami-exit]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!confirmExit()) return;
            withBusy(() => quitNamiTo('hub'));
        });
    });

    document.querySelector('[data-nami-play-again]')?.addEventListener('click', () => {
        withBusy(() => quitNamiTo('nami-setup'));
    });
    document.querySelector('[data-nami-final-home]')?.addEventListener('click', () => {
        withBusy(() => quitNamiTo('hub'));
    });

    // Hub and shared navigation
    document.querySelectorAll('[data-select-game]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.selectGame === 'alias') {
                renderAliasState();
            } else {
                renderNamiState();
            }
        });
    });

    document.querySelectorAll('[data-back-hub], [data-nami-back-hub]').forEach((button) => {
        button.addEventListener('click', () => showScreen('hub'));
    });

    document.querySelectorAll('[data-open-rules]').forEach((button) => {
        button.addEventListener('click', () => openSheet(aliasRules));
    });
    document.querySelectorAll('[data-close-rules]').forEach((button) => {
        button.addEventListener('click', () => closeSheet(aliasRules));
    });
    document.querySelectorAll('[data-open-nami-rules]').forEach((button) => {
        button.addEventListener('click', () => openSheet(namiRules));
    });
    document.querySelectorAll('[data-close-nami-rules]').forEach((button) => {
        button.addEventListener('click', () => closeSheet(namiRules));
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) return;
        if (aliasState?.phase === 'playing') updateAliasTimer();
        if (namiState?.phase === 'guessing') updateNamiTimer();
    });

    showScreen('hub');
}
