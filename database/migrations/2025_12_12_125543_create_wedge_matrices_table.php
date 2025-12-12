<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wedge_matrices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->string('label');
            $table->tinyInteger('number_of_rows');
            $table->tinyInteger('number_of_columns');
            $table->json('column_headers');
            $table->json('values');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedge_matrices');
    }
};
