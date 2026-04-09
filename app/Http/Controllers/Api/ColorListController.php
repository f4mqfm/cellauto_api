<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ColorList;
use Illuminate\Http\Request;

class ColorListController extends Controller
{
    public function index(Request $request)
    {
        return ColorList::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function show(Request $request, ColorList $color_list)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $color_list->load('colors');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $list = ColorList::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
        ]);

        return response()->json($list, 201);
    }

    public function update(Request $request, ColorList $color_list)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $color_list->update([
            'name' => $validated['name'],
        ]);

        return response()->json($color_list);
    }

    public function destroy(Request $request, ColorList $color_list)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $color_list->colors()->delete();
        $color_list->delete();

        return response()->json(['ok' => true]);
    }
}
