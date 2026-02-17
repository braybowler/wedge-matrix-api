<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wedge_matrices', function (Blueprint $table) {
            $table->json('club_labels')->nullable()->after('column_headers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedge_matrices', function (Blueprint $table) {
            $table->dropColumn('club_labels');
        });
    }
};
