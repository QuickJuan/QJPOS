{{-- Reviews Block - Customer reviews/testimonials --}}
<section class="py-12 px-4">
    <div class="max-w-4xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-8 text-center text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        <div class="space-y-6">
            @forelse($block->content['reviews'] ?? [] as $review)
                <div class="bg-white rounded-lg shadow p-6">
                    {{-- Rating Stars --}}
                    @if($review['rating'] ?? null)
                        <div class="flex items-center mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">
                                    ★
                                </span>
                            @endfor
                        </div>
                    @endif

                    {{-- Review Text --}}
                    <p class="text-gray-700 mb-4">{{ $review['text'] ?? '' }}</p>

                    {{-- Author Info --}}
                    <div class="flex items-center gap-3">
                        @if($review['author_image'] ?? null)
                            <img src="{{ Storage::url($review['author_image']) }}"
                                 alt="{{ $review['author'] ?? 'Author' }}"
                                 class="w-10 h-10 rounded-full object-cover">
                        @endif

                        <div>
                            <p class="font-semibold text-gray-900">{{ $review['author'] ?? 'Anonymous' }}</p>
                            @if($review['author_position'] ?? null)
                                <p class="text-sm text-gray-600">{{ $review['author_position'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">No reviews yet</p>
            @endforelse
        </div>
    </div>
</section>
