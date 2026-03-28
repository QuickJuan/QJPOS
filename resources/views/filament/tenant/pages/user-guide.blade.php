<x-filament-panels::page>
    <div class="space-y-4" x-data="{ openSection: null, openTopic: null }">

        {{-- Search bar --}}
        <div
            x-data="{ query: '' }"
            class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900 py-3"
        >
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    type="search"
                    x-model="query"
                    @input="
                        if (query.trim()) {
                            document.querySelectorAll('[data-section]').forEach(s => {
                                const matches = [...s.querySelectorAll('[data-topic]')].filter(t =>
                                    t.textContent.toLowerCase().includes(query.toLowerCase())
                                );
                                s.style.display = matches.length ? '' : 'none';
                                matches.forEach(t => t.style.display = '');
                                s.querySelectorAll('[data-topic]').forEach(t => {
                                    if (!t.textContent.toLowerCase().includes(query.toLowerCase()))
                                        t.style.display = 'none';
                                });
                            });
                        } else {
                            document.querySelectorAll('[data-section],[data-topic]').forEach(el => el.style.display = '');
                        }
                    "
                    placeholder="Search topics…"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 py-2.5 pl-10 pr-4 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
            </div>
        </div>

        {{-- Sections --}}
        @foreach ($this->getSections() as $sIdx => $section)
            <div
                data-section
                class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
                x-data="{ open: false }"
            >
                {{-- Section header --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="flex w-full items-center gap-3 px-5 py-4 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                >
                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-400/10">
                        <x-dynamic-component :component="$section['icon']" class="h-5 w-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <span class="flex-1 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $section['title'] }}
                    </span>
                    <span class="text-xs text-gray-400 mr-2">{{ count($section['topics']) }} {{ Str::plural('topic', count($section['topics'])) }}</span>
                    <svg
                        class="h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {{-- Topics --}}
                <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-white/5 divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($section['topics'] as $tIdx => $topic)
                        <div
                            data-topic
                            x-data="{ open: false }"
                        >
                            <button
                                type="button"
                                @click="open = !open"
                                class="flex w-full items-start gap-3 px-5 py-3.5 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            >
                                <svg
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-primary-500 transition-transform duration-150"
                                    :class="open ? 'rotate-90' : ''"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $topic['q'] }}
                                </span>
                            </button>

                            <div
                                x-show="open"
                                x-collapse
                                class="px-5 pb-4 pl-12"
                            >
                                <div class="rounded-lg bg-gray-50 dark:bg-white/5 px-4 py-3 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                    {!! $topic['a'] !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 pb-4">
            Can't find what you're looking for? Contact your system administrator.
        </p>
    </div>
</x-filament-panels::page>
