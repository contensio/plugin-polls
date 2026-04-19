@extends('contensio::admin.layout')

@section('title', 'Poll Results')

@section('content')
<div class="p-6 max-w-2xl">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('polls.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="bi bi-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Results</h1>
            <p class="mt-0.5 text-gray-500 text-sm">{{ $poll->question }}</p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-gray-900">{{ number_format($results['total']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Total votes</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-gray-900">{{ $poll->options->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">Options</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5 text-center">
            @php
                $statusLabel = match(true) {
                    $poll->isClosed()          => 'Closed',
                    $poll->status === 'draft'  => 'Draft',
                    $poll->status === 'active' => 'Active',
                    default                    => ucfirst($poll->status),
                };
                $statusColor = match(true) {
                    $poll->isClosed()          => 'text-red-600',
                    $poll->status === 'active' => 'text-green-600',
                    default                    => 'text-gray-500',
                };
            @endphp
            <p class="text-3xl font-bold {{ $statusColor }}">{{ $statusLabel }}</p>
            <p class="text-sm text-gray-500 mt-1">Status</p>
        </div>
    </div>

    {{-- Bar chart --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
        <h2 class="text-base font-semibold text-gray-900">Breakdown</h2>

        @if($results['total'] === 0)
        <p class="text-gray-400 text-sm">No votes yet.</p>
        @else
        @foreach($results['options'] as $opt)
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-sm font-medium text-gray-800">{{ $opt['label'] }}</span>
                <span class="text-sm text-gray-500 shrink-0 ml-3">
                    {{ number_format($opt['votes']) }} vote{{ $opt['votes'] !== 1 ? 's' : '' }}
                    · <strong class="text-gray-900">{{ $opt['pct'] }}%</strong>
                </span>
            </div>
            <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-ember-500 rounded-full transition-all duration-500"
                     style="width: {{ $opt['pct'] }}%"></div>
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Actions --}}
    <div class="mt-6 flex gap-3">
        <a href="{{ route('polls.edit', $poll->id) }}"
           class="inline-flex items-center gap-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <i class="bi bi-pencil"></i> Edit poll
        </a>
        <form method="POST" action="{{ route('polls.destroy', $poll->id) }}"
              onsubmit="return confirm('Delete this poll and all {{ $results['total'] }} votes?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <i class="bi bi-trash"></i> Delete poll
            </button>
        </form>
    </div>

</div>
@endsection
