<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use App\Models\WordList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WordController extends Controller
{
    public function index(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $list->words()->orderBy('position')->orderBy('id')->get();
    }

    public function store(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'word' => [
                'required',
                'string',
                'max:255',
                Rule::unique('words', 'word')->where(fn ($q) => $q->where('list_id', $list->id)),
            ],
            'position' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('words', 'position')->where(fn ($q) => $q->where('list_id', $list->id)),
            ],
        ]);

        $word = Word::create([
            'list_id' => $list->id,
            'word' => $validated['word'],
            'position' => $validated['position'],
        ]);

        return response()->json($word, 201);
    }

    public function update(Request $request, WordList $list, Word $word)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $word->list_id !== (int) $list->id) {
            return response()->json(['error' => 'A szó nem ehhez a listához tartozik'], 404);
        }

        $validated = $request->validate([
            'word' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('words', 'word')
                    ->where(fn ($q) => $q->where('list_id', $list->id))
                    ->ignore($word->id),
            ],
            'position' => [
                'sometimes',
                'integer',
                'min:0',
                Rule::unique('words', 'position')
                    ->where(fn ($q) => $q->where('list_id', $list->id))
                    ->ignore($word->id),
            ],
        ]);

        if ($validated === []) {
            return response()->json(['error' => 'Nincs frissítendő mező'], 422);
        }

        $word->fill($validated);
        $word->save();

        return response()->json($word);
    }

    public function destroy(Request $request, WordList $list, Word $word)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $word->list_id !== (int) $list->id) {
            return response()->json(['error' => 'A szó nem ehhez a listához tartozik'], 404);
        }

        $word->delete();

        return response()->json(['ok' => true]);
    }
}

