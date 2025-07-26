<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('faktur', function (Blueprint $table) {
            $table->integer('nominal_charge')->default(0)->change();
            $table->integer('charge')->default(0)->change();
            $table->integer('total_final')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('faktur', function (Blueprint $table) {
            $table->integer('nominal_charge')->nullable(false)->change();
            $table->integer('charge')->nullable(false)->change();
            $table->integer('total_final')->nullable(false)->change();
        });
    }
};