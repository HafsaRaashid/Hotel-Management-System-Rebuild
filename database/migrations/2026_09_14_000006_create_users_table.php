<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('type')->default(2);
            $table->timestamps();
        });

        // Development-only placeholder credentials - see
        // .specclaw/changes/007-admin-authentication/spec.md's Notes. Not a
        // production security decision; there is no UI yet to create a user
        // (MOD-002 unbuilt) and no legacy seed data to replicate for parity.
        DB::table('users')->insert([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('password123'),
            'type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
