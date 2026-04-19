<?php

/**
 * Simple Polls — Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Polls\Http\Controllers\Admin;

use Contensio\Polls\Models\Poll;
use Contensio\Polls\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::withCount('votes')
            ->latest()
            ->paginate(30);

        return view('polls::admin.index', compact('polls'));
    }

    public function create()
    {
        return view('polls::admin.form', ['poll' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $poll = Poll::create([
            'question'     => $data['question'],
            'status'       => $data['status'],
            'show_results' => $data['show_results'],
            'allow_guests' => $data['allow_guests'],
            'ends_at'      => $data['ends_at'],
        ]);

        $this->syncOptions($poll, $data['options']);

        return redirect()->route('polls.index')->with('success', 'Poll created.');
    }

    public function edit(int $id)
    {
        $poll = Poll::with('options')->findOrFail($id);

        return view('polls::admin.form', compact('poll'));
    }

    public function update(Request $request, int $id)
    {
        $poll = Poll::findOrFail($id);
        $data = $this->validated($request);

        $poll->update([
            'question'     => $data['question'],
            'status'       => $data['status'],
            'show_results' => $data['show_results'],
            'allow_guests' => $data['allow_guests'],
            'ends_at'      => $data['ends_at'],
        ]);

        $this->syncOptions($poll, $data['options']);

        return redirect()->route('polls.index')->with('success', 'Poll updated.');
    }

    public function destroy(int $id)
    {
        Poll::findOrFail($id)->delete();

        return back()->with('success', 'Poll deleted.');
    }

    public function results(int $id)
    {
        $poll    = Poll::with('options')->withCount('votes')->findOrFail($id);
        $results = $poll->buildResults();

        return view('polls::admin.results', compact('poll', 'results'));
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $request->validate([
            'question'         => 'required|string|max:500',
            'status'           => 'required|in:draft,active,closed',
            'show_results'     => 'required|in:always,after_vote,after_close',
            'ends_at'          => 'nullable|date|after:now',
            'options'          => 'required|array|min:2|max:20',
            'options.*'        => 'required|string|max:300',
        ]);

        return [
            'question'     => strip_tags(trim($request->input('question'))),
            'status'       => $request->input('status'),
            'show_results' => $request->input('show_results'),
            'allow_guests' => (bool) $request->boolean('allow_guests'),
            'ends_at'      => $request->input('ends_at') ?: null,
            'options'      => array_filter(array_map('strip_tags', array_map('trim', $request->input('options', [])))),
        ];
    }

    private function syncOptions(Poll $poll, array $labels): void
    {
        // Delete all existing options (votes cascade-deleted via FK)
        $poll->options()->delete();

        foreach (array_values($labels) as $i => $label) {
            if ($label === '') {
                continue;
            }
            PollOption::create([
                'poll_id'    => $poll->id,
                'label'      => $label,
                'sort_order' => $i,
            ]);
        }
    }
}
