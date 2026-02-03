@if ($paginator->hasPages())
    <nav class="pagination flex items-center justify-center gap-x-3" aria-label="Pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="min-h-[38px] min-w-[38px] py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-400 bg-white border border-gray-200 cursor-not-allowed focus:outline-none disabled:opacity-50 disabled:pointer-events-none" aria-label="Previous" disabled>
                <svg aria-hidden="true" class="hidden shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <span>Previous</span>
            </button>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="min-h-[38px] min-w-[38px] py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 bg-white border border-gray-200 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none" aria-label="Previous">
                <svg aria-hidden="true" class="hidden shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <span>Previous</span>
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex items-center gap-x-2">
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <button type="button" class="min-h-[38px] min-w-[38px] flex justify-center items-center text-gray-400 py-2 px-3 text-sm rounded-lg focus:outline-none disabled:opacity-50 disabled:pointer-events-none" disabled>{{ $element }}</button>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="min-h-[38px] min-w-[38px] flex justify-center items-center bg-gray-100 text-gray-800 border border-gray-200 py-2 px-3 text-sm rounded-lg focus:outline-none focus:bg-gray-200 disabled:opacity-50 disabled:pointer-events-none" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}" class="min-h-[38px] min-w-[38px] flex justify-center items-center text-gray-600 hover:bg-gray-100 border border-transparent hover:border-gray-200 py-2 px-3 text-sm rounded-lg focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="min-h-[38px] min-w-[38px] py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-800 bg-white border border-gray-200 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none" aria-label="Next">
                <span>Next</span>
                <svg aria-hidden="true" class="hidden shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        @else
            <button type="button" class="min-h-[38px] min-w-[38px] py-2 px-2.5 inline-flex justify-center items-center gap-x-2 text-sm rounded-lg text-gray-400 bg-white border border-gray-200 cursor-not-allowed focus:outline-none disabled:opacity-50 disabled:pointer-events-none" disabled>
                <span>Next</span>
                <svg aria-hidden="true" class="hidden shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        @endif
    </nav>
@endif
