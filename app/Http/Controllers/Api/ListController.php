<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WordList;
use Illuminate\Http\Request;

class ListController extends Controller
{
    private function canReadList(Request $request, WordList $list): bool
    {
        return (int) $list->user_id === (int) $request->user()->id || (bool) $list->public;
    }

    public function index(Request $request)
    {
        $uid = (int) $request->user()->id;

        // Admin felületben is: csak a bejelentkezett user saját listái.
        return WordList::query()
            ->where('user_id', $uid)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Publikus listák (más felhasználóktól) – a www kliens használhatja.
     * Az admin UI NEM ezt hívja.
     */
    public function publicIndex(Request $request)
    {
        $uid = (int) $request->user()->id;

        $lists = WordList::query()
            ->with('user:id,name,username,email')
            ->where('public', true)
            ->where('user_id', '!=', $uid)
            ->orderBy('id', 'desc')
            ->get();

        return $lists->map(function (WordList $list) {
            $arr = $list->toArray();
            $owner = $list->user;
            $arr['owner_username'] = (string) ($owner?->username ?? '');
            $arr['owner_name'] = (string) ($owner?->name ?? '');
            $arr['owner_email'] = (string) ($owner?->email ?? '');
            unset($arr['user']);
            return $arr;
        })->values();
    }

    public function show(Request $request, WordList $list)
    {
        if (! $this->canReadList($request, $list)) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        return $list->load('words');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'public' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:65535'],
            'wordlist' => ['nullable', 'string', 'max:16777215'],
        ]);

        $list = WordList::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'public' => (bool) ($validated['public'] ?? false),
            'notes' => $validated['notes'] ?? null,
            'wordlist' => $validated['wordlist'] ?? null,
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
            'public' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:65535'],
            'wordlist' => ['nullable', 'string', 'max:16777215'],
        ]);

        $list->update([
            'name' => $validated['name'],
            'public' => (bool) ($validated['public'] ?? $list->public),
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $list->notes,
            'wordlist' => array_key_exists('wordlist', $validated) ? $validated['wordlist'] : $list->wordlist,
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

