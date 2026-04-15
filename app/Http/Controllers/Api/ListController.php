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

        $lists = WordList::query()
            ->with('user:id,name,username,email')
            ->where(function ($q) use ($uid) {
                $q->where('user_id', $uid)
                    ->orWhere('public', true);
            })
            ->orderBy('id', 'desc')
            ->get();

        return $lists->map(function (WordList $list) use ($uid) {
            $arr = $list->toArray();
            if ((int) $list->user_id !== $uid && (bool) $list->public) {
                $owner = $list->user;
                $arr['owner_username'] = (string) ($owner?->username ?? '');
                $arr['owner_name'] = (string) ($owner?->name ?? '');
                $arr['owner_email'] = (string) ($owner?->email ?? '');
            }

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
        ]);

        $list = WordList::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'public' => (bool) ($validated['public'] ?? false),
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
        ]);

        $list->update([
            'name' => $validated['name'],
            'public' => (bool) ($validated['public'] ?? $list->public),
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

