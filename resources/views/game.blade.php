<!DOCTYPE html>
<html lang="ka">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1">
        <meta name="theme-color" content="#101a2f">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ალიასი — ქართული სიტყვების თამაში</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="app-shell" data-app>
            <div class="stars" aria-hidden="true"></div>

            <section class="screen screen--home is-active" data-screen="home">
                <header class="topbar">
                    <div class="brand">
                        <span class="brand__mark" aria-hidden="true">ა</span>
                        <span>ალიასი</span>
                    </div>
                    <button class="round-icon" type="button" data-open-rules aria-label="თამაშის წესები">?</button>
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
                    <button class="button button--primary" type="button" data-new-game>
                        <span>ახალი თამაში</span>
                        <span class="button__icon">→</span>
                    </button>
                    <button class="button button--ghost" type="button" data-open-rules>როგორ ვითამაშოთ?</button>
                </div>
            </section>

            <section class="screen screen--scroll" data-screen="setup" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <button class="back-button" type="button" data-setup-back aria-label="უკან">←</button>
                    <strong>თამაშის პარამეტრები</strong>
                    <span class="topbar__spacer"></span>
                </header>

                <form class="setup-form" data-setup-form>
                    <section class="setup-block">
                        <div class="section-heading">
                            <div>
                                <span class="step-number">01</span>
                                <h2>გუნდები</h2>
                            </div>
                            <small>2–4 გუნდი</small>
                        </div>
                        <div class="team-inputs" data-team-inputs></div>
                        <button class="add-team" type="button" data-add-team>＋ გუნდის დამატება</button>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div>
                                <span class="step-number">02</span>
                                <h2>რაუნდის დრო</h2>
                            </div>
                        </div>
                        <div class="segmented segmented--three">
                            <label><input type="radio" name="duration" value="45"><span>45 წმ</span></label>
                            <label><input type="radio" name="duration" value="60" checked><span>60 წმ</span></label>
                            <label><input type="radio" name="duration" value="90"><span>90 წმ</span></label>
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div>
                                <span class="step-number">03</span>
                                <h2>როგორ სრულდება?</h2>
                            </div>
                        </div>
                        <div class="segmented">
                            <label><input type="radio" name="mode" value="rounds" checked><span>რაუნდებით</span></label>
                            <label><input type="radio" name="mode" value="points"><span>ქულებით</span></label>
                        </div>
                        <div class="range-card" data-rounds-option>
                            <div><span>რაუნდების რაოდენობა</span><strong data-rounds-value>3</strong></div>
                            <input type="range" name="rounds" min="1" max="8" value="3">
                        </div>
                        <div class="range-card" data-points-option hidden>
                            <div><span>მიზნობრივი ქულა</span><strong data-target-value>30</strong></div>
                            <input type="range" name="targetScore" min="10" max="100" step="5" value="30">
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div>
                                <span class="step-number">04</span>
                                <h2>გამოტოვება</h2>
                            </div>
                        </div>
                        <div class="segmented">
                            <label><input type="radio" name="skipPenalty" value="0" checked><span>ჯარიმის გარეშე</span></label>
                            <label><input type="radio" name="skipPenalty" value="-1"><span>−1 ქულა</span></label>
                        </div>
                    </section>

                    <section class="setup-block">
                        <div class="section-heading">
                            <div>
                                <span class="step-number">05</span>
                                <h2>სიტყვების ნაკრები</h2>
                            </div>
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

                    <p class="form-error" data-form-error role="alert"></p>
                    <button class="button button--primary setup-submit" type="submit">
                        <span>თამაშის დაწყება</span>
                        <span class="button__icon">→</span>
                    </button>
                </form>
            </section>

            <section class="screen screen--handoff" data-screen="handoff" aria-hidden="true">
                <header class="topbar">
                    <button class="back-button back-button--dark" type="button" data-exit-game aria-label="თამაშიდან გასვლა">×</button>
                    <div class="round-counter" data-handoff-round>რაუნდი 1</div>
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
                    <div class="team-pill" data-handoff-team>გუნდი</div>
                    <p>სხვა გუნდებმა ეკრანს ნუ შეხედავთ. ამხსნელო, როცა მზად იქნები, დააჭირე ღილაკს.</p>
                </div>

                <div class="compact-scoreboard" data-handoff-scores></div>
                <button class="button button--mint" type="button" data-start-turn>
                    <span>მზად ვარ</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--play" data-screen="play" aria-hidden="true">
                <header class="play-header">
                    <button class="round-icon round-icon--dark" type="button" data-exit-game aria-label="თამაშიდან გასვლა">×</button>
                    <div class="play-team">
                        <span data-play-team>გუნდი</span>
                        <small data-play-round>რაუნდი 1</small>
                    </div>
                    <div class="turn-score"><strong data-turn-score>0</strong><small>ქულა</small></div>
                </header>

                <div class="time-area">
                    <div class="time-copy"><strong data-time>60</strong><span>წამი</span></div>
                    <div class="time-track"><span data-time-progress></span></div>
                </div>

                <div class="word-stage">
                    <p>აუხსენი შენს გუნდს</p>
                    <article class="word-card" data-word-card>
                        <span class="word-card__corner">ალიასი</span>
                        <strong data-current-word>სიტყვა</strong>
                        <small>არ გამოიყენო თვითონ სიტყვა ან მისი ფუძე</small>
                    </article>
                    <p class="play-message" data-play-message role="status"></p>
                </div>

                <div class="answer-buttons">
                    <button class="answer-button answer-button--skip" type="button" data-mark="skipped">
                        <span>↷</span>
                        <strong>გამოტოვება</strong>
                        <small data-skip-cost>0 ქულა</small>
                    </button>
                    <button class="answer-button answer-button--correct" type="button" data-mark="correct">
                        <span>✓</span>
                        <strong>გამოიცნო!</strong>
                        <small>+1 ქულა</small>
                    </button>
                </div>
            </section>

            <section class="screen screen--scroll screen--summary" data-screen="summary" aria-hidden="true">
                <header class="topbar topbar--sticky">
                    <span class="topbar__spacer"></span>
                    <strong>სვლის შედეგი</strong>
                    <span class="topbar__spacer"></span>
                </header>

                <div class="summary-hero">
                    <span>დრო ამოიწურა</span>
                    <strong data-summary-score>0</strong>
                    <small>ამ სვლის ქულა</small>
                    <div class="team-pill team-pill--small" data-summary-team>გუნდი</div>
                </div>

                <section class="review-section">
                    <div class="review-heading">
                        <h2>შეამოწმე პასუხები</h2>
                        <small>შეცდომის გასასწორებლად შეეხე სიტყვას</small>
                    </div>
                    <div class="review-list" data-review-list></div>
                    <div class="empty-review" data-empty-review hidden>ამ სვლაზე სიტყვა ვერ მოინიშნა.</div>
                </section>

                <section class="score-section">
                    <h2>საერთო ანგარიში</h2>
                    <div class="score-list" data-summary-scores></div>
                </section>

                <p class="form-error" data-summary-error role="alert"></p>
                <button class="button button--primary summary-next" type="button" data-next-turn>
                    <span>შემდეგი გუნდი</span><span class="button__icon">→</span>
                </button>
            </section>

            <section class="screen screen--final" data-screen="final" aria-hidden="true">
                <header class="topbar">
                    <div class="brand brand--light">
                        <span class="brand__mark brand__mark--light">ა</span>
                        <span>ალიასი</span>
                    </div>
                    <span></span>
                </header>

                <div class="final-content">
                    <div class="trophy" aria-hidden="true">★</div>
                    <p class="eyebrow eyebrow--mint" data-final-kicker>გამარჯვებულია</p>
                    <h2 data-winner-name>გუნდი</h2>
                    <p data-final-copy>ყველაზე მეტი სიტყვა გამოიცნო!</p>
                    <div class="final-scores" data-final-scores></div>
                </div>

                <div class="bottom-actions">
                    <button class="button button--mint" type="button" data-play-again>
                        <span>კიდევ თამაში</span><span class="button__icon">↻</span>
                    </button>
                    <button class="button button--dark-ghost" type="button" data-final-home>მთავარზე დაბრუნება</button>
                </div>
            </section>
        </main>

        <div class="rules-sheet" data-rules aria-hidden="true">
            <button class="rules-sheet__backdrop" type="button" data-close-rules tabindex="-1" aria-label="დახურვა"></button>
            <section class="rules-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="rules-title">
                <div class="sheet-handle" aria-hidden="true"></div>
                <div class="rules-heading">
                    <div>
                        <p class="eyebrow">თამაშის წესები</p>
                        <h2 id="rules-title">როგორ ვითამაშოთ ალიასი?</h2>
                    </div>
                    <button class="round-icon round-icon--light" type="button" data-close-rules aria-label="დახურვა">×</button>
                </div>
                <ol class="rules-list">
                    <li><span>1</span><p>გაიყავით 2–4 გუნდად. ყოველ სვლაზე ერთი მოთამაშე ხსნის ეკრანზე ნაჩვენებ სიტყვას.</p></li>
                    <li><span>2</span><p>არ შეიძლება თვითონ სიტყვის, მისი ნაწილის ან იმავე ფუძის მქონე სიტყვის გამოყენება.</p></li>
                    <li><span>3</span><p>სწორ პასუხზე დააჭირე „გამოიცნო“, რთული სიტყვისას — „გამოტოვება“. თითო სწორი პასუხი 1 ქულაა.</p></li>
                    <li><span>4</span><p>დროის დასრულების შემდეგ გადაამოწმე პასუხები. ყველა გუნდს თანაბარი რაოდენობის სვლა აქვს.</p></li>
                </ol>
                <div class="rule-example">
                    <small>მაგალითი</small>
                    <strong>სიტყვა: „ქოლგა“</strong>
                    <p>შეგიძლია თქვა: „წვიმის დროს თავზე იჭერ“.<br>არ შეიძლება თქვა: „საწვიმარი ქოლგა“.</p>
                </div>
            </section>
        </div>

        <script>
            window.ALIASI = @json($bootstrap);
        </script>
    </body>
</html>
