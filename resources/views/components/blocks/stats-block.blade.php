{{-- Stats Block - Statistics/Metrics display --}}
<section class="py-12 px-4 bg-gray-900 text-white">
    <div class="max-w-6xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-8 text-center">
                {{ $block->content['title'] }}
            </h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @forelse($block->content['stats'] ?? [] as $stat)
                <div class="text-center">
                    <div class="text-4xl font-bold mb-2" style="color: {{ $stat['color'] ?? '#fff' }};">
                        {{ $stat['number'] ?? 0 }}<span class="text-2xl">{{ $stat['suffix'] ?? '' }}</span>
                    </div>
                    <p class="text-gray-300">{{ $stat['label'] ?? '' }}</p>
                </div>
            @empty
                <p class="text-gray-400 col-span-full text-center py-8">No stats to display</p>
            @endforelse
        </div>
    </div>
</section>
