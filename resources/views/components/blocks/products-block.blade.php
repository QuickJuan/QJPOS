{{-- Products Block - Product showcase --}}
<section class="py-12 px-4 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-4 text-center text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        @if($block->content['description'])
            <p class="text-gray-600 text-center mb-8 max-w-2xl mx-auto">
                {!! $block->content['description'] !!}
            </p>
        @endif

        @php
            $layout = $block->settings['layout'] ?? 'grid';
            $columns = $block->settings['columns'] ?? 3;
            $products = $block->content['products'] ?? [];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min($columns, 4) }} gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
                    @if($product['image'] ?? null)
                        <img src="{{ Storage::url($product['image']) }}"
                             alt="{{ $product['name'] ?? 'Product' }}"
                             class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">No image</span>
                        </div>
                    @endif

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-gray-900">{{ $product['name'] ?? 'Product' }}</h3>

                        @if($product['price'] ?? null)
                            <p class="text-indigo-600 font-bold text-xl my-2">${{ number_format($product['price'], 2) }}</p>
                        @endif

                        @if($product['button_text'] ?? null)
                            <a href="{{ $product['button_url'] ?? '#' }}"
                               class="block w-full text-center bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700 transition mt-4">
                                {{ $product['button_text'] }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 col-span-full text-center py-8">No products to display</p>
            @endforelse
        </div>
    </div>
</section>
