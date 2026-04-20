@php
    use Contensio\Polls\Models\Poll;
    try { $pollCount = Poll::count(); } catch (\Throwable) { $pollCount = null; }
@endphp
<a href="{{ route('contensio-polls.index') }}"
   class="block bg-white border border-gray-200 rounded-xl p-5 hover:border-ember-400 hover:shadow-sm transition-all group">
    <div class="flex items-start justify-between gap-3">
        <div class="w-10 h-10 rounded-lg bg-ember-500/10 text-ember-600 flex items-center justify-center text-xl shrink-0">
            <i class="bi bi-bar-chart-steps"></i>
        </div>
        @if($pollCount !== null)
        <span class="text-sm font-semibold text-gray-400">{{ $pollCount }} poll{{ $pollCount !== 1 ? 's' : '' }}</span>
        @endif
    </div>
    <p class="mt-3 font-semibold text-gray-900 group-hover:text-ember-600 transition-colors">Polls</p>
    <p class="mt-0.5 text-sm text-gray-500">Multiple-choice polls with live results.</p>
</a>
