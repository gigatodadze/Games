<!DOCTYPE html>
<html lang="ka">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
        <meta name="theme-color" content="#101a2f">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ქართული სიტყვების თამაშები</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="app-shell" data-app>
            <div class="stars" aria-hidden="true"></div>

            <section class="screen screen--hub is-active" data-screen="hub">
                <header class="topbar">
                    <div class="brand">
                        <span class="brand__mark brand__mark--games" aria-hidden="true">თ</span>
                        <span>თამაშები</span>
                    </div>
                    <span class="game-count">2 თამაში</span>
                </header>

                <div class="hub-heading">
                    <p class="eyebrow eyebrow--mint">ითამაშეთ ერთ ტელეფონზე</p>
                    <h1>აირჩიე თამაში</h1>
                    <p>შეკრიბე მეგობრები, აირჩიე წესები და დაიწყე.</p>
                </div>

                <div class="game-picker">
                    <button class="game-choice game-choice--alias" type="button" data-select-game="alias">
                        <span class="game-choice__number">01</span>
                        <span class="game-choice__icon">ა</span>
                        <span class="game-choice__copy">
                            <strong>ალიასი</strong>
                            <small>ახსენი ბევრი სიტყვა დროის ამოწურვამდე</small>
                        </span>
                        <span class="game-choice__arrow">→</span>
                    </button>

                    <button class="game-choice game-choice--nami" type="button" data-select-game="namiokobana">
                        <span class="game-choice__number">02</span>
                        <span class="game-choice__icon">ნ</span>
                        <span class="game-choice__copy">
                            <strong>ნამიოკობანა</strong>
                            <small>საიდუმლო სიტყვა, მინიშნებები და გამოცნობა</small>
                        </span>
                        <span class="game-choice__arrow">→</span>
                    </button>
                </div>

                <p class="hub-note">ყველა თამაში მუშაობს ინტერნეტის გარეშე</p>
            </section>

            <section class="screen screen--home" data-screen="alias-home" data-game="alias" aria-hidden="true">
                <header class="topbar topbar--three">
                    <button class="round-icon" type="button" data-back-hub aria-label="თამაშების არჩევა">←</button>
                    <div class="brand">
                        <span class="brand__mark" aria-hidden="true">ა</span>
                        <span>ალიასი</span>
                    </div>
                    <button class="round-icon" type="button" data-open-rules aria-label="ალიასის წესები">?</button>
                </header>

                <div class="home-hero">
                    <div class="home-illustration" aria-hidden="true">
                        <span class="bubble bubble--one">სიტყვა</span>
                        <span class="bubble bubble--two">მინიშნება</span>
                        <span class="bubble bubble--three">გამოიცანი!</span>
                        <span class="spark spark--one">✦</span>
                        <span class="spark spark--two">✦</span>
                    </div>
                    <p class="eyebrow">ქართული გუნდური თამაში</p>
                    <h1>ახსენი სიტყვა.<br><em>არ თქვა სიტყვა.</em></h1>
                    <p>გადააწოდე ტელეფონი გუნდის ამხსნელს, გამოიცანით რაც შეიძლება მეტი სიტყვა და დააგროვეთ ყველაზე მეტი ქულა.</p>
                </div>

                <div class="bottom-actions">
                    <button class="button button--primary" type="button" data-alias-new-game>
                        <span>ახალი თამაში</span><span class="button__icon">→</span>
                    </button>
                    <button class="button button--ghost" type="button" data-open-rules>როგორ ვითამაშოთ?</button>
                </div>
            </section>

            <section class="screen screen--scroll" data-screen="alias-setup" data-game="alias" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <button class="back-button" type="button" data-alias-setup-back aria-label="უკან">←</button>
                    <strong>ალიასის პარამეტრები</strong>
                    <span class="topbar__spacer"></span>
                </header>

                <form class="setup-form" data-alias-setup-form>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number">01</span><h2>გუნდები</h2></div>
                            <small>2–4 გუნდი</small>
                        </div>
                        <div class="team-inputs" data-alias-team-inputs></div>
                        <button class="add-team" type="button" data-alias-add-team>＋ გუნდის დამატება</button>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number">02</span><h2>რაუნდის დრო</h2></div>
                        </div>
                        <div class="segmented segmented--three">
                            <label><input type="radio" name="duration" value="45"><span>45 წმ</span></label>
                            <label><input type="radio" name="duration" value="60" checked><span>60 წმ</span></label>
                            <label><input type="radio" name="duration" value="90"><span>90 წმ</span></label>
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number">03</span><h2>როგორ სრულდება?</h2></div>
                        </div>
                        <div class="segmented">
                            <label><input type="radio" name="mode" value="rounds" checked><span>რაუნდებით</span></label>
                            <label><input type="radio" name="mode" value="points"><span>ქულებით</span></label>
                        </div>
                        <div class="range-card" data-alias-rounds-option>
                            <div><span>რაუნდების რაოდენობა</span><strong data-alias-rounds-value>3</strong></div>
                            <input type="range" name="rounds" min="1" max="8" value="3">
                        </div>
                        <div class="range-card" data-alias-points-option hidden>
                            <div><span>მიზნობრივი ქულა</span><strong data-alias-target-value>30</strong></div>
                            <input type="range" name="targetScore" min="10" max="100" step="5" value="30">
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number">04</span><h2>გამოტოვება</h2></div>
                        </div>
                        <div class="segmented">
                            <label><input type="radio" name="skipPenalty" value="0" checked><span>ჯარიმის გარეშე</span></label>
                            <label><input type="radio" name="skipPenalty" value="-1"><span>−1 ქულა</span></label>
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number">05</span><h2>სიტყვების ნაკრები</h2></div>
                        </div>
                        <div class="category-list">
                            @foreach ($bootstrap['categories'] as $key => $category)
                                <label class="category-card">
                                    <input type="radio" name="category" value="{{ $key }}" @checked($key === 'daily')>
                                    <span class="category-card__check">✓</span>
                                    <span>
                                        <strong>{{ $category['label'] }}</strong>
                                        <small>{{ $category['description'] }} · {{ $category['count'] }} სიტყვა</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    <p class="form-error" data-alias-form-error role="alert"></p>
                    <button class="button button--primary setup-submit" type="submit">
                        <span>თამაშის დაწყება</span><span class="button__icon">→</span>
                    </button>
                </form>
            </section>

            <section class="screen screen--handoff" data-screen="alias-handoff" data-game="alias" aria-hidden="true">
                <header class="topbar">
                    <button class="back-button back-button--dark" type="button" data-alias-exit aria-label="თამაშიდან გასვლა">×</button>
                    <div class="round-counter" data-alias-handoff-round>რაუნდი 1</div>
                    <span class="topbar__spacer"></span>
                </header>
                <div class="handoff-content">
                    <div class="phone-orbit" aria-hidden="true">
                        <span class="phone">ა</span>
                        <span class="orbit-person orbit-person--one">●</span>
                        <span class="orbit-person orbit-person--two">●</span>
                        <span class="orbit-person orbit-person--three">●</span>
                    </div>
                    <p class="eyebrow eyebrow--mint">გადააწოდე ტელეფონი</p>
                    <h2>ახლა ხსნის</h2>
                    <div class="team-pill" data-alias-handoff-team>გუნდი</div>
                    <p>სხვა გუნდებმა ეკრანს ნუ შეხედავთ. ამხსნელო, როცა მზად იქნები, დააჭირე ღილაკს.</p>
                </div>
                <div class="compact-scoreboard" data-alias-handoff-scores></div>
                <button class="button button--mint" type="button" data-alias-start-turn>
                    <span>მზად ვარ</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--play" data-screen="alias-play" data-game="alias" aria-hidden="true">
                <header class="play-header">
                    <button class="round-icon round-icon--dark" type="button" data-alias-exit aria-label="თამაშიდან გასვლა">×</button>
                    <div class="play-team">
                        <span data-alias-play-team>გუნდი</span>
                        <small data-alias-play-round>რაუნდი 1</small>
                    </div>
                    <div class="turn-score"><strong data-alias-turn-score>0</strong><small>ქულა</small></div>
                </header>
                <div class="time-area">
                    <div class="time-copy"><strong data-alias-time>60</strong><span>წამი</span></div>
                    <div class="time-track"><span data-alias-time-progress></span></div>
                </div>
                <div class="word-stage">
                    <p>აუხსენი შენს გუნდს</p>
                    <article class="word-card" data-alias-word-card>
                        <span class="word-card__corner">ალიასი</span>
                        <strong data-alias-current-word>სიტყვა</strong>
                        <small>არ გამოიყენო თვითონ სიტყვა ან მისი ფუძე</small>
                    </article>
                    <p class="play-message" data-alias-play-message role="status"></p>
                </div>
                <div class="answer-buttons">
                    <button class="answer-button answer-button--skip" type="button" data-alias-mark="skipped">
                        <span>↷</span><strong>გამოტოვება</strong><small data-alias-skip-cost>0 ქულა</small>
                    </button>
                    <button class="answer-button answer-button--correct" type="button" data-alias-mark="correct">
                        <span>✓</span><strong>გამოიცნო!</strong><small>+1 ქულა</small>
                    </button>
                </div>
            </section>

            <section class="screen screen--scroll screen--summary" data-screen="alias-summary" data-game="alias" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <span class="topbar__spacer"></span><strong>სვლის შედეგი</strong><span class="topbar__spacer"></span>
                </header>
                <div class="summary-hero">
                    <span>დრო ამოიწურა</span>
                    <strong data-alias-summary-score>0</strong>
                    <small>ამ სვლის ქულა</small>
                    <div class="team-pill team-pill--small" data-alias-summary-team>გუნდი</div>
                </div>
                <section class="review-section">
                    <div class="review-heading">
                        <h2>შეამოწმე პასუხები</h2>
                        <small>შეცდომის გასასწორებლად შეეხე სიტყვას</small>
                    </div>
                    <div class="review-list" data-alias-review-list></div>
                    <div class="empty-review" data-alias-empty-review hidden>ამ სვლაზე სიტყვა ვერ მოინიშნა.</div>
                </section>
                <section class="score-section">
                    <h2>საერთო ანგარიში</h2>
                    <div class="score-list" data-alias-summary-scores></div>
                </section>
                <p class="form-error" data-alias-summary-error role="alert"></p>
                <button class="button button--primary summary-next" type="button" data-alias-next-turn>
                    <span>შემდეგი გუნდი</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--final" data-screen="alias-final" data-game="alias" aria-hidden="true">
                <header class="topbar">
                    <div class="brand brand--light"><span class="brand__mark brand__mark--light">ა</span><span>ალიასი</span></div>
                    <span></span>
                </header>
                <div class="final-content">
                    <div class="trophy" aria-hidden="true">★</div>
                    <p class="eyebrow eyebrow--mint" data-alias-final-kicker>გამარჯვებულია</p>
                    <h2 data-alias-winner-name>გუნდი</h2>
                    <p data-alias-final-copy>ყველაზე მეტი სიტყვა გამოიცნო!</p>
                    <div class="final-scores" data-alias-final-scores></div>
                </div>
                <div class="bottom-actions">
                    <button class="button button--mint" type="button" data-alias-play-again>
                        <span>კიდევ თამაში</span><span class="button__icon">↻</span>
                    </button>
                    <button class="button button--dark-ghost" type="button" data-alias-final-home>თამაშების არჩევა</button>
                </div>
            </section>

            <section class="screen screen--scroll" data-screen="nami-setup" data-game="namiokobana" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <button class="back-button" type="button" data-nami-back-hub aria-label="თამაშების არჩევა">←</button>
                    <strong>ნამიოკობანა</strong>
                    <button class="back-button" type="button" data-open-nami-rules aria-label="ნამიოკობანას წესები">?</button>
                </header>
                <div class="nami-intro">
                    <span class="nami-mark">ნ</span>
                    <p class="eyebrow">საიდუმლო სიტყვების თამაში</p>
                    <h1>ნამიოკობანა</h1>
                    <p>ითამაშეთ ორი წყვილით. სიტყვა აირჩიოს მოწინააღმდეგემ ან თვითონ აპმა და მეწყვილეს თქვენი წესებით გამოაცნობინეთ.</p>
                </div>
                <form class="setup-form" data-nami-setup-form>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number step-number--nami">01</span><h2>ორი გუნდი</h2></div>
                        </div>
                        <div class="team-inputs">
                            <label class="team-input-row">
                                <span class="team-dot team-dot--1">1</span>
                                <input type="text" name="teamOne" maxlength="24" value="ვარსკვლავები" aria-label="პირველი გუნდი">
                            </label>
                            <label class="team-input-row">
                                <span class="team-dot team-dot--2">2</span>
                                <input type="text" name="teamTwo" maxlength="24" value="კომეტები" aria-label="მეორე გუნდი">
                            </label>
                        </div>
                    </section>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number step-number--nami">02</span><h2>ვინ ირჩევს სიტყვას?</h2></div>
                        </div>
                        <div class="segmented">
                            <label><input type="radio" name="wordSource" value="players" checked><span>მოთამაშე წერს</span></label>
                            <label><input type="radio" name="wordSource" value="app"><span>აპი ირჩევს</span></label>
                        </div>
                        <p class="source-note" data-nami-source-note>მოწინააღმდეგე გუნდი ჩაწერს საიდუმლო სიტყვას.</p>
                    </section>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number step-number--nami">03</span><h2>გამოცნობის დრო</h2></div>
                        </div>
                        <div class="segmented segmented--three">
                            <label><input type="radio" name="duration" value="30" checked><span>30 წმ</span></label>
                            <label><input type="radio" name="duration" value="45"><span>45 წმ</span></label>
                            <label><input type="radio" name="duration" value="60"><span>60 წმ</span></label>
                        </div>
                    </section>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div><span class="step-number step-number--nami">04</span><h2>რაუნდები</h2></div>
                        </div>
                        <div class="segmented segmented--three">
                            <label><input type="radio" name="rounds" value="3"><span>3</span></label>
                            <label><input type="radio" name="rounds" value="5" checked><span>5</span></label>
                            <label><input type="radio" name="rounds" value="7"><span>7</span></label>
                        </div>
                    </section>
                    <p class="form-error" data-nami-setup-error role="alert"></p>
                    <button class="button button--nami setup-submit" type="submit">
                        <span>თამაშის დაწყება</span><span class="button__icon">→</span>
                    </button>
                </form>
            </section>

            <section class="screen screen--nami" data-screen="nami-word-entry" data-game="namiokobana" aria-hidden="true">
                <header class="topbar">
                    <button class="round-icon" type="button" data-nami-exit aria-label="თამაშიდან გასვლა">×</button>
                    <div class="round-counter" data-nami-entry-round>რაუნდი 1 / 5</div>
                    <span class="topbar__spacer"></span>
                </header>
                <div class="nami-step-content">
                    <div class="secret-symbol" aria-hidden="true">•••</div>
                    <p class="eyebrow">საიდუმლო სიტყვა</p>
                    <h2><span data-nami-setter-team>მეორე გუნდი</span>, აირჩიეთ სიტყვა</h2>
                    <p>ეს სიტყვა უნდა გამოიცნოს გუნდმა <strong data-nami-guessing-team>პირველი გუნდი</strong>. სხვებს ეკრანი არ აჩვენოთ.</p>
                    <form class="secret-form" data-nami-word-form>
                        <label class="sr-only" for="nami-secret-word">საიდუმლო სიტყვა</label>
                        <input id="nami-secret-word" type="text" name="word" maxlength="32" autocomplete="off" placeholder="ჩაწერე სიტყვა…" required>
                        <p class="play-message" data-nami-word-error role="alert"></p>
                        <button class="button button--nami" type="submit">
                            <span>სიტყვის დამალვა</span><span class="button__icon">→</span>
                        </button>
                    </form>
                </div>
                <div class="compact-scoreboard" data-nami-entry-scores></div>
            </section>

            <section class="screen screen--nami" data-screen="nami-handoff" data-game="namiokobana" aria-hidden="true">
                <header class="topbar">
                    <button class="round-icon" type="button" data-nami-exit aria-label="თამაშიდან გასვლა">×</button>
                    <div class="round-counter" data-nami-handoff-round>რაუნდი 1 / 5</div>
                    <span class="topbar__spacer"></span>
                </header>
                <div class="handoff-content">
                    <div class="phone-orbit phone-orbit--nami" aria-hidden="true"><span class="phone phone--nami">ნ</span></div>
                    <p class="eyebrow">გადააწოდე ტელეფონი</p>
                    <h2>გუნდის ამხსნელს</h2>
                    <div class="team-pill team-pill--nami" data-nami-handoff-team>გუნდი</div>
                    <p data-nami-handoff-copy>სიტყვა დამალულია. მხოლოდ ამხსნელმა დააჭიროს ღილაკს და ნახოს.</p>
                </div>
                <div class="compact-scoreboard" data-nami-handoff-scores></div>
                <button class="button button--nami" type="button" data-nami-reveal>
                    <span>სიტყვის ნახვა</span><span class="button__icon">◎</span>
                </button>
            </section>

            <section class="screen screen--nami" data-screen="nami-reveal" data-game="namiokobana" aria-hidden="true">
                <header class="topbar">
                    <span class="topbar__spacer"></span>
                    <div class="round-counter">მხოლოდ ამხსნელისთვის</div>
                    <span class="topbar__spacer"></span>
                </header>
                <div class="nami-reveal-content">
                    <p class="eyebrow">დაიმახსოვრე სიტყვა</p>
                    <article class="secret-card">
                        <span>ნამიოკობანა</span>
                        <strong data-nami-secret-word>სიტყვა</strong>
                        <small>დაიმახსოვრე და აუხსენი მეწყვილეს თქვენი წესებით</small>
                    </article>
                    <p>შეგიძლიათ ითამაშოთ სიტყვებით, ჟესტებით ან სრულიად მუნჯურად.</p>
                </div>
                <button class="button button--nami" type="button" data-nami-begin>
                    <span>დავიმახსოვრე — დაწყება</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--nami" data-screen="nami-guessing" data-game="namiokobana" aria-hidden="true">
                <header class="play-header">
                    <button class="round-icon" type="button" data-nami-exit aria-label="თამაშიდან გასვლა">×</button>
                    <div class="play-team">
                        <span data-nami-play-team>გუნდი</span>
                        <small data-nami-play-round>რაუნდი 1</small>
                    </div>
                    <div class="turn-score"><strong>+1</strong><small>სწორი პასუხი</small></div>
                </header>
                <div class="time-area">
                    <div class="time-copy"><strong data-nami-time>30</strong><span>წამი</span></div>
                    <div class="time-track time-track--nami"><span data-nami-time-progress></span></div>
                </div>
                <div class="nami-guess-stage">
                    <p class="eyebrow">გამოსაცნობი სიტყვა</p>
                    <article class="guess-word-card">
                        <strong data-nami-guessing-word>სიტყვა</strong>
                    </article>
                    <p class="play-message" data-nami-play-error role="alert"></p>
                </div>
                <div class="answer-buttons">
                    <button class="answer-button answer-button--missed" type="button" data-nami-result="missed">
                        <span>×</span><strong>ვერ გამოიცნო</strong><small>0 ქულა</small>
                    </button>
                    <button class="answer-button answer-button--nami" type="button" data-nami-result="correct">
                        <span>✓</span><strong>გამოიცნო!</strong><small>+1 ქულა</small>
                    </button>
                </div>
            </section>

            <section class="screen screen--scroll screen--summary" data-screen="nami-summary" data-game="namiokobana" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <span class="topbar__spacer"></span><strong>რაუნდის შედეგი</strong><span class="topbar__spacer"></span>
                </header>
                <div class="nami-result-hero">
                    <span class="result-symbol" data-nami-result-symbol>✓</span>
                    <p class="eyebrow" data-nami-result-label>გამოიცნო</p>
                    <h2 data-nami-summary-word>სიტყვა</h2>
                    <p data-nami-summary-copy>გუნდს დაემატა 1 ქულა.</p>
                </div>
                <section class="score-section">
                    <h2>საერთო ანგარიში</h2>
                    <div class="score-list" data-nami-summary-scores></div>
                </section>
                <p class="form-error" data-nami-summary-error role="alert"></p>
                <button class="button button--primary summary-next" type="button" data-nami-next>
                    <span>შემდეგი გუნდი</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--final screen--nami-final" data-screen="nami-final" data-game="namiokobana" aria-hidden="true">
                <header class="topbar">
                    <div class="brand brand--light"><span class="brand__mark brand__mark--nami">ნ</span><span>ნამიოკობანა</span></div>
                    <span></span>
                </header>
                <div class="final-content">
                    <div class="trophy trophy--nami" aria-hidden="true">✦</div>
                    <p class="eyebrow" data-nami-final-kicker>გამარჯვებულია</p>
                    <h2 data-nami-winner-name>გუნდი</h2>
                    <p data-nami-final-copy>მინიშნებების ჩემპიონები!</p>
                    <div class="final-scores" data-nami-final-scores></div>
                </div>
                <div class="bottom-actions">
                    <button class="button button--nami" type="button" data-nami-play-again>
                        <span>კიდევ თამაში</span><span class="button__icon">↻</span>
                    </button>
                    <button class="button button--dark-ghost" type="button" data-nami-final-home>თამაშების არჩევა</button>
                </div>
            </section>
        </main>

        <div class="rules-sheet" data-rules aria-hidden="true">
            <button class="rules-sheet__backdrop" type="button" data-close-rules tabindex="-1" aria-label="დახურვა"></button>
            <section class="rules-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="alias-rules-title">
                <div class="sheet-handle" aria-hidden="true"></div>
                <div class="rules-heading">
                    <div><p class="eyebrow">თამაშის წესები</p><h2 id="alias-rules-title">როგორ ვითამაშოთ ალიასი?</h2></div>
                    <button class="round-icon round-icon--light" type="button" data-close-rules aria-label="დახურვა">×</button>
                </div>
                <ol class="rules-list">
                    <li><span>1</span><p>გაიყავით 2–4 გუნდად. ყოველ სვლაზე ერთი მოთამაშე ხსნის ეკრანზე ნაჩვენებ სიტყვას.</p></li>
                    <li><span>2</span><p>არ შეიძლება თვითონ სიტყვის, მისი ნაწილის ან იმავე ფუძის მქონე სიტყვის გამოყენება.</p></li>
                    <li><span>3</span><p>სწორ პასუხზე დააჭირე „გამოიცნო“, რთული სიტყვისას — „გამოტოვება“. თითო სწორი პასუხი 1 ქულაა.</p></li>
                    <li><span>4</span><p>დროის დასრულების შემდეგ გადაამოწმე პასუხები. ყველა გუნდს თანაბარი რაოდენობის სვლა აქვს.</p></li>
                </ol>
            </section>
        </div>

        <div class="rules-sheet" data-nami-rules aria-hidden="true">
            <button class="rules-sheet__backdrop" type="button" data-close-nami-rules tabindex="-1" aria-label="დახურვა"></button>
            <section class="rules-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="nami-rules-title">
                <div class="sheet-handle" aria-hidden="true"></div>
                <div class="rules-heading">
                    <div><p class="eyebrow">თამაშის წესები</p><h2 id="nami-rules-title">როგორ ვითამაშოთ ნამიოკობანა?</h2></div>
                    <button class="round-icon round-icon--light" type="button" data-close-nami-rules aria-label="დახურვა">×</button>
                </div>
                <ol class="rules-list rules-list--nami">
                    <li><span>1</span><p>გაიყავით ორ წყვილად და აირჩიეთ რეჟიმი: საიდუმლო სიტყვას მოწინააღმდეგე გუნდი წერს ან აპი ავტომატურად ირჩევს.</p></li>
                    <li><span>2</span><p>სიტყვას ამხსნელი ნახავს და თამაშის დროს ეკრანზე ხელახლა ჩახედვაც შეუძლია.</p></li>
                    <li><span>3</span><p>წინასწარ შეთანხმდით როგორ თამაშობთ: სიტყვებით, ჟესტებით თუ სრულიად მუნჯურად.</p></li>
                    <li><span>4</span><p>სწორი პასუხი 1 ქულაა. ორივე გუნდს თითო სვლა აქვს ყოველ რაუნდში.</p></li>
                </ol>
                <div class="rule-example rule-example--nami">
                    <small>თქვენი თამაში, თქვენი წესები</small><strong>სიტყვა: „ქოლგა“</strong><p>აუხსენით ისე, როგორც თამაშის დაწყებამდე შეთანხმდით.</p>
                </div>
            </section>
        </div>

        <script>
            window.GEORGIAN_GAMES = @json($bootstrap);
        </script>
    </body>
</html>
