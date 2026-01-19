{{-- Banner Block - Hero section with image and CTA --}}
<section class="relative h-96 overflow-hidden" style="background-image: url('{{ Storage::url($block->content['image'] ?? '') }}'); background-size: cover; background-position: center;">
    {{-- Overlay --}}
    @php
        $opacity = ($block->settings['overlay_opacity'] ?? 50) / 100;
        $overlayColor = $block->settings['overlay_color'] ?? '#000000';
    @endphp
    <div class="absolute inset-0" style="background-color: {{ $overlayColor }}; opacity: {{ $opacity }};"></div>

    {{-- Content --}}
    <div class="relative h-full flex items-center justify-center">
        <div class="text-center text-white px-4 max-w-2xl">
            @if($block->content['title'])
                <h1 class="text-5xl font-bold mb-4">{{ $block->content['title'] }}</h1>
            @endif

            @if($block->content['subtitle'])
                <p class="text-xl mb-8">{{ $block->content['subtitle'] }}</p>
            @endif

            @if($block->content['button_text'])
                @php
                    $btnStyle = $block->settings['button_style'] ?? 'primary';
                    $btnClasses = match($btnStyle) {
                        'secondary' => 'bg-white text-gray-900 hover:bg-gray-100',
                        'outline' => 'border-2 border-white text-white hover:bg-white hover:text-gray-900',
                        default => 'bg-indigo-600 text-white hover:bg-indigo-700',
                    };
                @endphp
                <a href="{{ $block->content['button_url'] ?? '#' }}" class="inline-block px-8 py-3 rounded-lg font-semibold {{ $btnClasses }} transition">
                    {{ $block->content['button_text'] }}
                </a>
            @endif
        </div>
    </div>
</section>
