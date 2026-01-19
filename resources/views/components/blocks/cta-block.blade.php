{{-- CTA Block - Call-to-Action section --}}
<section class="py-12 px-4" style="background-color: {{ $block->settings['background_color'] ?? '#f3f4f6' }};">
    <div class="max-w-4xl mx-auto text-center">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-4" style="color: {{ $block->settings['text_color'] ?? '#000' }};">
                {{ $block->content['title'] }}
            </h2>
        @endif

        @if($block->content['description'])
            <p class="text-lg mb-8 max-w-2xl mx-auto" style="color: {{ $block->settings['text_color'] ?? '#000' }};">
                {!! $block->content['description'] !!}
            </p>
        @endif

        @if($block->content['button_text'])
            @php
                $btnStyle = $block->settings['button_style'] ?? 'primary';
                $btnClasses = match($btnStyle) {
                    'secondary' => 'bg-gray-600 text-white hover:bg-gray-700',
                    'outline' => 'border-2 border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white',
                    default => 'bg-indigo-600 text-white hover:bg-indigo-700',
                };
            @endphp
            <a href="{{ $block->content['button_url'] ?? '#' }}"
               class="inline-block px-8 py-3 rounded-lg font-semibold {{ $btnClasses }} transition">
                {{ $block->content['button_text'] }}
            </a>
        @endif
    </div>
</section>
