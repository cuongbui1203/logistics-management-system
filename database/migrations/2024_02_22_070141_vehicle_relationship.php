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
        Schema::table(
            'vehicles', function (Blueprint $table) {
                $table->foreign('type_id')
                    ->references('id')
                    ->on('types');
                $table->foreign('driver_id')
                    ->references('id')
                    ->on('users');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table(
            'vehicles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('type_id');
                $table->dropConstrainedForeignId('driver_id');
            }
        );
    }
};
