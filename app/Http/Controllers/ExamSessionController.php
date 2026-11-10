<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Models\ExamSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSessionController extends Controller
{
    public function home()
    {
        $sessions = ExamSession::where('status', 'published')
            ->withCount('questions')
            ->latest()
            ->get();

        return view('home', compact('sessions'));
    }

    public function dashboard()
    {
        $sessions = ExamSession::withCount(['questions', 'attempts'])->latest()->get();

        return view('admin.dashboard', compact('sessions'));
    }

    public function create()
    {
        return view('admin.sessions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'tab_switch_limit' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $session = ExamSession::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.now()->format('YmdHis'),
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'tab_switch_limit' => $validated['tab_switch_limit'],
            'status' => 'published',
        ]);

        return redirect()->route('admin.sessions.show', $session)->with('success', 'Sesi ujian berhasil dibuat.');
    }

    public function publicShow(ExamSession $examSession)
    {
        $examSession->load('questions.choices');

        return view('sessions.show', compact('examSession'));
    }

    public function show(ExamSession $examSession)
    {
        $examSession->load(['questions.choices', 'attempts' => fn ($query) => $query->latest()]);

        return view('admin.session.show', compact('examSession'));
    }

    public function results(ExamSession $examSession)
    {
        $attempts = $examSession->attempts()->with('examSession')->latest('submitted_at')->latest()->get();

        return view('admin.session.results', compact('examSession', 'attempts'));
    }

    public function destroyAttempt(ExamSession $examSession, ExamAttempt $examAttempt): RedirectResponse
    {
        $examSession->attempts()->findOrFail($examAttempt->id)->delete();

        return redirect()
            ->route('admin.sessions.results', $examSession)
            ->with('success', 'Hasil peserta berhasil dihapus.');
    }

    public function exportResults(ExamSession $examSession)
    {
        $examSession->load('questions.choices');
        $questions = $examSession->questions;

        $filename = Str::slug($examSession->title).'-hasil-ujian.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->streamDownload(function () use ($examSession, $questions) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No Peserta',
                'Nama Peserta',
                'Status',
                'Skor Total',
                'Tab Switch',
                'Mulai',
                'Selesai',
                'No Soal',
                'Tipe Soal',
                'Soal',
                'Jawaban Peserta',
                'Jawaban Benar',
                'Status Jawaban',
                'Poin Soal',
            ]);

            $spreadsheetSafeText = static function (?string $value): string {
                $value ??= '';

                return preg_match('/^[\t\r\n ]*[=+\-@]/', $value) === 1 ? "'".$value : $value;
            };
            $attempts = $examSession->attempts()
                ->with('answers')
                ->orderBy('submitted_at')
                ->orderBy('id')
                ->lazy();
            $participantNumber = 0;

            foreach ($attempts as $attempt) {
                $participantNumber++;
                $participantDetails = [
                    $participantNumber,
                    $spreadsheetSafeText($attempt->user_name),
                    $attempt->status,
                    $attempt->score ?? 0,
                    $attempt->tab_switch_count ?? 0,
                    $attempt->started_at?->format('d-m-Y H:i:s') ?? '-',
                    $attempt->submitted_at?->format('d-m-Y H:i:s') ?? '-',
                ];

                if ($questions->isEmpty()) {
                    fputcsv($file, [...$participantDetails, '', '', '', '', '', '', '']);

                    continue;
                }

                $answersByQuestionId = $attempt->answers->keyBy('question_id');

                foreach ($questions as $questionIndex => $question) {
                    $answer = $answersByQuestionId->get($question->id);
                    $choicesById = $question->choices->keyBy('id');
                    $selectedChoiceIds = array_map('intval', $answer?->selected_choice_ids ?? []);
                    sort($selectedChoiceIds, SORT_NUMERIC);
                    $selectedAnswer = $question->isEssay()
                        ? ($answer?->answer_text ?? '')
                        : collect($selectedChoiceIds)
                            ->map(fn (int $choiceId) => $choicesById->get($choiceId)?->choice_text)
                            ->filter(fn (?string $choiceText) => $choiceText !== null)
                            ->implode(' | ');
                    $correctChoiceIds = $question->choices
                        ->filter(fn ($choice) => (bool) $choice->is_correct)
                        ->pluck('id')
                        ->map(fn ($choiceId) => (int) $choiceId)
                        ->sort()
                        ->values()
                        ->all();
                    $correctAnswer = $question->choices
                        ->filter(fn ($choice) => (bool) $choice->is_correct)
                        ->pluck('choice_text')
                        ->implode(' | ');

                    if ($question->isEssay()) {
                        $answerStatus = $selectedAnswer === '' ? 'Belum dijawab' : 'Perlu penilaian';
                        $correctAnswer = '';
                    } elseif ($selectedChoiceIds === []) {
                        $answerStatus = 'Belum dijawab';
                    } else {
                        $answerStatus = $selectedChoiceIds === $correctChoiceIds ? 'Benar' : 'Salah';
                    }

                    fputcsv($file, [
                        ...$participantDetails,
                        $questionIndex + 1,
                        $question->question_type === 'essay' ? 'Esai' : 'Pilihan ganda',
                        $spreadsheetSafeText($question->question_text),
                        $spreadsheetSafeText($selectedAnswer),
                        $spreadsheetSafeText($correctAnswer),
                        $answerStatus,
                        $question->points,
                    ]);
                }
            }

            fclose($file);
        }, $filename, $headers);
    }

    public function storeQuestion(Request $request, ExamSession $examSession)
    {
        $questionType = $request->input('question_type', 'multiple_choice');

        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,essay'],
            'image' => ['nullable', 'image', 'max:2048'],
            'choices' => ['nullable', 'array'],
            'choices.*.text' => ['nullable', 'string', 'max:255'],
            'choices.*.is_correct' => ['nullable', 'boolean'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($questionType === 'multiple_choice') {
            $request->validate([
                'choices' => ['required', 'array', 'min:2'],
                'choices.*.text' => ['required', 'string', 'max:255'],
            ]);
        }

        $question = $examSession->questions()->create([
            'question_text' => $validated['question_text'],
            'question_type' => $questionType,
            'points' => $validated['points'] ?? 1,
            'sort_order' => $examSession->questions()->count() + 1,
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('question-images', 'public');
            $question->update(['image_path' => $path]);
        }

        if ($questionType === 'multiple_choice') {
            foreach ($validated['choices'] as $index => $choice) {
                $question->choices()->create([
                    'choice_text' => $choice['text'],
                    'is_correct' => (bool) ($choice['is_correct'] ?? false),
                    'sort_order' => $index + 1,
                ]);
            }
        }

        $message = $questionType === 'essay' ? 'Soal esai berhasil ditambahkan.' : 'Soal berhasil ditambahkan.';

        return back()->with('success', $message);
    }

    public function live(ExamSession $examSession)
    {
        $attempts = $examSession->attempts()->latest()->get()->map(function ($attempt) {
            $totalQuestions = $examSession->questions()->count();
            $answered = $attempt->answers()->count();

            return [
                'id' => $attempt->id,
                'user_name' => $attempt->user_name,
                'status' => $attempt->status,
                'progress' => $totalQuestions > 0 ? round(($answered / $totalQuestions) * 100) : 0,
                'remaining' => (int) $attempt->remaining_seconds,
                'tab_switch_count' => $attempt->tab_switch_count,
                'started_at' => $attempt->started_at?->format('d M Y H:i:s'),
                'submitted_at' => $attempt->submitted_at?->format('d M Y H:i:s'),
            ];
        });

        return response()->json([
            'session' => [
                'title' => $examSession->title,
                'status' => $examSession->status,
                'total_questions' => $examSession->questions()->count(),
            ],
            'attempts' => $attempts,
        ]);
    }

    public function start(Request $request, ExamSession $examSession)
    {
        $userName = trim((string) $request->input('user_name', 'Peserta')) ?: 'Peserta';

        $attempt = ExamAttempt::create([
            'exam_session_id' => $examSession->id,
            'user_name' => $userName,
            'status' => 'in_progress',
            'started_at' => now(),
            'last_seen_at' => now(),
            'tab_switch_count' => 0,
            'score' => 0,
        ]);

        return redirect()->route('exam.take', $attempt);
    }

    public function take(ExamAttempt $examAttempt)
    {
        $examAttempt->load(['examSession.questions.choices', 'answers']);

        if ($examAttempt->status === 'submitted') {
            return redirect()->route('exam.result', $examAttempt);
        }

        if (! $examAttempt->started_at) {
            $examAttempt->update(['started_at' => now(), 'status' => 'in_progress', 'last_seen_at' => now()]);
        }

        $remainingSeconds = $examAttempt->remaining_seconds;

        if ($remainingSeconds <= 0) {
            $this->finalizeSubmission($examAttempt, autoSubmitted: true);

            return redirect()->route('exam.result', $examAttempt);
        }

        return view('exam.take', compact('examAttempt', 'remainingSeconds'));
    }

    public function saveAnswer(Request $request, ExamAttempt $examAttempt)
    {
        $selectedAnswers = $request->input('answers', []);
        $essayAnswers = $request->input('essay_answers', []);

        foreach ($examAttempt->examSession->questions as $question) {
            if ($question->isEssay()) {
                $answerText = trim((string) ($essayAnswers[$question->id] ?? ''));

                if ($answerText === '') {
                    $examAttempt->answers()->where('question_id', $question->id)->delete();

                    continue;
                }

                $examAttempt->answers()->updateOrCreate(
                    ['exam_attempt_id' => $examAttempt->id, 'question_id' => $question->id],
                    ['answer_text' => $answerText, 'selected_choice_ids' => []]
                );

                continue;
            }

            $selected = $selectedAnswers[$question->id] ?? [];
            if (is_string($selected)) {
                $selected = [$selected];
            }

            $payload = array_values(array_filter(array_map('intval', (array) $selected)));

            if (empty($payload)) {
                $examAttempt->answers()->where('question_id', $question->id)->delete();

                continue;
            }

            $examAttempt->answers()->updateOrCreate(
                ['exam_attempt_id' => $examAttempt->id, 'question_id' => $question->id],
                ['selected_choice_ids' => $payload, 'answer_text' => null]
            );
        }

        $tabSwitchTriggered = false;
        if ($request->boolean('tab_switch')) {
            $examAttempt->increment('tab_switch_count');

            if ($examAttempt->tab_switch_count >= $examAttempt->examSession->tab_switch_limit) {
                $tabSwitchTriggered = true;
                $this->finalizeSubmission($examAttempt, autoSubmitted: true);

                return response()->json([
                    'status' => 'submitted',
                    'redirect' => route('exam.result', $examAttempt),
                    'auto_submitted' => true,
                    'tab_switch_count' => $examAttempt->tab_switch_count,
                ]);
            }
        }

        $examAttempt->update(['last_seen_at' => now()]);

        return response()->json([
            'status' => 'saved',
            'tab_switch_triggered' => $tabSwitchTriggered,
            'tab_switch_count' => $examAttempt->tab_switch_count,
        ]);
    }

    public function submit(Request $request, ExamAttempt $examAttempt)
    {
        if ($examAttempt->status === 'submitted') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'already_submitted', 'redirect' => route('exam.result', $examAttempt)]);
            }

            return redirect()->route('exam.result', $examAttempt);
        }

        $this->finalizeSubmission($examAttempt, autoSubmitted: $request->boolean('auto_submit'));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => 'submitted', 'redirect' => route('exam.result', $examAttempt)]);
        }

        return redirect()->route('exam.result', $examAttempt);
    }

    public function result(ExamAttempt $examAttempt)
    {
        $examAttempt->load(['examSession.questions.choices', 'answers.question']);

        return view('exam.result', compact('examAttempt'));
    }

    protected function finalizeSubmission(ExamAttempt $examAttempt, bool $autoSubmitted = false): void
    {
        if ($examAttempt->status === 'submitted') {
            return;
        }

        $examAttempt->status = 'submitted';
        $examAttempt->submitted_at = now();
        $examAttempt->score = $this->calculateScore($examAttempt);
        $examAttempt->save();
    }

    protected function calculateScore(ExamAttempt $examAttempt): int
    {
        $score = 0;

        foreach ($examAttempt->examSession->questions as $question) {
            if ($question->isEssay()) {
                continue;
            }

            $correctIds = $question->choices()->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
            $selectedIds = $examAttempt->answers()->where('question_id', $question->id)->first()?->selected_choice_ids ?? [];
            $selectedIds = array_map('intval', $selectedIds);
            sort($selectedIds);

            if ($selectedIds === $correctIds) {
                $score += (int) $question->points;
            }
        }

        return $score;
    }
}
