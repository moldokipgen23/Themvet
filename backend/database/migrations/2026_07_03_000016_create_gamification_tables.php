<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Streaks
        Schema::create('user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->timestamp('last_activity_date');
            $table->timestamps();

            $table->unique('user_id');
        });

        // User Badges
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->string('icon');
            $table->string('color')->default('#6C63FF');
            $table->string('category')->default('general');
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_id']);
        });

        // User Stats
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('total_tests_taken')->default(0);
            $table->integer('total_questions_attempted')->default(0);
            $table->integer('total_correct_answers')->default(0);
            $table->decimal('average_accuracy', 5, 2)->default(0);
            $table->integer('total_points')->default(0);
            $table->timestamps();

            $table->unique('user_id');
        });

        // Leaderboard Snapshots
        Schema::create('leaderboard_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('score_metric', 10, 2)->default(0);
            $table->integer('rank')->nullable();
            $table->enum('period', ['daily', 'weekly', 'all_time']);
            $table->date('date');
            $table->timestamps();

            $table->index(['exam_id', 'period', 'date']);
            $table->index(['user_id', 'period', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_snapshots');
        Schema::dropIfExists('user_stats');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('user_streaks');
    }
};
