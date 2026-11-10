<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-indigo-600">Session detail</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $examSession->title }}</h2>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.sessions.results', $examSession) }}" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">Lihat hasil</a>
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Informasi sesi</p>
                        <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $examSession->title }}</h3>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $examSession->status }}</span>
                </div>

                <div class="space-y-5 text-sm text-slate-600">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl bg-gradient-to-br from-slate-50 to-white p-4 ring-1 ring-slate-200"><p class="text-slate-500">Durasi</p><p class="mt-2 text-lg font-bold text-slate-900">{{ $examSession->duration_minutes }} min</p></div>
                        <div class="rounded-2xl bg-gradient-to-br from-slate-50 to-white p-4 ring-1 ring-slate-200"><p class="text-slate-500">Batas tab</p><p class="mt-2 text-lg font-bold text-slate-900">{{ $examSession->tab_switch_limit }}x</p></div>
                        <div class="rounded-2xl bg-gradient-to-br from-slate-50 to-white p-4 ring-1 ring-slate-200"><p class="text-slate-500">Total soal</p><p class="mt-2 text-lg font-bold text-slate-900">{{ $examSession->questions->count() }}</p></div>
                    </div>
                    <p>{{ $examSession->description ?: 'Belum ada deskripsi untuk sesi ini.' }}</p>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    <h4 class="mb-4 text-lg font-bold text-slate-900">Tambah soal baru</h4>

                    <form method="POST" action="{{ route('admin.sessions.questions.store', $examSession) }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Pertanyaan</label>
                            <textarea name="question_text" rows="4" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none" placeholder="Tulis soal di sini..."></textarea>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Gambar pendukung (opsional)</label>
                            <input type="file" name="image" accept="image/*" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Poin soal</label>
                            <input type="number" name="points" min="1" max="100" value="1" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Tipe soal</label>
                            <select id="question-type" name="question_type" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-indigo-500 focus:outline-none">
                                <option value="multiple_choice">Pilihan ganda</option>
                                <option value="essay">Esai</option>
                            </select>
                        </div>

                        <div id="multiple-choice-options" class="grid gap-4 md:grid-cols-2">
                            @for ($i = 1; $i <= 4; $i++)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="mb-3 flex items-center justify-between">
                                        <label class="text-sm font-medium text-slate-700">Jawaban {{ $i }}</label>
                                        <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                            <input type="checkbox" name="choices[{{ $i - 1 }}][is_correct]" value="1" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                            Benar
                                        </label>
                                    </div>
                                    <input type="text" name="choices[{{ $i - 1 }}][text]" required class="choice-input w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-900 focus:border-indigo-500 focus:outline-none" placeholder="Pilihan jawaban {{ $i }}">
                                </div>
                            @endfor
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-500 hover:to-violet-500">Simpan soal</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="text-lg font-bold text-slate-900">Live progress</h4>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Realtime</span>
                </div>

                <div id="live-progress" class="space-y-4"></div>
            </div>
        </div>
    </div>

    <script>
        const sessionId = {{ $examSession->id }};
        const progressContainer = document.getElementById('live-progress');
        const questionTypeSelect = document.getElementById('question-type');
        const multipleChoiceOptions = document.getElementById('multiple-choice-options');
        const choiceInputs = document.querySelectorAll('.choice-input');

        function updateQuestionTypeFields() {
            const isEssay = questionTypeSelect.value === 'essay';
            multipleChoiceOptions.classList.toggle('hidden', isEssay);

            choiceInputs.forEach((input) => {
                input.required = !isEssay;
            });
        }

        questionTypeSelect.addEventListener('change', updateQuestionTypeFields);
        updateQuestionTypeFields();

        async function loadLiveProgress() {
            try {
                const response = await fetch(`/admin/sessions/${sessionId}/live`);
                const data = await response.json();

                progressContainer.innerHTML = '';

                if (!data.attempts.length) {
                    progressContainer.innerHTML = '<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-500">Belum ada peserta yang mulai ujian.</div>';
                    return;
                }

                data.attempts.forEach((attempt) => {
                    const card = document.createElement('div');
                    card.className = 'rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4';
                    card.innerHTML = `
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">${attempt.user_name}</p>
                                <p class="text-xs text-slate-500">${attempt.started_at ?? 'Belum mulai'}</p>
                            </div>
                            <span class="rounded-full bg-indigo-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-indigo-700">${attempt.status}</span>
                        </div>
                        <div class="mb-2 h-2.5 w-full overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-violet-500" style="width: ${attempt.progress}%"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Progress ${attempt.progress}%</span>
                            <span>Tab switch: ${attempt.tab_switch_count}</span>
                        </div>
                    `;
                    progressContainer.appendChild(card);
                });
            } catch (error) {
                console.error('Could not load live progress', error);
            }
        }

        loadLiveProgress();
        setInterval(loadLiveProgress, 5000);
    </script>
</x-app-layout>
