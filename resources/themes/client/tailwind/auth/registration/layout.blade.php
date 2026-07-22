@extends(Theme::path('auth.wrapper'))

@section('container')
    <section class="bg-white dark:bg-gray-900 min-h-screen">
        <div class="lg:flex min-h-screen">
        <div class="hidden w-full max-w-md p-12 lg:flex lg:flex-col bg-gray-800 min-h-screen">
                <div class="flex items-center mb-8 space-x-4">
                    <a href="#" class="flex items-center text-2xl font-semibold text-white">
                        <img class="w-15 h-8 mr-2" src="https://www.hidencloud.com/hidencloudstorage/LogoHidenCloud.png" />
                        @settings('app_name', 'HCTestDash')
                    </a>
                    <a href="{{ route('login') }}"
                        class="inline-flex items-center text-sm font-medium text-primary-100 hover:text-white">
                        <svg class="w-6 h-6 mr-1" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        {!! __('auth.back_to_login') !!}
                    </a>
                </div>
                <div class="flex-grow flex items-center">
                    <div class="block p-8 text-white rounded-lg bg-gray-700">
                        <h2 class="mb-1 text-2xl font-semibold text-primary-500">@settings('theme::default::auth::title', 'Your Game, Our World: Hosting Perfected')</h2>
                        <p class="mb-4 font-light text-white sm:text-lg">
                        <br>
                            @settings('theme::default::auth::description', 'Here you might want to explain how everything works. You can edit this in Admin -> configuration -> Theme Settings')</p>
                    </div>
                </div>
            </div>
            <div class="mx-auto flex items-center px-4 md:w-[42rem] md:px-8 xl:px-0 min-h-screen">
                <div class="w-full py-8">
                    @yield('content')
                </div>
            </div>
        </div>
    </section>
@endsection
