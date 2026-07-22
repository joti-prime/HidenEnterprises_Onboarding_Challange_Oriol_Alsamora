<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('title') - HCTestDash</title>
    <link rel="icon" href="/assets/core/img/logo.png">

    {{-- meta tags --}}
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/css/custom.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    @include(Theme::path('layouts.tailwind'))
    @yield('header')
</head>

<body class="bg-gray-100 flex-col dark:bg-gray-900 flex items-center justify-center h-screen">

<div class="container max-w-screen-xl">
    <div class="sm:flex mb-6">
        <div class="mb-4 flex-shrink-0 sm:mb-0 sm:mr-4">
            <img class="h-16 w-16 text-gray-300 rounded" src="/assets/core/img/logo.png" alt="HCTestDash Logo">
        </div>
        <div style="width: 100%">
            <h4 class="text-lg text-gray-900 dark:text-white font-bold">HCTestDash</h4>
            <div class="flex justify-between items-center">
                <p class="mt-1 text-gray-700 dark:text-gray-400">Take your business to new heights with our innovative software solutions.</p>
                <div class="flex items-center">
                    <div>
                        <button data-tooltip-target="tooltip-dark" type="button" onclick="toggleDarkmode()" aria-label="{{ __('client.toggle_darkmode') }}"
                                class="inline-flex items-center p-2 mr-1 text-sm font-medium text-gray-500 rounded-lg dark:text-gray-400 hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @if (Session::has('success'))
        <div class="flex p-4 mb-4 text-sm text-green-800 border border-green-300 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400 dark:border-green-800"
             role="alert">
            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                      clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">{!! __('client.info') !!}</span>
            <div>
                <span class="font-medium">{!! __('admin.success') !!}!</span> {!! session('success') !!}
            </div>
        </div>
    @endif

    @if (Session::has('error'))
        <div class="flex p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800"
             role="alert">
            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                      clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">{!! __('client.info') !!}</span>
            <div>
                <span class="font-medium">{!! __('admin.error') !!}!</span> {!! session('error') !!}
            </div>
        </div>
    @endif

    @if (Session::has('warning'))
        <div class="flex p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300 dark:border-yellow-800"
             role="alert">
            <svg aria-hidden="true" class="flex-shrink-0 inline w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20"
                 xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd"
                      d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                      clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">{!! __('client.info') !!}</span>
            <div>
                <span class="font-medium">{!! __('admin.warning') !!}!</span> {!! session('warning') !!}
            </div>
        </div>
    @endif

    @yield('content')
</div>
<script>
    function toggleDarkmode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
    }
</script>
</body>
</html>
