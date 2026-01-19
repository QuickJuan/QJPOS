{{-- Contact Form Block - Contact form with custom fields --}}
<section class="py-12 px-4">
    <div class="max-w-2xl mx-auto">
        @if($block->content['title'])
            <h2 class="text-3xl font-bold mb-4 text-gray-900">
                {{ $block->content['title'] }}
            </h2>
        @endif

        @if($block->content['description'])
            <p class="text-gray-600 mb-6">
                {!! $block->content['description'] !!}
            </p>
        @endif

        <form action="#" method="POST" class="space-y-4">
            @csrf

            @forelse($block->content['form_fields'] ?? [] as $field)
                <div>
                    <label for="field_{{ $field['name'] ?? $loop->index }}" class="block text-sm font-medium text-gray-900 mb-1">
                        {{ $field['label'] ?? '' }}
                        @if($field['required'] ?? false)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>

                    @php
                        $fieldType = $field['type'] ?? 'text';
                        $fieldName = $field['name'] ?? 'field_' . $loop->index;
                        $placeholder = $field['placeholder'] ?? '';
                        $isRequired = $field['required'] ?? false;
                    @endphp

                    @if($fieldType === 'textarea')
                        <textarea
                            name="{{ $fieldName }}"
                            id="field_{{ $fieldName }}"
                            placeholder="{{ $placeholder }}"
                            rows="4"
                            {{ $isRequired ? 'required' : '' }}
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                        </textarea>
                    @else
                        <input
                            type="{{ $fieldType }}"
                            name="{{ $fieldName }}"
                            id="field_{{ $fieldName }}"
                            placeholder="{{ $placeholder }}"
                            {{ $isRequired ? 'required' : '' }}
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-transparent">
                    @endif
                </div>
            @empty
                <p class="text-gray-500">No form fields configured</p>
            @endforelse

            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-2 rounded-lg hover:bg-indigo-700 transition">
                {{ $block->content['button_text'] ?? 'Send' }}
            </button>
        </form>
    </div>
</section>
