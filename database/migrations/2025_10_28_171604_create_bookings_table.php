<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('quest_id')->constrained();
            $table->string('date');
            $table->string('time');
            $table->integer('players_count');
            $table->decimal('total_price', 8, 2);
            $table->string('status');
            $table->timestamps();  // добавление временных меток
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
