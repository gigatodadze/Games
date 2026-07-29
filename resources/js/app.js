const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
const bootstrap = window.ALIASI ?? {};

if (document.querySelector('[data-app]')) {
    const screens = [...document.querySelectorAll('[data-screen]')];
    const rules = document.querySelector('[data-rules]');
    const setupForm = document.querySelector('[data-setup-form]');
    const teamInputs = document.querySelector('[data-team-inputs]');
    const addTeamButton = document.querySelector('[data-add-team]');
    const formError = document.querySelector('[data-form-error]');
    const playMessage = document.querySelector('[data-play-message]');
    const summaryError = document.querySelector('[data-summary-error]');
    let state = bootstrap.currentGame ?? null;
    let teamNames = ['ლურჯები', 'ვარდისფრები'];
    let timerId = null;
    let timerFinishing = false;
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

        if (name !== 'play') stopTimer();
    };

    const post = async (url, payload = {}) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const error = new Error(data.message ?? 'რაღაც ვერ გამოვიდა. სცადე თავიდან.');
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

    const renderTeamInputs = () => {
        if (!teamInputs) return;
        teamInputs.replaceChildren();

        teamNames.forEach((name, index) => {
            const row = document.createElement('div');
            row.className = 'team-input-row';

            const badge = document.createElement('span');
            badge.className = `team-dot team-dot--${index + 1}`;
            badge.textContent = index + 1;

            const input = document.createElement('input');
            input.type = 'text';
            input.maxLength = 24;
            input.value = name;
            input.setAttribute('aria-label', `გუნდი ${index + 1}`);
            input.addEventListener('input', () => {
                teamNames[index] = input.value;
            });

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'remove-team';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `${name} გუნდის წაშლა`);
            remove.hidden = teamNames.length <= 2;
            remove.addEventListener('click', () => {
                if (teamNames.length <= 2) return;
                teamNames.splice(index, 1);
                renderTeamInputs();
            });

            row.append(badge, input, remove);
            teamInputs.append(row);
        });

        if (addTeamButton) addTeamButton.hidden = teamNames.length >= 4;
    };

    const teamColorClass = (index) => `team-dot--${(index % 4) + 1}`;

    const renderScores = (container, teams = state?.teams ?? []) => {
        if (!container) return;
        container.replaceChildren();

        teams.forEach((team) => {
            const row = document.createElement('div');
            row.className = 'score-row';
            if (team.index === state?.currentTeam) row.classList.add('is-current');

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

    const renderHandoff = () => {
        const team = state.teams[state.currentTeam];
        text('[data-handoff-team]', team.name);
        const roundLabel = state.settings.mode === 'rounds'
            ? `რაუნდი ${state.round} / ${state.settings.rounds}`
            : `რაუნდი ${state.round} · მიზანი ${state.settings.targetScore}`;
        text('[data-handoff-round]', roundLabel);
        renderScores(document.querySelector('[data-handoff-scores]'));
        showScreen('handoff');
    };

    const updateTimer = () => {
        if (!state || state.phase !== 'playing') return;
        const milliseconds = Math.max(0, state.endsAt - Date.now());
        const seconds = Math.ceil(milliseconds / 1000);
        const progress = Math.max(0, Math.min(1, milliseconds / (state.settings.duration * 1000)));
        text('[data-time]', seconds);
        const bar = document.querySelector('[data-time-progress]');
        if (bar) bar.style.transform = `scaleX(${progress})`;

        if (milliseconds <= 0 && !timerFinishing) {
            timerFinishing = true;
            withBusy(async () => {
                try {
                    const data = await post('/alias/turn/finish');
                    state = data.state;
                    renderSummary();
                } catch (error) {
                    if (playMessage) playMessage.textContent = error.message;
                } finally {
                    timerFinishing = false;
                }
            });
        }
    };

    const startTimer = () => {
        stopTimer();
        updateTimer();
        timerId = window.setInterval(updateTimer, 200);
    };

    function stopTimer() {
        if (timerId !== null) window.clearInterval(timerId);
        timerId = null;
    }

    const renderPlay = (animate = false) => {
        const team = state.teams[state.currentTeam];
        text('[data-play-team]', team.name);
        text('[data-play-round]', `რაუნდი ${state.round}`);
        text('[data-turn-score]', state.turnScore);
        text('[data-current-word]', state.currentWord);
        text('[data-skip-cost]', state.settings.skipPenalty === -1 ? '−1 ქულა' : 'ჯარიმის გარეშე');
        if (playMessage) playMessage.textContent = '';

        const card = document.querySelector('[data-word-card]');
        if (animate && card) {
            card.classList.remove('is-changing');
            void card.offsetWidth;
            card.classList.add('is-changing');
        }

        showScreen('play');
        startTimer();
    };

    const renderReviewList = () => {
        const list = document.querySelector('[data-review-list]');
        const empty = document.querySelector('[data-empty-review]');
        if (!list || !empty) return;
        list.replaceChildren();
        empty.hidden = state.turnItems.length > 0;

        state.turnItems.forEach((item, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `review-item review-item--${item.result}`;
            button.dataset.reviewIndex = index;

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

    const renderSummary = () => {
        stopTimer();
        const team = state.teams[state.currentTeam];
        text('[data-summary-score]', state.turnScore > 0 ? `+${state.turnScore}` : state.turnScore);
        text('[data-summary-team]', team.name);
        if (summaryError) summaryError.textContent = '';
        renderReviewList();
        renderScores(document.querySelector('[data-summary-scores]'));

        const nextText = document.querySelector('[data-next-turn] span:first-child');
        const lastTeam = state.currentTeam === state.teams.length - 1;
        if (nextText) {
            nextText.textContent = lastTeam ? 'რაუნდის დასრულება' : 'შემდეგი გუნდი';
        }
        showScreen('summary');
    };

    const renderFinal = () => {
        const winners = state.winnerNames ?? [];
        text('[data-final-kicker]', state.isTie ? 'ფრე!' : 'გამარჯვებულია');
        text('[data-winner-name]', winners.join(' და ') || 'თამაში დასრულდა');
        text('[data-final-copy]', state.isTie
            ? 'გუნდებმა თანაბარი ქულები დააგროვეს.'
            : 'ყველაზე მეტი სიტყვა გამოიცნო!');
        renderScores(document.querySelector('[data-final-scores]'));
        showScreen('final');
    };

    const renderState = () => {
        if (!state) {
            showScreen('home');
            return;
        }

        if (state.phase === 'handoff') renderHandoff();
        else if (state.phase === 'playing') renderPlay();
        else if (state.phase === 'summary') renderSummary();
        else if (state.phase === 'finished') renderFinal();
        else showScreen('home');
    };

    const quitTo = async (screen) => {
        await post('/alias/quit');
        state = null;
        showScreen(screen);
    };

    renderTeamInputs();
    renderState();

    document.querySelector('[data-new-game]')?.addEventListener('click', () => showScreen('setup'));
    document.querySelector('[data-setup-back]')?.addEventListener('click', () => showScreen('home'));
    addTeamButton?.addEventListener('click', () => {
        if (teamNames.length >= 4) return;
        teamNames.push(`გუნდი ${teamNames.length + 1}`);
        renderTeamInputs();
    });

    setupForm?.querySelectorAll('input[name="mode"]').forEach((input) => {
        input.addEventListener('change', () => {
            const points = input.value === 'points' && input.checked;
            const roundsOption = document.querySelector('[data-rounds-option]');
            const pointsOption = document.querySelector('[data-points-option]');
            if (roundsOption) roundsOption.hidden = points;
            if (pointsOption) pointsOption.hidden = !points;
        });
    });

    setupForm?.querySelector('input[name="rounds"]')?.addEventListener('input', (event) => {
        text('[data-rounds-value]', event.target.value);
    });

    setupForm?.querySelector('input[name="targetScore"]')?.addEventListener('input', (event) => {
        text('[data-target-value]', event.target.value);
    });

    setupForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (formError) formError.textContent = '';
        const names = teamNames.map((name) => name.trim());

        if (names.some((name) => !name)) {
            if (formError) formError.textContent = 'ყველა გუნდს დაარქვი სახელი.';
            return;
        }

        const data = new FormData(setupForm);
        withBusy(async () => {
            try {
                const response = await post('/alias/start', {
                    teams: names,
                    duration: Number(data.get('duration')),
                    mode: data.get('mode'),
                    rounds: Number(data.get('rounds')),
                    targetScore: Number(data.get('targetScore')),
                    skipPenalty: Number(data.get('skipPenalty')),
                    category: data.get('category'),
                });
                state = response.state;
                renderHandoff();
            } catch (error) {
                if (formError) formError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-start-turn]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/alias/turn/start');
                state = data.state;
                renderPlay();
            } catch (error) {
                showScreen('handoff');
            }
        });
    });

    document.querySelectorAll('[data-mark]').forEach((button) => {
        button.addEventListener('click', () => {
            withBusy(async () => {
                try {
                    const data = await post('/alias/word/mark', { result: button.dataset.mark });
                    state = data.state;
                    if (state.phase === 'summary') renderSummary();
                    else renderPlay(true);
                } catch (error) {
                    if (playMessage) playMessage.textContent = error.message;
                }
            });
        });
    });

    document.querySelector('[data-review-list]')?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-review-index]');
        if (!button) return;
        const index = Number(button.dataset.reviewIndex);
        const current = state.turnItems[index]?.result;
        const result = current === 'correct' ? 'skipped' : 'correct';

        withBusy(async () => {
            try {
                const data = await post('/alias/turn/review', { index, result });
                state = data.state;
                renderSummary();
            } catch (error) {
                if (summaryError) summaryError.textContent = error.message;
            }
        });
    });

    document.querySelector('[data-next-turn]')?.addEventListener('click', () => {
        withBusy(async () => {
            try {
                const data = await post('/alias/next');
                state = data.state;
                renderState();
            } catch (error) {
                if (summaryError) summaryError.textContent = error.message;
            }
        });
    });

    document.querySelectorAll('[data-exit-game]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!window.confirm('ნამდვილად გინდა მიმდინარე თამაშიდან გასვლა?')) return;
            withBusy(() => quitTo('home'));
        });
    });

    document.querySelector('[data-play-again]')?.addEventListener('click', () => {
        withBusy(() => quitTo('setup'));
    });
    document.querySelector('[data-final-home]')?.addEventListener('click', () => {
        withBusy(() => quitTo('home'));
    });

    document.querySelectorAll('[data-open-rules]').forEach((button) => {
        button.addEventListener('click', () => {
            rules?.classList.add('is-open');
            rules?.setAttribute('aria-hidden', 'false');
        });
    });

    document.querySelectorAll('[data-close-rules]').forEach((button) => {
        button.addEventListener('click', () => {
            rules?.classList.remove('is-open');
            rules?.setAttribute('aria-hidden', 'true');
        });
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && state?.phase === 'playing') updateTimer();
    });
}
