<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BL-004/BL-008 (change 006-delete-referential-integrity): CQ-024 only
 * blocks deleting a room/customer referenced by an ACTIVE booking (status
 * 0/1) - a historical (checked-out/cancelled) booking must not prevent it.
 * The original bookings migration (change 005) left both FKs at their
 * default RESTRICT, which blocks a delete for ANY reference regardless of
 * status - stricter than CQ-024 actually requires. This relaxes both FKs
 * to SET NULL, matching the application-level check in
 * RoomController::destroy()/CustomerController::destroy() exactly: a
 * historical booking loses its room/customer link but the booking record
 * itself survives (CQ-014). customer_id becoming nullable is safe -
 * StayController::checkout() is the only place a booking's customer
 * relation is dereferenced, and it only runs on an active (status=1)
 * booking, which by the same application check can never have had its
 * customer deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable(false)->change();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
};
