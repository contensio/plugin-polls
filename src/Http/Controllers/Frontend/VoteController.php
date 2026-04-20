<?php

/**
 * Simple Polls - Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Polls\Http\Controllers\Frontend;

use Contensio\Polls\Models\Poll;
use Contensio\Polls\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VoteController extends Controller
{
    public function vote(Request $request, int $id): JsonResponse
    {
        $poll = Poll::with('options')->find($id);

        if (! $poll) {
            return response()->json(['error' => 'Poll not found.'], 404);
        }

        if (! $poll->isActive()) {
            return response()->json(['error' => 'This poll is closed.'], 422);
        }

        if (! $poll->allow_guests && ! $request->user()) {
            return response()->json(['error' => 'You must be logged in to vote.'], 403);
        }

        if ($poll->hasVoted($request)) {
            return response()->json([
                'error'   => 'You have already voted on this poll.',
                'results' => $poll->canSeeResults($request) ? $poll->buildResults() : null,
            ], 422);
        }

        $request->validate([
            'option_id' => 'required|integer',
        ]);

        $option = $poll->options()->where('id', $request->integer('option_id'))->first();

        if (! $option) {
            return response()->json(['error' => 'Invalid option.'], 422);
        }

        try {
            PollVote::create([
                'poll_id'    => $poll->id,
                'option_id'  => $option->id,
                'user_id'    => $request->user()?->id,
                'ip_address' => $request->ip(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint - already voted (race condition)
            return response()->json(['error' => 'You have already voted.'], 422);
        }

        return response()->json([
            'success' => true,
            'results' => $poll->buildResults(),
        ]);
    }

    public function results(Request $request, int $id): JsonResponse
    {
        $poll = Poll::with('options')->find($id);

        if (! $poll) {
            return response()->json(['error' => 'Poll not found.'], 404);
        }

        if (! $poll->canSeeResults($request)) {
            return response()->json(['error' => 'Results are not visible yet.'], 403);
        }

        return response()->json([
            'voted'   => $poll->hasVoted($request),
            'results' => $poll->buildResults(),
        ]);
    }
}
