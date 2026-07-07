<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviewer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('level', ['reviewer'])->default('reviewer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'exam_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviewer_assignments');
    }
};
