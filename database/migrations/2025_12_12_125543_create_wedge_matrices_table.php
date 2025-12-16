<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedge_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->tinyInteger('number_of_rows')->default(4);
            $table->tinyInteger('number_of_columns')->default(4);
            $table->enum(
                'selected_row_display_option',
                ['Carry', 'Total', 'Both']
            )->default('Both');
            $table->json('column_headers')->nullable();
            $table->json('yardage_values')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedge_matrices');
    }
};
