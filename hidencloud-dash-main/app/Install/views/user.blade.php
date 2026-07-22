@extends('install::master')

@section('title', 'Admin User')

@section('content')
<div class="p-6 bg-white border border-gray-200 rounded shadow dark:bg-gray-800 dark:border-gray-700">
    <a href="#">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Create Admin User</h5>
    </a>
    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">Create the administrator account for your application</p>

    <form action="" method="POST">
        @csrf

        <div class="mb-4">
            <label for="helper-text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">First Name</label>
            <input name="first_name" value="{{ old('first_name', '') }}" type="text" id="helper-text" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="John" required>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your first name</p>
        </div>

        <div class="mb-4">
            <label for="helper-text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Last Name</label>
            <input name="last_name" value="{{ old('last_name', '') }}" type="text" id="helper-text" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Smith" required>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your last name</p>
        </div>

        <div class="mb-4">
            <label for="helper-text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
            <input name="username" value="{{ old('username', '') }}" type="text" id="helper-text" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="johnsmith" required>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your username</p>
        </div>

        <div class="mb-4">
            <label for="helper-text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
            <input name="email" value="{{ old('email', '') }}" type="email" id="helper-text" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="john@example.com" required>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your email address</p>
        </div>

        <div class="mb-4">
            <label for="helper-text" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
            <input name="password" value="{{ old('password', '') }}" type="password" id="helper-text" aria-describedby="helper-text-explanation" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="********" required>
            <p id="helper-text-explanation" class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please enter your password</p>
        </div>

        <div class="text-right">
            <button type="submit" class="inline-flex items-center text0right px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                Next Step
                <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                </svg>
            </button>
        </div>

    </form>


</div>
@endsection
