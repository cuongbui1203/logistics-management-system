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
            'orders', function (Blueprint $table) {
                $table->id();
                $table->string('sender_name');
                $table->string('sender_address');
                $table->string('sender_phone');
                $table->string('receiver_name');
                $table->string('receiver_address');
                $table->string('receiver_phone');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
