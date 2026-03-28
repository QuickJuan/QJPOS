<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Sitemap URL Banner --}}
        <div class="rounded-lg bg-primary-50 dark:bg-primary-400/10 p-4 border border-primary-200 dark:border-primary-400/20">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-primary-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-primary-800 dark:text-primary-300">Sitemap URL</p>
                    <p class="text-sm text-primary-700 dark:text-primary-400 mt-1 font-mono break-all">
                        {{ $this->getSitemapUrl() }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a
                        href="{{ $this->getSitemapUrl() }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-md bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                        Open
                    </a>
                </div>
            </div>
        </div>

        {{-- Pages Table --}}
        @php $pages = $this->getPages(); @endphp

        <div class="fi-ta rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            {{-- Table header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    URLs included in the sitemap
                </h3>
                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                    {{ $pages->count() }} {{ Str::plural('URL', $pages->count()) }}
                </span>
            </div>

            @if ($pages->isEmpty())
                <div class="px-6 py-12 text-center">
                    <x-heroicon-o-document class="mx-auto h-10 w-10 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No published pages found.</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Publish pages from the Pages section to include them in the sitemap.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm divide-y divide-gray-200 dark:divide-white/10">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Page
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    URL
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Freq
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Priority
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Modified
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                            @foreach ($pages as $page)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap max-w-xs truncate">
                                        {{ $page['title'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-sm">
                                        <a
                                            href="{{ $page['url'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="hover:text-primary-600 dark:hover:text-primary-400 font-mono text-xs break-all"
                                        >
                                            {{ $page['url'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-400/10 px-2 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400">
                                            {{ $page['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                        {{ $page['changefreq'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                        {{ $page['priority'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                        {{ $page['lastmod'] ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Search engines info --}}
        <div class="rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50 p-4">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">How to submit your sitemap</h4>
            <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li>
                    <span class="font-medium">Google Search Console:</span>
                    Go to Sitemaps &rarr; Enter <code class="rounded bg-gray-100 dark:bg-gray-700 px-1 text-xs">sitemap.xml</code> &rarr; Submit
                </li>
                <li>
                    <span class="font-medium">Bing Webmaster Tools:</span>
                    Go to Sitemaps &rarr; Submit sitemap &rarr; Enter the full sitemap URL above
                </li>
                <li>
                    <span class="font-medium">robots.txt:</span>
                    Add <code class="rounded bg-gray-100 dark:bg-gray-700 px-1 text-xs">Sitemap: {{ $this->getSitemapUrl() }}</code> to your robots.txt file
                </li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
