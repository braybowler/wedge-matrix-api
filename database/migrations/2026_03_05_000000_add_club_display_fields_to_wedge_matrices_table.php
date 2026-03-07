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
            $table->json('club_lofts')->nullable()->after('club_labels');
            $table->string('club_label_display_mode')->default('title')->after('club_lofts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedge_matrices', function (Blueprint $table) {
            $table->dropColumn(['club_lofts', 'club_label_display_mode']);
        });
    }
};
