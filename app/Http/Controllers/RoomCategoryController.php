<?php

namespace App\Http\Controllers;

use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomCategoryController extends Controller
{
    public function index(): View
    {
        return view('room_categories.index', [
            'categories' => RoomCategory::all(),
        ]);
    }

    public function create(): View
    {
        return view('room_categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        RoomCategory::create($this->validated($request));

        return redirect()->route('room-categories.index');
    }

    public function edit(RoomCategory $roomCategory): View
    {
        return view('room_categories.edit', ['category' => $roomCategory]);
    }

    public function update(Request $request, RoomCategory $roomCategory): RedirectResponse
    {
        $roomCategory->update($this->validated($request));

        return redirect()->route('room-categories.index');
    }

    public function destroy(RoomCategory $roomCategory): RedirectResponse
    {
        $roomCategory->delete();

        return redirect()->route('room-categories.index');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
        ]);
    }
}
