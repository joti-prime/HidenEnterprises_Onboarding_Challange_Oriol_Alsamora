@extends(Theme::wrapper())

@section('title')
    {!! __('client.services') !!}
@endsection

@section('container')
@php
    $isVisible = function ($category) {
        if (in_array($category->status, ['unlisted', 'inactive', 'admin_unlisted'])) {
            return false;
        }
        if ($category->status === 'restricted') {
            return Auth::check() && Auth::user()->is_admin();
        }
        return true;
    };

    $cards = \App\Models\Categories::query()
        ->whereNull('parent_id')
        ->with(['children' => function ($q) { $q->orderBy('order'); }])
        ->orderBy('order')
        ->get()
        ->filter(function ($node) use ($isVisible) {
            if (!$isVisible($node)) {
                return false;
            }
            if ($node->children->count() > 0) {
                return $node->children->contains($isVisible);
            }
            return true;
        });
@endphp

<section class="antialiased">
  <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
    <div class="mb-4 flex items-center justify-between gap-4 md:mb-8">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">{{ __('admin.categories') }}</h2>
    </div>

    <div class="flex flex-wrap justify-center gap-4 sm:mt-8 xl:gap-8">
      @foreach($cards as $category)
        <a href="{{ route('store.service', ['service' => $category->link]) }}" class="block w-full max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-primary-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-500 sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-1rem)]">
          @if($category->icon)
            <img class="mx-auto mb-4 h-32 md:mb-6 rounded" src="{{ $category->icon() }}" alt="{{ $category->name }} icon" />
          @else
            <div class="mx-auto mb-4 flex h-32 md:mb-6 items-center justify-center rounded bg-primary-600/10 text-3xl font-bold uppercase tracking-widest text-primary-700 dark:text-primary-400">
              {{ Str::limit($category->name, 12, '') }}
            </div>
          @endif

          <div class="flex items-baseline justify-between">
            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $category->name }}</span>
            @if($category->children->count() > 0)
              @php $visibleCount = $category->children->filter($isVisible)->count(); @endphp
              <span class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ $visibleCount }} {{ __('client.options', ['default' => 'options']) }}
              </span>
            @endif
          </div>

          @if($category->description)
            <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $category->description }}</p>
          @endif

          <span class="mt-4 inline-flex items-center gap-1.5 font-medium text-primary-700 dark:text-primary-500">
            {{ $category->children->count() > 0 ? __('client.browse', ['default' => 'Browse']) : __('client.pricing') }}
            <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path>
            </svg>
          </span>
        </a>
      @endforeach
    </div>

  </div>
</section>
@endsection
