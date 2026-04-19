@extends('contensio::admin.layout')

@section('title', $poll ? 'Edit Poll' : 'New Poll')

@section('content')
<div class="p-6 max-w-2xl"
     x-data="{
        options: {{ $poll ? json_encode($poll->options->pluck('label')->values()) : "['', '']" }},
        addOption() { this.options.push(''); this.$nextTick(() => this.$el.querySelectorAll('.opt-input:last-of-type')[0]?.focus()); },
        removeOption(i) { if (this.options.length > 2) this.options.splice(i, 1); }
     }">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('polls.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="bi bi-arrow-left text-lg"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $poll ? 'Edit Poll' : 'New Poll' }}
        </h1>
    </div>

    @if($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $poll ? route('polls.update', $poll->id) : route('polls.store') }}"
          class="space-y-6">
        @csrf
        @if($poll) @method('PUT') @endif

        {{-- Question --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900">Question</h2>
            <div>
                <textarea name="question" rows="3" required maxlength="500"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400 resize-none"
                          placeholder="What do you think about…?">{{ old('question', $poll?->question) }}</textarea>
            </div>
        </div>

        {{-- Options --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Options <span class="text-gray-400 font-normal text-sm">(min 2, max 20)</span></h2>
            </div>

            @if($errors->has('options') || $errors->has('options.*'))
            <p class="text-red-600 text-sm">Please add at least 2 non-empty options.</p>
            @endif

            <div class="space-y-3">
                <template x-for="(opt, i) in options" :key="i">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm w-5 text-right shrink-0" x-text="i + 1 + '.'"></span>
                        <input type="text" :name="'options[' + i + ']'"
                               x-model="options[i]"
                               class="opt-input flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400"
                               maxlength="300" placeholder="Option text…" required>
                        <button type="button" @click="removeOption(i)"
                                :disabled="options.length <= 2"
                                class="text-gray-300 hover:text-red-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                            <i class="bi bi-x-lg text-sm"></i>
                        </button>
                    </div>
                </template>
            </div>

            <button type="button" @click="addOption"
                    x-show="options.length < 20"
                    class="flex items-center gap-2 text-sm text-ember-600 hover:text-ember-700 font-medium transition-colors">
                <i class="bi bi-plus-lg"></i> Add option
            </button>
        </div>

        {{-- Settings --}}
        <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
            <h2 class="text-base font-semibold text-gray-900">Settings</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                        @foreach(['draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $poll?->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="show_results" class="block text-sm font-medium text-gray-700 mb-1">Show results</label>
                    <select id="show_results" name="show_results"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                        @foreach(['always' => 'Always', 'after_vote' => 'After voting', 'after_close' => 'After closing'] as $val => $label)
                        <option value="{{ $val }}" {{ old('show_results', $poll?->show_results ?? 'after_vote') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" id="allow_guests" name="allow_guests" value="1"
                       class="w-4 h-4 rounded border-gray-300 text-ember-500 focus:ring-ember-400"
                       {{ old('allow_guests', $poll?->allow_guests ?? true) ? 'checked' : '' }}>
                <label for="allow_guests" class="text-sm text-gray-700">
                    Allow guests to vote <span class="text-gray-400">(one vote per IP address)</span>
                </label>
            </div>

            <div>
                <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                    Auto-close at <span class="text-gray-400 font-normal">(optional)</span>
                </label>
                <input id="ends_at" name="ends_at" type="datetime-local"
                       value="{{ old('ends_at', $poll?->ends_at ? $poll->ends_at->format('Y-m-d\TH:i') : '') }}"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ember-400">
                <p class="mt-1 text-xs text-gray-400">The poll will automatically stop accepting votes after this time.</p>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('polls.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            <button type="submit"
                    class="bg-ember-500 hover:bg-ember-600 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
                {{ $poll ? 'Save changes' : 'Create poll' }}
            </button>
        </div>

    </form>
</div>
@endsection
