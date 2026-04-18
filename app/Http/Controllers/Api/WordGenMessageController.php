<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Word;
use App\Models\WordGenMessage;
use App\Models\WordList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WordGenMessageController extends Controller
{
    private function canReadList(Request $request, WordList $list): bool
    {
        return (int) $list->user_id === (int) $request->user()->id || (bool) $list->public;
    }

    /**
     * @return array<int, int>|null null = hibás (nem 1..N folytonos) generációk
     */
    private function expectedGenerationSequence(WordList $list): ?array
    {
        $gens = Word::query()
            ->where('list_id', $list->id)
            ->distinct()
            ->orderBy('generation')
            ->pluck('generation')
            ->map(fn ($g) => (int) $g)
            ->values();

        if ($gens->isEmpty()) {
            return [];
        }

        foreach ($gens as $idx => $g) {
            if ($g !== $idx + 1) {
                return null;
            }
        }

        return $gens->all();
    }

    public function index(Request $request, WordList $list)
    {
        if (! $this->canReadList($request, $list)) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $expected = $this->expectedGenerationSequence($list);
        if ($expected === null) {
            return response()->json([
                'error' => 'A lista szavainál a generációk nem folytonosak (1..N)',
            ], 422);
        }

        if ($expected === []) {
            return response()->json([
                'list_id' => $list->id,
                'generations' => [],
            ]);
        }

        $rows = WordGenMessage::query()
            ->where('list_id', $list->id)
            ->whereIn('generation', $expected)
            ->orderBy('generation')
            ->get()
            ->keyBy('generation');

        $generations = [];
        foreach ($expected as $gen) {
            $row = $rows->get($gen);
            $generations[] = [
                'generation' => $gen,
                'correct_answer_message' => $row?->correct_answer_message,
                'incorrect_answer_message' => $row?->incorrect_answer_message,
            ];
        }

        return response()->json([
            'list_id' => $list->id,
            'generations' => $generations,
        ]);
    }

    public function replace(Request $request, WordList $list)
    {
        if ($list->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Nincs jogosultság'], 403);
        }

        $validated = $request->validate([
            'generations' => ['required', 'array'],
            'generations.*.generation' => ['required', 'integer', 'min:1'],
            'generations.*.correct_answer_message' => ['nullable', 'string'],
            'generations.*.incorrect_answer_message' => ['nullable', 'string'],
        ]);

        $expected = $this->expectedGenerationSequence($list);
        if ($expected === null) {
            return response()->json([
                'error' => 'A lista szavainál a generációk nem folytonosak (1..N)',
            ], 422);
        }

        if ($expected === []) {
            if (count($validated['generations']) > 0) {
                return response()->json([
                    'error' => 'Üres listánál a generations tömb is üres kell legyen',
                ], 422);
            }

            WordGenMessage::query()->where('list_id', $list->id)->delete();

            return response()->json([
                'list_id' => $list->id,
                'generations' => [],
            ]);
        }

        $items = collect($validated['generations'])->sortBy('generation')->values();

        if ($items->count() !== count($expected)) {
            return response()->json([
                'error' => 'A generációs üzenetek száma nem egyezik a lista generációinak számával',
            ], 422);
        }

        foreach ($items as $idx => $item) {
            if ((int) $item['generation'] !== $expected[$idx]) {
                return response()->json([
                    'error' => 'A generációk csak 1-től N-ig adhatók meg a szavak szerint',
                ], 422);
            }
        }

        DB::transaction(function () use ($list, $items): void {
            WordGenMessage::query()->where('list_id', $list->id)->delete();

            foreach ($items as $item) {
                WordGenMessage::create([
                    'list_id' => $list->id,
                    'generation' => (int) $item['generation'],
                    'correct_answer_message' => $item['correct_answer_message'] ?? null,
                    'incorrect_answer_message' => $item['incorrect_answer_message'] ?? null,
                ]);
            }
        });

        return $this->index($request, $list);
    }
}
