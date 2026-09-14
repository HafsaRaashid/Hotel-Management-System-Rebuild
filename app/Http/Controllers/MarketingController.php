<?php

namespace App\Http\Controllers;

use App\Models\RoomCategory;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', [
            'categories' => RoomCategory::all(),
        ]);
    }

    public function room(): View
    {
        return view('marketing.room', [
            'categories' => RoomCategory::all(),
        ]);
    }

    public function services(): View
    {
        return view('marketing.services');
    }

    public function food(): View
    {
        return view('marketing.food');
    }
}
