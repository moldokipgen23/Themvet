<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->boolean('is_marked_for_review')->default(false)->after('time_spent_on_question');
            $table->foreignId('mock_test_section_id')->nullable()->after('is_marked_for_review')->constrained('mock_test_sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attempt_answers', function (Blueprint $table) {
            $table->dropColumn(['is_marked_for_review', 'mock_test_section_id']);
        });
    }
};
