<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use App\Models\WordList;
use App\Models\WordRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WordRelationController extends Controller
{
    public function index(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'from_generation' => ['sometimes', 'integer', 'min:1'],
        ]);

        $query = WordRelation::query()
            ->where('list_id', $list->id)
            ->with([
                'fromWord:id,list_id,generation,word',
                'toWord:id,list_id,generation,word',
            ])
            ->orderBy('id');

        if (isset($validated['from_generation'])) {
            $fromGen = (int) $validated['from_generation'];
            $fromWordIds = Word::query()
                ->where('list_id', $list->id)
                ->where('generation', $fromGen)
                ->pluck('id')
                ->all();
            $query->whereIn('from_word_id', $fromWordIds);
        }

        return $query->get();
    }

    public function store(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'from_word_id' => ['required', 'integer', 'min:1'],
            'to_word_id' => ['required', 'integer', 'min:1'],
        ]);

        $from = Word::query()->where('list_id', $list->id)->find($validated['from_word_id']);
        $to = Word::query()->where('list_id', $list->id)->find($validated['to_word_id']);

        if (! $from || ! $to) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        if ($to->generation !== $from->generation + 1) {
            return response()->json([
                'error' => 'Reláció csak szomszédos generációk között adható meg (GENn -> GENn+1)',
            ], 422);
        }

        $relation = WordRelation::firstOrCreate([
            'list_id' => $list->id,
            'from_word_id' => $from->id,
            'to_word_id' => $to->id,
        ]);

        return response()->json($relation, 201);
    }

    public function destroy(Request $request, WordList $list, WordRelation $relation)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $relation->list_id !== (int) $list->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $relation->delete();

        return response()->json(['ok' => true]);
    }

    public function replaceForFromWord(Request $request, WordList $list, Word $fromWord)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        if ((int) $fromWord->list_id !== (int) $list->id) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        $validated = $request->validate([
            'to_word_ids' => ['required', 'array'],
            'to_word_ids.*' => ['integer', 'min:1', 'distinct'],
        ]);

        $toWords = Word::query()
            ->where('list_id', $list->id)
            ->whereIn('id', $validated['to_word_ids'])
            ->get(['id', 'generation']);

        if ($toWords->count() !== count($validated['to_word_ids'])) {
            return response()->json(['error' => 'Nincs találat'], 404);
        }

        foreach ($toWords as $to) {
            if ((int) $to->generation !== (int) $fromWord->generation + 1) {
                return response()->json([
                    'error' => 'Reláció csak szomszédos generációk között adható meg (GENn -> GENn+1)',
                ], 422);
            }
        }

        DB::transaction(function () use ($list, $fromWord, $validated) {
            WordRelation::query()
                ->where('list_id', $list->id)
                ->where('from_word_id', $fromWord->id)
                ->delete();

            foreach ($validated['to_word_ids'] as $toId) {
                WordRelation::create([
                    'list_id' => $list->id,
                    'from_word_id' => $fromWord->id,
                    'to_word_id' => (int) $toId,
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }
}

