<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('faktur', function (Blueprint $table) {
            $table->softDeletes(); // Ini nambah kolom deleted_at
        });
    }

    public function down(): void
    {
        Schema::table('faktur', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
};
