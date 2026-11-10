<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('question_type')->default('multiple_choice')->after('question_text');
        });

        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->text('answer_text')->nullable()->after('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropColumn('answer_text');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('question_type');
        });
    }
};
