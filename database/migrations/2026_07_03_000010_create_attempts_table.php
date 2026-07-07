<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->integer('total_marks')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->enum('status', ['in_progress', 'completed', 'expired'])->default('in_progress');
            $table->timestamps();

            $table->index(['user_id', 'mock_test_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
