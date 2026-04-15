<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use App\Models\WordList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WordController extends Controller
{
    private function canReadList(Request $request, WordList $list): bool
    {
        return (int) $list->user_id === (int) $request->user()->id || (bool) $list->public;
    }

    public function index(Request $request, WordList $list)
    {
        if (! $this->canReadList($request, $list)) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $words = $list->words()
            ->orderBy('generation')
            ->orderBy('word')
            ->orderBy('id')
            ->get();

        $generations = $words
            ->groupBy('generation')
            ->map(fn ($items, $generation) => [
                'generation' => (int) $generation,
                'words' => $items->map(fn (Word $item) => [
                    'id' => $item->id,
                    'word' => $item->word,
                ])->values(),
            ])
            ->values();

        return response()->json([
            'list_id' => $list->id,
            'generations' => $generations,
        ]);
    }

    public function store(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'generation' => ['required', 'integer', 'min:1'],
            'word' => ['required_without:words', 'string', 'max:255'],
            'words' => ['required_without:word', 'array', 'min:1'],
            'words.*' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        $wordsToInsert = array_values(array_filter(
            isset($validated['word']) ? [$validated['word']] : $validated['words'],
            fn ($value) => is_string($value) && trim($value) !== ''
        ));

        if ($wordsToInsert === []) {
            return response()->json(['error' => 'Legalább egy szó kötelező'], 422);
        }

        $created = DB::transaction(function () use ($list, $validated, $wordsToInsert) {
            $generation = (int) $validated['generation'];
            $createdWords = [];

            foreach ($wordsToInsert as $wordValue) {
                $wordValue = trim($wordValue);

                $exists = Word::query()
                    ->where('list_id', $list->id)
                    ->where('generation', $generation)
                    ->where('word', $wordValue)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $createdWords[] = Word::create([
                    'list_id' => $list->id,
                    'generation' => $generation,
                    'word' => $wordValue,
                ]);
            }

            return $createdWords;
        });

        return response()->json([
            'generation' => (int) $validated['generation'],
            'created' => $created,
        ], 201);
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
            'generation' => ['sometimes', 'integer', 'min:1'],
            'word' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('words', 'word')
                    ->where(fn ($q) => $q
                        ->where('list_id', $list->id)
                        ->where('generation', (int) ($request->input('generation', $word->generation))))
                    ->ignore($word->id),
            ],
        ]);

        if ($validated === []) {
            return response()->json(['error' => 'Nincs frissítendő mező'], 422);
        }

        if (array_key_exists('generation', $validated) && ! array_key_exists('word', $validated)) {
            $duplicateInTargetGeneration = Word::query()
                ->where('list_id', $list->id)
                ->where('generation', (int) $validated['generation'])
                ->where('word', $word->word)
                ->where('id', '!=', $word->id)
                ->exists();

            if ($duplicateInTargetGeneration) {
                return response()->json([
                    'error' => 'A szó már létezik a cél generációban',
                ], 422);
            }
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

        $generationWordCount = Word::query()
            ->where('list_id', $list->id)
            ->where('generation', $word->generation)
            ->count();

        if ($generationWordCount <= 1) {
            return response()->json([
                'error' => 'Egy generációban legalább egy szónak maradnia kell',
            ], 422);
        }

        $word->delete();

        return response()->json(['ok' => true]);
    }

    public function replaceGenerations(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'generations' => ['required', 'array', 'min:1'],
            'generations.*.generation' => ['required', 'integer', 'min:1'],
            'generations.*.words' => ['required', 'array', 'min:1'],
            'generations.*.words.*' => ['required', 'string', 'max:255', 'distinct'],
        ]);

        $items = collect($validated['generations'])->sortBy('generation')->values();
        $expectedGeneration = 1;

        foreach ($items as $item) {
            if ((int) $item['generation'] !== $expectedGeneration) {
                return response()->json([
                    'error' => 'A generációk csak 1-től N-ig folytonosan adhatók meg',
                ], 422);
            }

            $expectedGeneration++;
        }

        DB::transaction(function () use ($list, $items) {
            $list->words()->delete();

            foreach ($items as $item) {
                foreach ($item['words'] as $value) {
                    Word::create([
                        'list_id' => $list->id,
                        'generation' => (int) $item['generation'],
                        'word' => trim($value),
                    ]);
                }
            }
        });

        return $this->index($request, $list);
    }
}

