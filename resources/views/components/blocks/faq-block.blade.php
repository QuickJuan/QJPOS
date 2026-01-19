{{-- FAQ Block - Frequently Asked Questions --}}
<section class="py-12 px-4">
    <div class="max-w-3xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-8 text-center text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        <div class="space-y-4">
            @forelse($block->content['faqs'] ?? [] as $faq)
                <details class="bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:bg-gray-50">
                    <summary class="font-semibold text-gray-900 flex justify-between items-center">
                        {{ $faq['question'] ?? '' }}
                        <span class="text-indigo-600">▼</span>
                    </summary>

                    <div class="mt-4 text-gray-700 prose prose-sm">
                        {!! $faq['answer'] ?? '' !!}
                    </div>
                </details>
            @empty
                <p class="text-gray-500 text-center py-8">No FAQs to display</p>
            @endforelse
        </div>
    </div>
</section>
