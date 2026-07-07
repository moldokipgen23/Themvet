<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->integer('marks')->default(1);
            $table->decimal('negative_marks', 3, 2)->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['mock_test_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_test_questions');
    }
};
