@extends('install::master')

@section('title', 'Requirements')

@section('content')

@if(!$info['files']['storage'])
<div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
    </svg>
    <span class="sr-only">Info</span>
    <div>
        The {{ storage_path() }} folder is not writable. Please set the permissions of this folder to 775
    </div>
</div>
@endif

@if(!$info['files']['storage'])
<div class="flex items-center p-4 mb-4 text-sm text-red-800 border border-red-300 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400 dark:border-red-800" role="alert">
    <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
    </svg>
    <span class="sr-only">Info</span>
    <div>
        The {{ base_path('bootstrap/cache/') }} folder is not writable. Please set the permissions of this folder to 775
    </div>
</div>
@endif

<div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
    <a href="#">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Requirements</h5>
    </a>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Check the minimum requirements for HCTestDash</p>

    <div class="flex">
        <span class="inline-flex items-center justify-center w-6 h-6 me-2 text-sm font-semibold text-green-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-green-300">
            <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
        </svg>
        <span class="sr-only">Icon description</span></span>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">HCTestDash version {{ config('app.version') }}</p>
    </div>

    <div class="flex">
        <span class="inline-flex items-center justify-center w-6 h-6 me-2 text-sm font-semibold text-green-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-green-300">
            <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
        </svg>
        <span class="sr-only">Icon description</span></span>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">PHP 8.1 or above ({{$info['php']['version']}})</p>
    </div>

    <div class="flex">
        <span class="inline-flex items-center justify-center w-6 h-6 me-2 text-sm font-semibold text-green-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-green-300">
            <svg class="w-2.5 h-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
        </svg>
        <span class="sr-only">Icon description</span></span>
        <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Composer dependencies</p>
    </div>

    <div class="flex items-end justify-between">
        <div class="flex items-center">
            <input id="link-checkbox" type="checkbox" value="" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
            <label for="link-checkbox" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">I agree with the HCTestDash <a href="https://wemx.net/pages/terms-and-conditions" class="text-blue-600 dark:text-blue-500 hover:underline">terms and conditions</a>.</label>
        </div>
        <a href="{{ route('install.config') }}" class="inline-flex items-center text0right px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Begin Installation
            <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
            </svg>
        </a>
    </div>
</div>
@endsection
