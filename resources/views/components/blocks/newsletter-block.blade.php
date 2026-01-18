{{-- Newsletter Block - Email signup form --}}
<section class="py-12 px-4 bg-indigo-600 text-white">
    <div class="max-w-2xl mx-auto text-center">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-4">{{ $block->content['title'] }}</h2>
        @endif

        @if($block->content['description'])
            <p class="text-lg mb-6">{{ $block->content['description'] }}</p>
        @endif

        <form action="#" method="POST" class="flex gap-2">
            @csrf
            <input type="email"
                   name="email"
                   placeholder="{{ $block->content['placeholder'] ?? 'Enter your email' }}"
                   class="flex-1 px-4 py-2 rounded text-gray-900"
                   required>
            <button type="submit" class="px-6 py-2 bg-gray-900 text-white font-semibold rounded hover:bg-gray-800 transition">
                {{ $block->content['button_text'] ?? 'Subscribe' }}
            </button>
        </form>

        @if($block->content['privacy_notice'])
            <p class="text-sm text-indigo-100 mt-4">
                {{ $block->content['privacy_text'] ?? 'We respect your privacy' }}
            </p>
        @endif
    </div>
</section>
