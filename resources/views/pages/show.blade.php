@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-white">
    {{-- Page Header --}}
    @if($page->seo_title || $page->title)
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-8 px-4">
            <div class="max-w-6xl mx-auto">
                <h1 class="text-4xl font-bold">
                    {{ $page->seo_title ?? $page->title }}
                </h1>
                @if($page->description)
                    <p class="mt-2 text-lg text-indigo-100">
                        {{ $page->description }}
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Featured Image --}}
    @if($page->featured_image)
        <div class="relative h-96 bg-gray-200 overflow-hidden">
            <img src="{{ Storage::url($page->featured_image) }}"
                 alt="{{ $page->title }}"
                 class="w-full h-full object-cover">
        </div>
    @endif

    {{-- Page Blocks --}}
    <div class="py-8">
        @forelse($page->blocks()->orderBy('order')->get() as $block)
            @php
                $componentName = $block->blockType->slug;
                $view = "components.blocks.{$componentName}-block";
            @endphp

            @if(view()->exists($view))
                @include($view)
            @else
                <div class="max-w-6xl mx-auto px-4 py-4 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-yellow-800">
                        ⚠️ Block component not found: <strong>{{ $view }}</strong>
                    </p>
                </div>
            @endif
        @empty
            <div class="max-w-6xl mx-auto px-4 py-12 text-center text-gray-500">
                <p>This page has no content blocks yet.</p>
            </div>
        @endforelse
    </div>

    {{-- SEO Meta Tags --}}
    @if($page->seo)
        <meta name="description" content="{{ $page->seo->meta_description }}">
        <meta name="keywords" content="{{ $page->seo->focus_keywords }}">
    @endif
</div>
@endsection
