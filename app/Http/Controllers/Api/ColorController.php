<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\ColorList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request, ColorList $color_list)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $color_list->colors()->orderBy('position')->orderBy('id')->get();
    }

    public function store(Request $request, ColorList $color_list)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'color' => ['required', 'string', 'max:50'],
            'position' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('colors', 'position')->where(fn ($q) => $q->where('list_id', $color_list->id)),
            ],
        ]);

        $color = Color::create([
            'list_id' => $color_list->id,
            'color' => $validated['color'],
            'position' => $validated['position'],
        ]);

        return response()->json($color, 201);
    }

    public function update(Request $request, ColorList $color_list, Color $color)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $color->list_id !== (int) $color_list->id) {
            return response()->json(['error' => 'A szín nem ehhez a listához tartozik'], 404);
        }

        $validated = $request->validate([
            'color' => ['sometimes', 'string', 'max:50'],
            'position' => [
                'sometimes',
                'integer',
                'min:0',
                Rule::unique('colors', 'position')
                    ->where(fn ($q) => $q->where('list_id', $color_list->id))
                    ->ignore($color->id),
            ],
        ]);

        if ($validated === []) {
            return response()->json(['error' => 'Nincs frissítendő mező'], 422);
        }

        $color->fill($validated);
        $color->save();

        return response()->json($color);
    }

    public function destroy(Request $request, ColorList $color_list, Color $color)
    {
        if ($color_list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $color->list_id !== (int) $color_list->id) {
            return response()->json(['error' => 'A szín nem ehhez a listához tartozik'], 404);
        }

        $color->delete();

        return response()->json(['ok' => true]);
    }
}
