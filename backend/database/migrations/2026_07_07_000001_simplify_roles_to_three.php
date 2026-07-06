<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove extra roles (moderator, reviewer, lead_reviewer)
        DB::table('roles')->whereIn('name', ['moderator', 'reviewer', 'lead_reviewer'])->delete();

        // Remove group column
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('group')->default('student')->index()->after('description');
        });

        DB::table('roles')->insert([
            ['name' => 'moderator', 'group' => 'system', 'description' => 'Content moderation, user reports, light admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'reviewer', 'group' => 'teacher', 'description' => 'Review, approve or reject submitted questions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'lead_reviewer', 'group' => 'teacher', 'description' => 'Senior reviewer, create official tests, override decisions', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
};
