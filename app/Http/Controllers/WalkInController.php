<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\View\View;

class WalkInController extends Controller
{
    /**
     * BL-018. Enumeration 2: rooms.status 0 = available.
     */
    public function available(): View
    {
        return view('walk-in.available', [
            'rooms' => Room::where('status', 0)->with('category')->get(),
        ]);
    }
}
