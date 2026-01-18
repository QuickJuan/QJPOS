{{-- Text Block - Simple rich text display --}}
<section class="py-8 px-4">
    <div class="max-w-4xl mx-auto">
        @if(isset($block->content['title']) && $block->content['title'])
            <h2 class="text-3xl font-bold mb-6 text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        <div class="prose prose-lg max-w-none text-gray-700">
            {!! $block->content['text'] ?? '' !!}
        </div>
    </div>
</section>
