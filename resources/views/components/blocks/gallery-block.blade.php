{{-- Gallery Block - Image gallery with grid/carousel/masonry layouts --}}
<section class="py-12 px-4">
    <div class="max-w-6xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-8 text-center text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        @php
            $layout = $block->settings['layout'] ?? 'grid';
            $columns = $block->settings['columns'] ?? 3;
            $images = $block->content['images'] ?? [];

            $gridClasses = match($layout) {
                'carousel' => 'overflow-x-auto flex gap-4 pb-4',
                'masonry' => 'columns-1 md:columns-2 lg:columns-3 gap-4',
                default => 'grid grid-cols-1 md:grid-cols-' . min($columns, 4) . ' gap-6',
            };
        @endphp

        <div class="{{ $gridClasses }}">
            @forelse($images as $image)
                @php
                    $imageUrl = is_array($image) ? ($image['image'] ?? '') : $image;
                    $altText = is_array($image) ? ($image['alt_text'] ?? 'Gallery image') : 'Gallery image';
                    $linkUrl = is_array($image) ? ($image['link_url'] ?? null) : null;
                @endphp

                <div class="group relative overflow-hidden rounded-lg {{ $layout === 'carousel' ? 'flex-shrink-0 w-80' : '' }}">
                    @if($linkUrl)
                        <a href="{{ $linkUrl }}" class="block overflow-hidden rounded-lg">
                    @endif

                    <img src="{{ Storage::url($imageUrl) }}"
                         alt="{{ $altText }}"
                         class="w-full h-64 object-cover group-hover:scale-105 transition duration-300">

                    @if($linkUrl)
                        </a>
                    @endif
                </div>
            @empty
                <p class="text-gray-500 col-span-full text-center py-8">No images in gallery</p>
            @endforelse
        </div>
    </div>
</section>
