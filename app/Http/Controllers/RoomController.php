<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = Room::with('category')
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->input('category_id')))
            ->get();

        return view('rooms.index', [
            'rooms' => $rooms,
            'categories' => RoomCategory::all(),
            'selectedCategoryId' => $request->input('category_id'),
        ]);
    }

    public function create(): View
    {
        return view('rooms.create', [
            'categories' => RoomCategory::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Room::create($this->validated($request));

        return redirect()->route('rooms.index');
    }

    public function edit(Room $room): View
    {
        return view('rooms.edit', [
            'room' => $room,
            'categories' => RoomCategory::all(),
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room->update($this->validated($request));

        return redirect()->route('rooms.index');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'room' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:room_categories,id'],
            'status' => ['required', 'in:0,1'],
        ]);
    }
}
