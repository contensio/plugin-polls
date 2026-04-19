@extends('contensio::admin.layout')

@section('title', 'Polls')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Polls</h1>
            <p class="mt-1 text-gray-500">Create and manage polls on your site.</p>
        </div>
        <a href="{{ route('polls.create') }}"
           class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2.5 rounded-lg transition-colors">
            <i class="bi bi-plus-lg"></i>
            New poll
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($polls->isEmpty())
    <div class="bg-white border border-gray-200 rounded-xl py-16 text-center text-gray-400">
        <i class="bi bi-bar-chart-steps text-4xl mb-3 block"></i>
        <p class="text-lg font-medium text-gray-500">No polls yet</p>
        <p class="text-sm mt-1 mb-5">Create your first poll and embed it anywhere on your site.</p>
        <a href="{{ route('polls.create') }}"
           class="inline-flex items-center gap-2 bg-ember-500 hover:bg-ember-600 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">
            <i class="bi bi-plus-lg"></i> Create a poll
        </a>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Question</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Votes</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Results visible</th>
                    <th class="text-left px-5 py-3 text-xs font-bold uppercase tracking-wider text-gray-500">Ends</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($polls as $poll)
                @php
                    $effectivelyClosed = $poll->isClosed();
                    $statusLabel = $effectivelyClosed && $poll->status === 'active' ? 'Expired' : ucfirst($poll->status);
                    $badgeClass = match(true) {
                        $poll->status === 'active' && ! $effectivelyClosed => 'bg-green-50 text-green-700 border border-green-200',
                        $poll->status === 'draft'                          => 'bg-gray-100 text-gray-600 border border-gray-200',
                        default                                             => 'bg-red-50 text-red-700 border border-red-200',
                    };
                    $showLabel = match($poll->show_results) {
                        'always'      => 'Always',
                        'after_vote'  => 'After voting',
                        'after_close' => 'After closing',
                        default       => '—',
                    };
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900 max-w-xs">
                        <span class="line-clamp-2">{{ $poll->question }}</span>
                        <p class="text-xs text-gray-400 mt-0.5">ID: {{ $poll->id }} · Embed: <code class="font-mono">@{{include('polls::partials.poll', ['pollId' => {{ $poll->id }}])}}</code></p>
                    </td>
                    <td class="px-5 py-3.5">
                        <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full {{ $badgeClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-700 font-semibold">{{ number_format($poll->votes_count) }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $showLabel }}</td>
                    <td class="px-5 py-3.5 text-gray-500 whitespace-nowrap">
                        {{ $poll->ends_at ? $poll->ends_at->format('M j, Y H:i') : '—' }}
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('polls.results', $poll->id) }}"
                               class="text-gray-400 hover:text-ember-600 transition-colors" title="Results">
                                <i class="bi bi-bar-chart text-base"></i>
                            </a>
                            <a href="{{ route('polls.edit', $poll->id) }}"
                               class="text-gray-400 hover:text-ember-600 transition-colors" title="Edit">
                                <i class="bi bi-pencil text-base"></i>
                            </a>
                            <form method="POST" action="{{ route('polls.destroy', $poll->id) }}"
                                  onsubmit="return confirm('Delete this poll and all its votes?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="bi bi-trash text-base"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($polls->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $polls->links() }}</div>
        @endif
    </div>
    @endif

</div>
@endsection
