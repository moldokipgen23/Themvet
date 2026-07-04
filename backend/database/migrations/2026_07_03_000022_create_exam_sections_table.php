<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_pattern_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('total_questions');
            $table->integer('total_marks');
            $table->integer('duration_minutes')->nullable();
            $table->decimal('marks_per_question', 4, 2)->default(1);
            $table->decimal('negative_marks_per_question', 4, 2)->default(0);
            $table->string('difficulty_range')->nullable()->comment('e.g. easy-hard, medium-hard');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['exam_pattern_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sections');
    }
};
