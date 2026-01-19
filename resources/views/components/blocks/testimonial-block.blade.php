{{-- Testimonial Block - Featured single testimonial --}}
<section class="py-12 px-4 bg-indigo-50">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-8">
            {{-- Quote --}}
            <blockquote class="text-2xl font-semibold text-gray-900 mb-6 italic">
                "{{ $block->content['quote'] ?? '' }}"
            </blockquote>

            {{-- Author Info --}}
            <div class="flex items-center gap-4">
                @if($block->content['author_image'] ?? null)
                    <img src="{{ Storage::url($block->content['author_image']) }}"
                         alt="{{ $block->content['author'] ?? 'Author' }}"
                         class="w-16 h-16 rounded-full object-cover">
                @endif

                <div>
                    <p class="font-bold text-lg text-gray-900">{{ $block->content['author'] ?? 'Anonymous' }}</p>
                    @if($block->content['author_title'] ?? null)
                        <p class="text-gray-600">{{ $block->content['author_title'] }}</p>
                    @endif
                    @if($block->content['company'] ?? null)
                        <p class="text-sm text-gray-500">{{ $block->content['company'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
