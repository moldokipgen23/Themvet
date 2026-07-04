<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mock_tests', function (Blueprint $table) {
            $table->foreignId('exam_pattern_id')->nullable()->after('exam_id')->constrained()->nullOnDelete();
            $table->integer('total_questions')->default(0)->after('total_marks');
            $table->string('difficulty')->default('medium')->after('total_questions');
        });

        Schema::table('mock_test_questions', function (Blueprint $table) {
            $table->foreignId('mock_test_section_id')->nullable()->after('mock_test_id')->constrained('mock_test_sections')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mock_test_questions', function (Blueprint $table) {
            $table->dropForeign(['mock_test_section_id']);
            $table->dropColumn('mock_test_section_id');
        });

        Schema::table('mock_tests', function (Blueprint $table) {
            $table->dropForeign(['exam_pattern_id']);
            $table->dropColumn(['exam_pattern_id', 'total_questions', 'difficulty']);
        });
    }
};
