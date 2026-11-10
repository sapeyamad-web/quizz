<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ExamSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_list_shows_a_delete_action_for_each_participant(): void
    {
        $examSession = ExamSession::create([
            'title' => 'Ujian Daftar Hasil',
            'slug' => 'ujian-daftar-hasil',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $attempt = $examSession->attempts()->create([
            'user_name' => 'Peserta',
            'status' => 'submitted',
            'score' => 1,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.sessions.results', $examSession))
            ->assertOk()
            ->assertSee(route('admin.sessions.attempts.destroy', [$examSession, $attempt]), false)
            ->assertSee('Hapus hasil ujian peserta ini? Jawaban peserta juga akan dihapus.')
            ->assertSee('Hapus');
    }

    public function test_admin_can_delete_a_participant_result_and_its_answers(): void
    {
        $examSession = ExamSession::create([
            'title' => 'Ujian Hapus Hasil',
            'slug' => 'ujian-hapus-hasil',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $question = $examSession->questions()->create([
            'question_text' => 'Pertanyaan?',
            'question_type' => 'essay',
            'points' => 1,
            'sort_order' => 1,
        ]);
        $attempt = $examSession->attempts()->create([
            'user_name' => 'Peserta Hapus',
            'status' => 'submitted',
            'score' => 1,
        ]);
        $answer = $attempt->answers()->create([
            'question_id' => $question->id,
            'answer_text' => 'Jawaban',
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->delete(route('admin.sessions.attempts.destroy', [$examSession, $attempt]));

        $response->assertRedirect(route('admin.sessions.results', $examSession));
        $response->assertSessionHas('success', 'Hasil peserta berhasil dihapus.');
        $this->assertDatabaseMissing('exam_attempts', ['id' => $attempt->id]);
        $this->assertDatabaseMissing('attempt_answers', ['id' => $answer->id]);
    }

    public function test_admin_cannot_delete_a_result_from_another_exam_session(): void
    {
        $examSession = ExamSession::create([
            'title' => 'Sesi Admin',
            'slug' => 'sesi-admin',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $otherSession = ExamSession::create([
            'title' => 'Sesi Lain',
            'slug' => 'sesi-lain',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $attempt = $otherSession->attempts()->create([
            'user_name' => 'Peserta Sesi Lain',
            'status' => 'submitted',
            'score' => 1,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.sessions.attempts.destroy', [$examSession, $attempt]))
            ->assertNotFound();

        $this->assertDatabaseHas('exam_attempts', ['id' => $attempt->id]);
    }

    public function test_guest_cannot_delete_a_participant_result(): void
    {
        $examSession = ExamSession::create([
            'title' => 'Ujian Hasil Privat',
            'slug' => 'ujian-hasil-privat',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $attempt = $examSession->attempts()->create([
            'user_name' => 'Peserta',
            'status' => 'submitted',
            'score' => 1,
        ]);

        $this->delete(route('admin.sessions.attempts.destroy', [$examSession, $attempt]))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('exam_attempts', ['id' => $attempt->id]);
    }

    public function test_expired_attempt_is_submitted_when_opening_the_exam(): void
    {
        $this->travelTo(now());
        $examSession = ExamSession::create([
            'title' => 'Ujian Kedaluwarsa',
            'slug' => 'ujian-kedaluwarsa',
            'duration_minutes' => 5,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $examAttempt = $examSession->attempts()->create([
            'user_name' => 'Peserta',
            'status' => 'in_progress',
            'started_at' => now()->subMinutes(6),
            'score' => 0,
        ]);

        $this->get(route('exam.take', $examAttempt))
            ->assertRedirect(route('exam.result', $examAttempt));

        $this->assertDatabaseHas('exam_attempts', [
            'id' => $examAttempt->id,
            'status' => 'submitted',
        ]);
        $this->assertNotNull($examAttempt->fresh()->submitted_at);
    }

    public function test_export_includes_each_participants_question_and_answer(): void
    {
        $examSession = ExamSession::create([
            'title' => 'Simulasi Kuis',
            'slug' => 'simulasi-kuis',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);
        $choiceQuestion = $examSession->questions()->create([
            'question_text' => 'Warna langit?',
            'question_type' => 'multiple_choice',
            'points' => 1,
            'sort_order' => 1,
        ]);
        $correctChoice = $choiceQuestion->choices()->create([
            'choice_text' => 'Biru',
            'is_correct' => true,
            'sort_order' => 1,
        ]);
        $incorrectChoice = $choiceQuestion->choices()->create([
            'choice_text' => 'Merah',
            'is_correct' => false,
            'sort_order' => 2,
        ]);
        $essayQuestion = $examSession->questions()->create([
            'question_text' => 'Jelaskan alasanmu.',
            'question_type' => 'essay',
            'points' => 2,
            'sort_order' => 2,
        ]);
        $alice = $examSession->attempts()->create([
            'user_name' => 'Alice',
            'status' => 'submitted',
            'score' => 1,
        ]);
        $alice->answers()->create([
            'question_id' => $choiceQuestion->id,
            'selected_choice_ids' => [$correctChoice->id],
        ]);
        $alice->answers()->create([
            'question_id' => $essayQuestion->id,
            'answer_text' => 'Karena langit memantulkan cahaya.',
            'selected_choice_ids' => [],
        ]);
        $bob = $examSession->attempts()->create([
            'user_name' => 'Bob',
            'status' => 'submitted',
            'score' => 0,
        ]);
        $bob->answers()->create([
            'question_id' => $choiceQuestion->id,
            'selected_choice_ids' => [$incorrectChoice->id],
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('admin.sessions.results.export', $examSession));

        $response->assertDownload('simulasi-kuis-hasil-ujian.csv');
        $csv = substr($response->streamedContent(), 3);
        $rows = array_map(
            static fn (string $line): array => str_getcsv($line),
            array_filter(explode("\n", trim($csv))),
        );

        $this->assertCount(5, $rows);
        $this->assertSame('Nama Peserta', $rows[0][1]);
        $this->assertSame('Jawaban Peserta', $rows[0][10]);
        $rowsByParticipantAndQuestion = [];

        foreach (array_slice($rows, 1) as $row) {
            $rowsByParticipantAndQuestion[$row[1]][$row[7]] = $row;
        }

        $this->assertSame('Biru', $rowsByParticipantAndQuestion['Alice']['1'][10]);
        $this->assertSame('Biru', $rowsByParticipantAndQuestion['Alice']['1'][11]);
        $this->assertSame('Benar', $rowsByParticipantAndQuestion['Alice']['1'][12]);
        $this->assertSame('1', $rowsByParticipantAndQuestion['Alice']['1'][3]);
        $this->assertSame('Karena langit memantulkan cahaya.', $rowsByParticipantAndQuestion['Alice']['2'][10]);
        $this->assertSame('Perlu penilaian', $rowsByParticipantAndQuestion['Alice']['2'][12]);
        $this->assertSame('Merah', $rowsByParticipantAndQuestion['Bob']['1'][10]);
        $this->assertSame('Salah', $rowsByParticipantAndQuestion['Bob']['1'][12]);
        $this->assertSame('Belum dijawab', $rowsByParticipantAndQuestion['Bob']['2'][12]);
    }

    public function test_uploaded_question_image_uses_public_disk_url_in_the_exam(): void
    {
        Storage::fake('public');
        $this->withoutVite();

        $examSession = ExamSession::create([
            'title' => 'Kuis Gambar',
            'slug' => 'kuis-gambar',
            'duration_minutes' => 30,
            'tab_switch_limit' => 3,
            'status' => 'published',
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('admin.sessions.show', $examSession))
            ->post(route('admin.sessions.questions.store', $examSession), [
                'question_text' => 'Apa yang terlihat?',
                'question_type' => 'multiple_choice',
                'image' => UploadedFile::fake()->image('diagram.png'),
                'choices' => [
                    ['text' => 'Pilihan A', 'is_correct' => '1'],
                    ['text' => 'Pilihan B'],
                ],
            ])
            ->assertRedirect(route('admin.sessions.show', $examSession))
            ->assertSessionHasNoErrors();

        $question = $examSession->questions()->firstOrFail();
        Storage::disk('public')->assertExists($question->image_path);
        $examAttempt = $examSession->attempts()->create([
            'user_name' => 'Peserta',
            'status' => 'in_progress',
            'started_at' => now(),
            'score' => 0,
        ]);

        $this->get(route('exam.take', $examAttempt))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($question->image_path), false);
    }
}
