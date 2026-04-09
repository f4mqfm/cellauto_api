<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WordList;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function index(Request $request)
    {
        return WordList::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function show(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $list->load('words');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $list = WordList::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
        ]);

        return response()->json($list, 201);
    }

    public function update(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $list->update([
            'name' => $validated['name'],
        ]);

        return response()->json($list);
    }

    public function destroy(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $list->words()->delete();
        $list->delete();

        return response()->json(['ok' => true]);
    }
}

