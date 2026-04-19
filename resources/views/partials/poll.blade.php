@php
    use Contensio\Polls\Models\Poll;

    if (! isset($pollId)) { return; }

    try {
        $poll = Poll::with('options')->find((int) $pollId);
    } catch (\Throwable) {
        return;
    }

    if (! $poll || $poll->status === 'draft') { return; }

    $hasVoted    = $poll->hasVoted(request());
    $canVote     = $poll->isActive() && ! $hasVoted && ($poll->allow_guests || auth()->check());
    $showResults = $poll->canSeeResults(request()) || $hasVoted;
    $isClosed    = $poll->isClosed();
    $initResults = $showResults ? json_encode($poll->buildResults()) : 'null';
@endphp

<div class="polls-widget"
     x-data="pollWidget(
         {{ $poll->id }},
         {{ $hasVoted ? 'true' : 'false' }},
         {{ $showResults ? 'true' : 'false' }},
         {{ $canVote ? 'true' : 'false' }},
         {{ $initResults }}
     )">

    {{-- Question --}}
    <p class="font-semibold text-lg text-ink-900 leading-snug mb-4">{{ $poll->question }}</p>

    {{-- Voting form --}}
    <div x-show="canVote && !voted">
        <div class="space-y-2.5 mb-4">
            @foreach($poll->options as $option)
            <label class="flex items-center gap-3 cursor-pointer group">
                <input type="radio" name="poll_{{ $poll->id }}_option"
                       value="{{ $option->id }}"
                       x-model="selected"
                       class="w-4 h-4 text-ember-500 border-gray-300 focus:ring-ember-400 cursor-pointer">
                <span class="text-base text-ink-700 group-hover:text-ink-900 transition-colors">{{ $option->label }}</span>
            </label>
            @endforeach
        </div>

        <button @click="castVote"
                :disabled="!selected || loading"
                class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition-colors">
            <span x-show="!loading">Vote</span>
            <span x-show="loading" x-cloak>Submitting…</span>
        </button>

        <p x-show="error" x-cloak x-text="error" class="mt-2 text-sm text-red-600"></p>
    </div>

    {{-- Results bar chart --}}
    <div x-show="showResults">
        <div class="space-y-3 mb-3">
            <template x-for="opt in (results ? results.options : [])" :key="opt.id">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-ink-800" x-text="opt.label"></span>
                        <span class="text-sm text-ink-500 shrink-0 ml-3">
                            <strong class="text-ink-900" x-text="opt.pct + '%'"></strong>
                            &nbsp;<span x-text="'(' + opt.votes + ')'"></span>
                        </span>
                    </div>
                    <div class="h-2.5 bg-cream-200 rounded-full overflow-hidden">
                        <div class="h-full bg-ember-500 rounded-full transition-all duration-700"
                             :style="'width:' + opt.pct + '%'"></div>
                    </div>
                </div>
            </template>
        </div>

        <p class="text-xs text-ink-400">
            <span x-text="(results ? results.total : 0)"></span> vote<span x-show="!results || results.total !== 1">s</span>
            @if($isClosed) · <span class="font-medium text-ink-500">Poll closed</span> @endif
        </p>
    </div>

    {{-- Not eligible to vote --}}
    @if(! $poll->allow_guests && ! auth()->check() && ! $hasVoted)
    <p class="text-sm text-ink-500">
        <a href="{{ route('login') }}" class="text-ember-600 underline underline-offset-2">Log in</a> to vote.
    </p>
    @endif

</div>

<script>
function pollWidget(pollId, initialVoted, initialShowResults, canVote, initResults) {
    return {
        pollId,
        voted:       initialVoted,
        showResults: initialShowResults,
        canVote,
        selected:    null,
        loading:     false,
        error:       null,
        results:     initResults,

        async castVote() {
            if (! this.selected || this.loading) return;
            this.loading = true;
            this.error   = null;

            try {
                const resp = await fetch('/polls/' + this.pollId + '/vote', {
                    method:  'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ option_id: this.selected }),
                });

                const data = await resp.json();

                if (data.success) {
                    this.voted       = true;
                    this.showResults = true;
                    this.results     = data.results;
                } else {
                    this.error = data.error ?? 'Something went wrong. Please try again.';
                    if (data.results) {
                        this.results     = data.results;
                        this.showResults = true;
                    }
                }
            } catch {
                this.error = 'Could not submit your vote. Please check your connection.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
