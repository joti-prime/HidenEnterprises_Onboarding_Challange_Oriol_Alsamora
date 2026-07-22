@extends(Theme::path('auth.wrapper'))

@section('container')
    <section>
        <div class="grid lg:h-screen lg:grid-cols-2">
            <div class="flex items-center justify-center px-4 py-6 sm:px-0 lg:py-0">
                <form method="POST" action="{{ route('2fa.recover.access') }}" class="w-full max-w-md space-y-4 md:space-y-6 xl:max-w-xl">
                    @csrf
                    <div class="flex flex-col items-center space-x-0 space-y-3">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{!! __('auth.lost_access_to_device') !!}</h1>
                    </div>

                    @include(Theme::path('layouts.alerts'))

                    <div>

                        <label for="recovery_key"
                            class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-300">{!! __('auth.recovery_key') !!}</label>
                        <input type="text" name="recovery_code" id="recovery_key"
                            class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="XXXXX-XXXXX" required="">
                    </div>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center justify-end">
                            <a href="{{ route('2fa.validate') }}"
                                class="text-primary-600 dark:text-primary-500 text-sm font-medium hover:underline">
                                {!! __('auth.use_authenticator_app') !!}
                            </a>
                        </div>
                    </div>
                    <button type="submit"
                        class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 w-full rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">

                        {!! __('auth.recover_access') !!}

                    </button>
                    <p class="text-sm font-light text-gray-500 dark:text-gray-400">
                        {!! __('auth.locked_out') !!}
                    </p>
                </form>
            </div>
            <div class="flex items-center justify-center px-4 py-6 bg-gray-900 lg:py-0 sm:px-0">
                <div class="max-w-md xl:max-w-xl">
                    <a href="#" class="flex items-center mb-4 text-2xl font-semibold text-white">
                        <img class="w-15 h-8 mr-2" src="https://www.hidencloud.com/hidencloudstorage/LogoHidenCloud.png"
                             alt="logo">
                        @settings('app_name', 'HCTestDash')
                    </a>
                    <h1 class="mb-4 text-3xl font-extrabold leading-none tracking-tight text-primary-500 xl:text-5xl">@settings('theme::default::auth::title', 'Your Game, Our World: Hosting Perfected')</h1>
                    <p class="mb-4 font-light text-white lg:mb-8">@settings('theme::default::auth::description', 'Here you might want to explain how everything works. You can edit this in Admin -> configuration -> Theme Settings')
                    </p>
                    <div class="flex items-center divide-x divide-primary-500">
                    <div class="flex pr-3 -space-x-4 sm:pr-5">
                            <a href="https://discord.hidencloud.com" target="_blank">
                                <img class="w-10 h-10 border-2 border-white rounded-full"
                                    src="https://www.hidencloud.com/hidencloudstorage/discordico.png"
                                    alt="{!! __('bonnie avatar') !!}">
                            </a>
                        </div>
                        <div class="pl-3 text-white sm:pl-5 dark:text-white">
                            <span
                                class="text-sm text-white">@settings('theme::default::auth::customers', 'Join over 3.2k members')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        svg {
            border-radius: 0.25rem;
            width: 250px;
            height: 250px;
        }
    </style>
@endsection
