<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('total_questions');
            $table->integer('sections_count')->default(0);
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_marking_value', 4, 2)->default(0);
            $table->boolean('is_official')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_patterns');
    }
};
