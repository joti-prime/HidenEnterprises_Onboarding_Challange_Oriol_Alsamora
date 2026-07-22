{{-- header  --}}
<header>

<!--
<div id="maintenance-banner" class="relative bg-blue-600 dark:bg-blue-800 w-full" style="display: none;">
    <div class="mx-auto max-w-screen-xl px-4 py-3 md:px-6">
        <div class="flex items-center justify-between flex-wrap w-full">
            <div class="flex flex-1 items-center">
                <span class="flex rounded-lg bg-blue-800 dark:bg-blue-900 p-1 mr-3">
                    <i class='bx bx-info-circle text-white'></i>
                </span>
                <p class="font-medium text-white">
                    <span class="md:hidden">ES: Los servidores gratis estarán disponibles proximamente debido a un mantenimiento.</span>
                    <span class="hidden md:inline">ES: Los servidores gratis estarán disponibles proximamente debido a un mantenimiento.</span>
                    <br/>
                    <span class="md:hidden">EN: Free servers will be available soon due to maintenance.</span>
                    <span class="hidden md:inline">EN: Free servers will be available soon due to maintenance.</span>
                </p>
            </div>
            <div class="order-2 flex-shrink-0 mt-0">
                <button id="close-banner" type="button" class="flex text-white focus:outline-none">
                    <span class="sr-only">Dismiss</span>
                    <i class='bx bx-x text-xl'></i>
                </button>
            </div>
        </div>
    </div>
</div>
-->

<nav class="bg-white border-gray-200 px-4 lg:px-6 py-2.5 dark:bg-gray-900">
        <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl px-4 md:px-6">
            <div class="flex justify-start items-center">
                <a href="/" class="flex mr-6 xl:mr-8">
                    @if (Settings::has('logo'))
                        <img src="https://www.hidencloud.com/hidencloudstorage/LogoHidenCloud.png" class="mr-4 w-15 h-10 rounded"
                            alt="@settings('app_name', 'HCTestDash')" />
                    @endif
                    <span
                        class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">@settings('app_name',
                        'HCTestDash')</span>
                </a>

            </div>
            @include(Theme::path('layouts.widgets.user-dropdown'))
        </div>
    </nav>



    <div class="border-b border-t border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
        <div class="mx-auto flex max-w-screen-xl flex-wrap items-center justify-between px-4 md:px-6">
            <ul class="-mb-px -ml-4 flex flex-wrap">
                <li class="mr-2">
                    <a class="{{ is_active('dashboard') }} group inline-flex rounded-t-lg border-b-2 border-gray-50 px-4 py-4 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400"
                       href="{{ route('dashboard') }}">
                        <span class="mr-2" style="font-size: 20px;">
                            <i class='bx bxs-dashboard'></i>
                        </span>
                        {!! __('client.dashboard') !!}
                    </a>
                </li>
                <li class="mr-2">
                    <a class="{{ is_active('news.index') }} group inline-flex rounded-t-lg border-b-2 border-gray-50 px-4 py-4 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400"
                       href="{{ route('news.index') }}">
                        <span class="mr-2" style="font-size: 20px;">
                            <i class='bx bxs-news'></i>
                        </span>
                        {{ __('client.news') }}
                    </a>
                </li>
                <li class="mr-2">
                    <a class="{{ is_active('store.index') }} group inline-flex rounded-t-lg border-b-2 border-gray-50 px-4 py-4 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400"
                       href="{{ route('store.index') }}">
                        <span class="mr-2" style="font-size: 20px;">
                            <i class='bx bxs-server'></i>
                        </span>
                        {!! __('client.services') !!}
                    </a>
                </li>

                @foreach (Page::getActive() as $page)
                    @if (in_array('navbar', $page->placement))
                        <li class="mr-2">
                            <a class="{{ is_active('page', ['page' => $page->path]) }} group inline-flex rounded-t-lg border-b-2 border-gray-50 px-4 py-4 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400"
                               href="{{ route('page', $page->path) }}" @if ($page->new_tab) target="_blank" @endif>
                                <span class="mr-2" style="font-size: 20px;">
                                    {!! $page->icon !!}
                                </span>
                                {{ __($page->name) }}
                            </a>
                        </li>
                    @endif
                @endforeach

                {{-- load module nav items  --}}
                @foreach (enabledModules() as $module)
                    @if (config($module->getLowerName() . '.elements.main_menu'))
                        @foreach (config($module->getLowerName() . '.elements.main_menu') as $key => $menu)
                            <li class="mr-2">
                                <a class="{{ is_active($menu['href'], ['module' => true]) }} group inline-flex rounded-t-lg border-b-2 border-gray-50 px-4 py-4 text-center text-sm font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400"
                                   href="{{ $menu['href'] }}">
                                    <span class="mr-2" style="font-size: 20px; {{ $menu['style'] }}">
                                        {!! $menu['icon'] !!}
                                    </span>
                                    {!! __($menu['name']) !!}
                                </a>
                            </li>
                        @endforeach
                    @endif
                    @includeIf(Theme::moduleView($module->getLowerName(), 'elements.main-menu'))
                @endforeach
            </ul>
        </div>
    </div>
</header>
{{-- end header --}}

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if the banner has been dismissed before showing it
        if (localStorage.getItem('maintenanceBannerDismissed') !== 'true') {
            // Use a small timeout to ensure smooth rendering
            setTimeout(function() {
                document.getElementById('maintenance-banner').style.display = 'block';
            }, 10);
        }

        // Add event listener to close button
        document.getElementById('close-banner').addEventListener('click', function() {
            document.getElementById('maintenance-banner').style.display = 'none';
            localStorage.setItem('maintenanceBannerDismissed', 'true');
        });
    });
</script>