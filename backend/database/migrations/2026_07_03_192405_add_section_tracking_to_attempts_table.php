<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->foreignId('current_section_id')->nullable()->after('mock_test_id')->constrained('mock_test_sections')->nullOnDelete();
            $table->json('section_time_remaining')->nullable()->after('current_section_id');
            $table->json('section_status')->nullable()->after('section_time_remaining');
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn(['current_section_id', 'section_time_remaining', 'section_status']);
        });
    }
};
