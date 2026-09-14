<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    public const STATUS_BOOKED = 0;

    public const STATUS_CHECKED_IN = 1;

    public const STATUS_CHECKED_OUT = 2;

    public const STATUS_CANCELLED = 3;

    protected $fillable = [
        'ref_no',
        'customer_id',
        'name',
        'mail',
        'phone',
        'category_id',
        'room_id',
        'adult',
        'children',
        'datein',
        'dateout',
        'days_of_stay',
        'status',
        'message',
        'price',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * DR-003: generate a ref_no in [0, 999999999], regenerated until unique.
     */
    public static function generateUniqueRefNo(): int
    {
        do {
            $candidate = random_int(0, 999999999);
        } while (static::where('ref_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * DR-009's exact legacy formula, adopted as-is: the whole-day difference
     * between check-in and check-out dates.
     */
    public static function computeDaysOfStay(string $datein, string $dateout): int
    {
        return (int) floor(abs(strtotime($dateout) - strtotime($datein)) / 86400);
    }
}
