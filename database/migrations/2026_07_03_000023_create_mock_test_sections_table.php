<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_test_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mock_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('total_questions');
            $table->integer('total_marks');
            $table->integer('duration_minutes')->nullable();
            $table->decimal('marks_per_question', 4, 2)->default(1);
            $table->decimal('negative_marks_per_question', 4, 2)->default(0);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['mock_test_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_test_sections');
    }
};
