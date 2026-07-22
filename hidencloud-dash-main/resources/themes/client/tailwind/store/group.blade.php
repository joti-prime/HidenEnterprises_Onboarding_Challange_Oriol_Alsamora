@extends(Theme::wrapper())

@section('title', $category->name)

@section('container')
@php
    $isVisible = function ($cat) {
        if (in_array($cat->status, ['unlisted', 'inactive', 'admin_unlisted'])) {
            return false;
        }
        if ($cat->status === 'restricted') {
            return Auth::check() && Auth::user()->is_admin();
        }
        return true;
    };

    $children = $category->children()->orderBy('order')->get()->filter($isVisible);
    $isFreeGroup = $category->link === 'group-free';
@endphp

<section class="antialiased">
  <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">

    <nav class="mb-4 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
      <ol class="flex items-center gap-2">
        <li>
          <a href="{{ route('store.index') }}" class="hover:text-primary-700 hover:underline dark:hover:text-primary-400">{!! __('client.services') !!}</a>
        </li>
        <li aria-hidden="true">/</li>
        <li class="text-gray-700 dark:text-gray-200">{{ $category->name }}</li>
      </ol>
    </nav>

    <div class="mb-6 flex items-center justify-between gap-4 md:mb-8">
      <h2 class="text-2xl font-semibold text-gray-900 dark:text-white sm:text-3xl">{{ $category->name }}</h2>
      @if($category->description)
        <span class="hidden text-sm text-gray-500 dark:text-gray-400 sm:inline">{{ $category->description }}</span>
      @endif
    </div>

    @if($children->isEmpty())
      <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
        {!! __('client.no_categories_in_group', ['default' => 'No services available in this group yet.']) !!}
      </div>
    @else
      <div class="flex flex-wrap justify-center gap-4 xl:gap-8">
        @foreach($children as $child)
          <a href="{{ route('store.service', ['service' => $child->link]) }}" class="block w-full max-w-sm rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:border-primary-400 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-500 sm:w-[calc(50%-0.5rem)] lg:w-[calc(33.333%-1rem)]">
            @if($child->icon)
              <img class="mx-auto mb-4 h-48 md:mb-6 rounded" src="{{ $child->icon() }}" alt="{{ $child->name }} icon" />
            @endif
            <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $child->name }}</span>
            @if($child->description)
              <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $child->description }}</p>
            @endif
            <span class="mt-4 inline-flex items-center gap-1.5 font-medium text-primary-700 dark:text-primary-500">
              {{ $isFreeGroup ? __('client.get_now') : __('client.pricing') }}
              <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5m14 0-4 4m4-4-4-4"></path>
              </svg>
            </span>
          </a>
        @endforeach
      </div>
    @endif

  </div>
</section>
@endsection
