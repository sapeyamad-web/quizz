<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $examAttempt->examSession->title }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            body {
                background: linear-gradient(180deg, #4a0d2d 0%, #2d0618 100%);
                margin: 0;
                min-height: 100%;
            }
            .quiz-shell {
                display: flex;
                flex-direction: column;
                height: 100vh;
                height: 100dvh;
                overflow: hidden;
            }
            .exam-header-wrap {
                flex: none;
                position: relative;
                z-index: 50;
            }
            .exam-content {
                flex: 1;
                min-height: 0;
                overflow-y: auto;
                overscroll-behavior: contain;
            }
            .answer-card {
                position: relative;
                min-height: 170px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 18px;
                border-radius: 22px;
                color: white;
                font-weight: 800;
                font-size: clamp(1.1rem, 2.1vw, 2.2rem);
                box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
                transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
            }
            .answer-card:hover {
                transform: translateY(-1px);
                box-shadow: 0 18px 30px rgba(0,0,0,0.12);
            }
            .answer-card input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }
            .answer-card label {
                cursor: pointer;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                padding: 18px 16px;
                text-align: center;
            }
            .answer-card .index-badge {
                position: absolute;
                top: 12px;
                right: 12px;
                width: 28px;
                height: 28px;
                border-radius: 8px;
                background: rgba(255,255,255,0.18);
                border: 1px solid rgba(255,255,255,0.25);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: 700;
                line-height: 1;
            }
            .answer-card label::before {
                content: '✓';
                position: absolute;
                top: 12px;
                left: 12px;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: rgba(255,255,255,0.22);
                border: 1px solid rgba(255,255,255,0.3);
                display: none;
                align-items: center;
                justify-content: center;
                font-size: 0.75rem;
                font-weight: 900;
            }
            .answer-card input:checked + label::before {
                display: flex;
            }
            .answer-card.selected {
                box-shadow: 0 0 0 4px rgba(255,255,255,0.75), 0 0 0 8px rgba(255, 212, 98, 0.2), 0 18px 30px rgba(0,0,0,0.18);
                transform: translateY(-1px);
                filter: saturate(1.15);
            }
            .question-box {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 240px;
                text-align: center;
                width: 100%;
            }
            .question-title {
                max-width: 1200px;
                margin: 0 auto;
            }
            .exam-status-bar {
                position: relative;
                z-index: 40;
            }
            @media (max-width: 1023px) {
                .question-box {
                    min-height: 0;
                    justify-content: center;
                    text-align: center;
                }
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="quiz-shell relative overflow-x-hidden">
            <div class="exam-header-wrap">
                <div class="border-b border-[#6d1744] bg-[#3a0029]/95 backdrop-blur-sm shadow-lg shadow-[#1a020b]/20">
                    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3 text-white">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 ring-1 ring-white/20 text-sm font-bold">?</div>
                            <div class="text-sm font-semibold">{{ $examAttempt->user_name }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="inline-flex items-center rounded-full bg-[#13050d] px-4 py-2 text-sm font-bold text-[#fbeab8] ring-1 ring-[#7a2149]">
                                <span class="mr-2 text-[#f4d474]">1</span>
                                / {{ $examAttempt->examSession->questions->count() }}
                            </div>
                            <button id="fullscreen-btn" type="button" class="rounded-xl border border-white/20 bg-white/10 px-3 py-2 text-sm font-semibold text-white transition hover:bg-white/20" aria-pressed="false">Layar penuh</button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="exam-content">
                <div class="mx-auto max-w-7xl px-3 pb-2 pt-5 sm:px-6 lg:px-8">
                    <div class="exam-status-bar mb-5 flex items-center justify-between gap-3 rounded-2xl border border-[#7a2149] bg-[#4b0c33]/60 px-4 py-3 shadow-lg shadow-[#250613]/20 backdrop-blur-sm">
                    <div class="text-sm text-white/80">Sesi: <span class="font-semibold text-white">{{ $examAttempt->examSession->title }}</span></div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl border border-[#7a2149] bg-[#1a0611] px-3 py-2 text-right">
                            <div class="text-[9px] uppercase tracking-[0.2em] text-[#f5bc5a]">Timer</div>
                            <div id="timer" class="text-2xl font-black text-[#f3cf73]">{{ gmdate('i:s', $remainingSeconds) }}</div>
                        </div>
                        <button id="submit-btn" type="button" class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:from-emerald-400 hover:to-teal-400">Selesai</button>
                    </div>
                    </div>

                    <div id="warning-banner" class="mb-5 hidden rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 shadow-sm">
                    <span id="warning-text">Peringatan: jangan terlalu sering keluar dari tab.</span>
                </div>

                <form id="exam-form" method="POST" action="{{ route('exam.submit', $examAttempt) }}">
                    @csrf
                    <div class="space-y-6">
                        @foreach ($examAttempt->examSession->questions as $index => $question)
                            @php
                                $answers = $examAttempt->answers->firstWhere('question_id', $question->id)?->selected_choice_ids ?? [];
                                $choicePalette = ['#b8d95f', '#c289f2', '#f7a64b', '#56c7ca'];
                            @endphp

                            <div class="rounded-[26px] border border-[#7a2149] bg-[#4b0c33]/50 p-4 shadow-xl shadow-[#2d051b]/20 sm:p-5">
                                <div class="mb-5 flex items-center justify-center gap-2 rounded-full bg-[#230914] px-3 py-2 text-sm font-semibold text-[#f9d792] ring-1 ring-[#7a2149]">
                                    <span>{{ $index + 1 }}</span>
                                    <span class="text-white/60">/</span>
                                    <span>{{ $examAttempt->examSession->questions->count() }}</span>
                                </div>

                                @if ($question->image_path)
                                    <div class="grid gap-5 lg:grid-cols-[minmax(260px,430px)_1fr] lg:items-center">
                                        <div class="overflow-hidden rounded-[20px] border border-white/20 bg-white/90 p-3 shadow-inner shadow-[#d9d9d9]/60">
                                            <img src="{{ Storage::disk('public')->url($question->image_path) }}" alt="Question illustration" class="h-[260px] w-full rounded-[14px] object-contain bg-white">
                                        </div>

                                        <div class="question-box">
                                            <h2 class="question-title text-3xl font-black leading-relaxed text-white sm:text-[2.4rem] lg:text-[2.7rem]">{{ $question->question_text }}</h2>
                                        </div>
                                    </div>
                                @else
                                    <div class="question-box py-8">
                                        <h2 class="question-title text-3xl font-black leading-relaxed text-white sm:text-[2.4rem] lg:text-[2.7rem]">{{ $question->question_text }}</h2>
                                    </div>
                                @endif

                                @if ($question->isEssay())
                                    @php
                                        $essayAnswer = $examAttempt->answers->firstWhere('question_id', $question->id)?->answer_text ?? '';
                                    @endphp

                                    <div class="mt-6 rounded-3xl border border-[#7a2149] bg-[#220914] p-4">
                                        <textarea id="essay-{{ $question->id }}" name="essay_answers[{{ $question->id }}]" rows="6" class="w-full rounded-2xl border border-[#7a2149] bg-[#14050d] px-4 py-3 text-base text-white placeholder:text-slate-400 focus:border-[#f3cf73] focus:outline-none" placeholder="Tulis jawaban Anda di sini...">{{ $essayAnswer }}</textarea>
                                    </div>
                                @else
                                    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        @foreach ($question->choices as $choice)
                                            @php
                                                $checked = in_array((string) $choice->id, array_map('strval', $answers), true);
                                                $color = $choicePalette[$loop->index % count($choicePalette)];
                                            @endphp

                                            <div class="answer-card {{ $checked ? 'selected' : '' }}" style="background: {{ $color }};">
                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $choice->id }}" id="choice-{{ $question->id }}-{{ $choice->id }}" @checked($checked)>
                                                <label for="choice-{{ $question->id }}-{{ $choice->id }}" class="w-full h-full">
                                                    <span class="index-badge">{{ $loop->index + 1 }}</span>
                                                    <span>{{ $choice->choice_text }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script>
            const examAttemptId = {{ $examAttempt->id }};
            const quizShell = document.querySelector('.quiz-shell');
            const fullscreenBtn = document.getElementById('fullscreen-btn');
            const timerEl = document.getElementById('timer');
            const form = document.getElementById('exam-form');
            const submitBtn = document.getElementById('submit-btn');
            const warningBanner = document.getElementById('warning-banner');
            const warningText = document.getElementById('warning-text');
            let remainingSeconds = {{ $remainingSeconds }};
            const maxTabSwitch = {{ $examAttempt->examSession->tab_switch_limit }};
            let tabSwitchCount = {{ $examAttempt->tab_switch_count ?? 0 }};
            let isSubmissionInProgress = false;

            function updateFullscreenButton() {
                const isFullscreen = document.fullscreenElement === quizShell;
                fullscreenBtn.textContent = isFullscreen ? 'Keluar layar penuh' : 'Layar penuh';
                fullscreenBtn.setAttribute('aria-pressed', String(isFullscreen));
            }

            fullscreenBtn.addEventListener('click', async () => {
                try {
                    if (document.fullscreenElement === quizShell) {
                        await document.exitFullscreen();
                    } else {
                        await quizShell.requestFullscreen();
                    }
                } catch {
                    showWarning('Mode layar penuh tidak dapat diaktifkan di browser ini.', 'warning');
                }
            });

            document.addEventListener('fullscreenchange', updateFullscreenButton);
            if (typeof quizShell.requestFullscreen !== 'function') {
                fullscreenBtn.hidden = true;
            }

            window.addEventListener('beforeunload', (event) => {
                if (!isSubmissionInProgress) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });

            function updateTimer() {
                const minutes = String(Math.floor(remainingSeconds / 60)).padStart(2, '0');
                const seconds = String(remainingSeconds % 60).padStart(2, '0');
                timerEl.textContent = `${minutes}:${seconds}`;
                if (remainingSeconds <= 30) {
                    timerEl.classList.add('text-red-400');
                    timerEl.classList.remove('text-[#f3cf73]');
                }
            }

            function showWarning(message, type = 'warning') {
                warningBanner.classList.remove('hidden');
                warningBanner.classList.remove('border-amber-200', 'bg-amber-50', 'text-amber-900', 'border-red-200', 'bg-red-50', 'text-red-900');
                if (type === 'danger') {
                    warningBanner.classList.add('border-red-200', 'bg-red-50', 'text-red-900');
                } else {
                    warningBanner.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-900');
                }
                warningText.textContent = message;
            }

            function hideWarning() {
                warningBanner.classList.add('hidden');
            }

            function rebindSelectionStates() {
                document.querySelectorAll('.answer-card input[type="checkbox"]').forEach((input) => {
                    const card = input.closest('.answer-card');
                    card.classList.toggle('selected', input.checked);
                });
            }

            function finalizeSubmission(autoSubmit = true) {
                if (isSubmissionInProgress) {
                    return;
                }

                isSubmissionInProgress = true;
                fetch(`/exam/${examAttemptId}/submit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ auto_submit: autoSubmit })
                }).then((response) => {
                    if (!response.ok) {
                        throw new Error('Submission failed');
                    }

                    window.location.href = `/exam/${examAttemptId}/result`;
                }).catch(() => {
                    isSubmissionInProgress = false;
                    showWarning('Jawaban belum berhasil dikirim. Periksa koneksi, lalu coba lagi.', 'danger');
                });
            }

            function saveCurrentAnswers(tabSwitch = false) {
                const payload = new FormData(form);
                if (tabSwitch) {
                    payload.append('tab_switch', '1');
                }

                return fetch(`/exam/${examAttemptId}/answer`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: payload
                }).then(async (response) => {
                    const data = await response.json();
                    if (data.status === 'submitted') {
                        isSubmissionInProgress = true;
                        window.location.href = data.redirect;
                        return data;
                    }

                    if (tabSwitch && typeof data.tab_switch_count !== 'undefined') {
                        tabSwitchCount = Number(data.tab_switch_count) || tabSwitchCount;
                        if (tabSwitchCount >= maxTabSwitch) {
                            showWarning('Batas pindah tab sudah tercapai. Ujian akan otomatis dikumpulkan sekarang.', 'danger');
                            finalizeSubmission();
                        } else {
                            const remaining = Math.max(0, maxTabSwitch - tabSwitchCount);
                            showWarning(`Peringatan: Anda sudah keluar tab ${tabSwitchCount}/${maxTabSwitch}. Sisa kesempatan: ${remaining}.`);
                        }
                    }

                    return data;
                });
            }

            setInterval(() => {
                if (remainingSeconds > 0) {
                    remainingSeconds -= 1;
                    updateTimer();
                }
                if (remainingSeconds <= 0) {
                    finalizeSubmission();
                }
            }, 1000);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    saveCurrentAnswers(true);
                }
            });

            submitBtn.addEventListener('click', () => finalizeSubmission(false));

            document.querySelectorAll('.answer-card input').forEach((input) => {
                input.addEventListener('change', () => {
                    rebindSelectionStates();
                    saveCurrentAnswers(false);
                });
            });

            document.querySelectorAll('textarea[name^="essay_answers"]').forEach((textarea) => {
                textarea.addEventListener('input', () => {
                    saveCurrentAnswers(false);
                });
            });

            updateTimer();
            hideWarning();
        </script>
    </body>
</html>
