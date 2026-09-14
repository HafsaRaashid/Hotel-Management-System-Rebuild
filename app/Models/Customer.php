<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_id',
        'name',
        'mail',
        'phone',
        'address',
        'charges',
    ];

    /**
     * DR-004: generate a customer_id in [0, 99999999], regenerated until unique.
     */
    public static function generateUniqueCustomerId(): int
    {
        do {
            $candidate = random_int(0, 99999999);
        } while (static::where('customer_id', $candidate)->exists());

        return $candidate;
    }
}
