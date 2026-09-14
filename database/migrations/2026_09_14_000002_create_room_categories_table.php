<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->timestamps();
        });

        DB::table('room_categories')->insert([
            ['name' => 'Single Room', 'price' => 99, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Double Room', 'price' => 149, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Deluxe Room', 'price' => 199, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('room_categories');
    }
};
