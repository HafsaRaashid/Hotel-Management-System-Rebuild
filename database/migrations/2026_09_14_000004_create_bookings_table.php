<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ref_no')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('name');
            $table->string('mail');
            $table->string('phone');
            $table->foreignId('category_id')->constrained('room_categories');
            $table->foreignId('room_id')->nullable()->constrained('rooms');
            $table->unsignedInteger('adult');
            $table->unsignedInteger('children');
            $table->date('datein');
            $table->date('dateout');
            $table->unsignedInteger('days_of_stay');
            $table->unsignedTinyInteger('status')->default(0);
            $table->text('message')->nullable();
            $table->unsignedInteger('price')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
