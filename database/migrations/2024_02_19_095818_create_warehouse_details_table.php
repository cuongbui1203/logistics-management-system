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
        Schema::create(
            'warehouse_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wp_id');
                $table->integer('max_payload')->default(0);
                $table->integer('payload')->default(0);
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_details');
    }
};
