@extends(Theme::wrapper())
@section('title', __('client.profile'))
@section('container')
    <div class="grid grid-cols-1 px-4 pt-6 dark:bg-gray-900 xl:grid-cols-3 xl:gap-4">

        <!-- Right Content -->
        <div class="col-span-full xl:col-auto">
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <div class="items-center sm:flex sm:space-x-4 xl:block xl:space-x-0 2xl:flex 2xl:space-x-4"
                    style="display: flex;justify-content: space-evenly;">
                    @if (auth()->user()->avatar !== null)
                        <img class="mb-4 h-28 w-28 rounded-lg sm:mb-0 xl:mb-4 2xl:mb-0" src="{{ auth()->user()->avatar() }}" alt="user photo">
                    @else
                        <div
                            class="relative mb-4 inline-flex h-28 w-28 items-center justify-center overflow-hidden rounded-full rounded-lg bg-gray-100 dark:bg-gray-600 sm:mb-0 xl:mb-4 2xl:mb-0">
                            <span class="font-medium text-gray-600 dark:text-gray-300">
                                {{ substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h3 class="mb-1 text-2xl font-bold text-gray-900 dark:text-white">{{ auth()->user()->first_name }}
                            {{ auth()->user()->last_name }}</h3>
                        <div class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">
                            {!! __('client.client') !!}
                        </div>
                    </div>
                </div>

                @if(settings('allow_custom_avatars', true))
                <form id="avatar-upload-form" action="{{ route('upload-profile-picture') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <!-- File Preview Section -->
                    <div id="file-preview" class="mb-4 mt-4 hidden">
                        <div class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border-2 border-dashed border-green-300 dark:border-green-500">
                            <div class="flex items-center space-x-4">
                                <img id="preview-image" class="h-16 w-16 rounded-lg object-cover" src="" alt="Preview">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Image selected</p>
                                    <p id="file-info" class="text-xs text-gray-500 dark:text-gray-400"></p>
                                </div>
                                <button type="button" id="remove-file" class="text-red-500 hover:text-red-700">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Success Section -->
                    <div id="upload-success" class="mb-4 mt-4 hidden">
                        <div class="flex items-center justify-center p-4 bg-green-50 dark:bg-green-900 rounded-lg border-2 border-green-300 dark:border-green-500">
                            <div class="flex items-center space-x-3">
                                <svg class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Image uploaded successfully!</p>
                                    <p class="text-xs text-green-600 dark:text-green-300">Your new profile picture has been updated.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Drop Zone -->
                    <label for="dropzone-file" id="dropzone-label"
                        class="dark:hover:bg-bray-800 mb-4 mt-4 flex h-20 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:border-gray-500 dark:hover:bg-gray-600">
                        <div class="flex flex-col items-center justify-center pb-6 pt-5">
                            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                {!! __('client.drag_and_drop') !!}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, JPEG (MAX. 2MB)</p>
                        </div>
                        <input id="dropzone-file" type="file" name="avatar" accept="image/*" required class="hidden">
                    </label>

                    <!-- Upload Button -->
                    <button type="submit" id="upload-btn"
                        class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 inline-flex items-center rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:ring-4 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <svg id="upload-icon" class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"></path>
                            <path d="M9 13h2v5a1 1 0 11-2 0v-5z"></path>
                        </svg>
                        <span id="upload-text">{!! __('client.upload') !!}</span>
                    </button>

                    <!-- Delete Avatar Button -->
                    @if(auth()->user()->avatar !== null)
                    <button type="button" id="delete-avatar-btn" class="bg-red-600 hover:bg-red-700 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800 inline-flex items-center rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:ring-4 ml-2">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        Delete Photo
                    </button>
                    @endif
                </form>
                @endif
            </div>
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <h3 class="text-xl font-bold dark:text-white">{!! __('client.two_factor_authentication') !!}</h3>
                <p class="mt-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                    {!! __('client.two_factor_authentication_desc') !!}
                </p>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-4">
                        <div class="flex justify-end space-x-4">
                            <div class="inline-flex items-center">
                                @if (!Auth::user()->TwoFa()->exists())
                                    <a href="{{ route('2fa.setup') }}"
                                        class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mb-2 mr-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-4">
                                        {!! __('client.enable') !!}
                                    </a>
                                @else
                                    <button type="button" data-modal-target="disableTwoFA" data-modal-toggle="disableTwoFA"
                                        class="mb-2 mr-2 rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                        {!! __('client.disable') !!}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <div class="flow-root">
                    <h3 class="text-xl font-bold dark:text-white">{!! __('client.social_accounts') !!}</h3>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @if (Settings::getJson('encrypted::oauth::google', 'is_enabled', false))
                            <li class="pb-6 pt-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <i class='bx bxl-google dark:text-white' style="font-size: 1.75rem;"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="block truncate text-base font-semibold text-gray-900 dark:text-white">
                                            {!! __('client.google_account') !!}
                                        </span>
                                        <span class="block flex items-center truncate text-sm font-normal text-gray-500 dark:text-gray-400">
                                            @if (Auth::user()->oauthService('google')->exists())
                                                {{ Auth::user()->oauthService('google')->first()->email }} <i
                                                    class='bx bxs-badge-check ml-1'></i>
                                            @else
                                                {!! __('client.not_connected') !!}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="inline-flex items-center">
                                        @if (Auth::user()->oauthService('google')->exists())
                                            <a href="{{ route('oauth.remove', 'google') }}"
                                                class="mb-2 mr-3 rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">
                                                {!! __('client.remove') !!}
                                            </a>
                                        @else
                                            <a href="{{ route('oauth.connect', 'google') }}"
                                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mr-3 rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:ring-4">
                                                {!! __('client.connect') !!}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif
                        @if (Settings::getJson('encrypted::oauth::github', 'is_enabled', false))
                            <li class="pb-6 pt-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <i class='bx bxl-github dark:text-white' style="font-size: 1.75rem;"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="block truncate text-base font-semibold text-gray-900 dark:text-white">
                                            {!! __('client.github_account') !!}
                                        </span>
                                        <span class="block truncate text-sm font-normal text-gray-500 dark:text-gray-400">
                                            @if (Auth::user()->oauthService('github')->exists())
                                                <a class="text-primary-500"
                                                    href="{{ Auth::user()->oauthService('github')->first()->external_profile }}"
                                                    target="_blank">{{ Auth::user()->oauthService('github')->first()->external_profile }}</a>
                                            @else
                                                {!! __('client.not_connected') !!}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="inline-flex items-center">
                                        @if (Auth::user()->oauthService('github')->exists())
                                            <a href="{{ route('oauth.remove', 'github') }}"
                                                class="mb-2 mr-3 rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">
                                                {!! __('client.remove') !!}
                                            </a>
                                        @else
                                            <a href="{{ route('oauth.connect', 'github') }}"
                                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mr-3 rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:ring-4">
                                                {!! __('client.connect') !!}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif
                        @if (Settings::getJson('encrypted::oauth::discord', 'is_enabled', false))
                            <li class="pb-6 pt-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <i class='bx bxl-discord-alt dark:text-white' style="font-size: 1.75rem;"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="block truncate text-base font-semibold text-gray-900 dark:text-white">
                                            {!! __('client.discord_account') !!}
                                        </span>
                                        <span class="block flex items-center truncate text-sm font-normal text-gray-500 dark:text-gray-400">
                                            @if (Auth::user()->oauthService('discord')->exists())
                                                {{ Auth::user()->oauthService('discord')->first()->data->username }} <i
                                                    class='bx bxs-badge-check ml-1'></i>
                                            @else
                                                {!! __('client.not_connected') !!}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="inline-flex items-center">
                                        @if (Auth::user()->oauthService('discord')->exists())
                                            <a href="{{ route('oauth.remove', 'discord') }}"
                                                class="mb-2 mr-3 rounded-lg border border-red-700 px-3 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">
                                                {!! __('client.remove') !!}
                                            </a>
                                        @else
                                            <a href="{{ route('oauth.connect', 'discord') }}"
                                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mr-3 rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:ring-4">
                                                {!! __('client.connect') !!}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endif

                        @includeIf('tickets::client.tailwind.whatsapp.social-account-item')
                    </ul>
                    <div></div>
                </div>
            </div>
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <h3 class="text-xl font-bold dark:text-white">{!! __('client.sessions') !!}</h3>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach (auth()->user()->devices()->latest()->paginate(5) as $device)
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    @if ($device->device_name == 'Phone')
                                        <svg class="h-6 w-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    @else
                                        <svg class="h-6 w-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $device->device_type }}
                                    </p>
                                    <p class="truncate text-sm font-normal text-gray-500 dark:text-gray-400">
                                        {{ $device->device_name }} ({{ $device->ip_address }}) <br>
                                        {!! __('client.last_seen') !!}: {{ $device->last_login_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="inline-flex items-center">
                                    @if (!$device->is_revoked)
                                        <a href="{{ route('revoke', ['device' => $device->id]) }}"
                                            class="focus:ring-primary-300 mb-3 mr-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:ring-4 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                            {!! __('client.revoke') !!}
                                        </a>
                                    @else
                                        <a href="{{ route('revoke', ['device' => $device->id]) }}"
                                            class="mb-3 mr-3 rounded-lg border border-red-700 px-3 px-5 py-2 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">
                                            {!! __('client.revoked') !!}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                    <div class="pagination pt-6 overflow-x-auto">
                        {{ auth()->user()->devices()->latest()->paginate(5)->links(Theme::pagination()) }}
                    </div>
                </ul>
                <div>
                    {{-- <button class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        See more
                    </button> --}}
                </div>
            </div>
            @if (settings('download_user_data', true))
                <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                    <h3 class="text-xl font-bold dark:text-white">{!! __('client.download_my_data') !!}</h3>
                    <p class="mt-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                        {!! __('client.download_data_description') !!}
                    </p>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li class="py-4">
                            <div class="flex justify-end space-x-4">
                                <div class="inline-flex items-center">
                                    <button type="button" data-modal-target="downloadUserData" data-modal-toggle="downloadUserData"
                                        class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 mb-2 mr-2 rounded-lg px-5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-4">
                                        {!! __('client.download') !!}
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            @endif
        </div>
        <div class="col-span-2">
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <h3 class="mb-4 text-xl font-bold dark:text-white">{!! __('client.general_information') !!}</h3>
                <form method="post" action="{{ route('update-username') }}">
                    @csrf
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="first-name"
                                class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{!! __('auth.username') !!}</label>
                            <input type="text" value="{{ auth()->user()->username }}" name="username" required="" id="first-name"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="last-name"
                                class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">{!! __('auth.email') !!}</label>
                            <input type="text" placeholder="{{ auth()->user()->email }}" disabled="" id="last-name"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-400 sm:text-sm opacity-90"
                                required>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="first-name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.first_name') !!}
                            </label>
                            <input type="text" placeholder="{{ auth()->user()->first_name }}" disabled="" id="first-name"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-400 sm:text-sm opacity-90">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="last-name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.last_name') !!}
                            </label>
                            <input type="text" placeholder="{{ auth()->user()->last_name }}" disabled="" id="last-name"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-400 sm:text-sm opacity-90">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="organization" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('client.organization') !!} {!! __('client.optional') !!}
                            </label>
                            <input type="text" name="company_name" value="{{ auth()->user()->address->company_name ?? '' }}" id="organization"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="countries" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Country
                            </label>
                            <input type="text" placeholder="{{ config('utils.countries')[auth()->user()->address->country ?? ''] ?? '' }}" disabled="" id="countries"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-800 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-400 sm:text-sm opacity-90">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="address" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.address') !!}
                            </label>
                            <input type="text" name="address" value="{{ auth()->user()->address->address ?? '' }}" id="address"
                                @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) disabled="" @endif
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) cursor-not-allowed @endif rounded-lg border border-gray-300 bg-gray-50 p-2.5 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:placeholder-gray-400 sm:text-sm @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) opacity-90 @else text-gray-900 dark:text-white @endif">
                        </div>
                        {{-- <div class="col-span-6 sm:col-span-3">
                            <label for="address_2" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                Address 2 (optional)
                            </label>
                            <input type="text" placeholder="address 2" name="address_2" value="{{ auth()->user()->address->address_2 }}"
                                id="address_2"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm">
                        </div> --}}
                        <div class="col-span-6 sm:col-span-3">
                            <label for="city" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('admin.city') !!}
                            </label>
                            <input type="text" placeholder="{{ __('client.city') }}" name="city"
                                value="{{ auth()->user()->address->city ?? '' }}" id="city"
                                @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) disabled="" @endif
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) cursor-not-allowed @endif rounded-lg border border-gray-300 bg-gray-50 p-2.5 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:placeholder-gray-400 sm:text-sm @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) opacity-90 @else text-gray-900 dark:text-white @endif">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="zip_code" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.zip_code') !!}
                            </label>
                            <input type="text" placeholder="{{ __('client.zip_code') }}" name="zip_code"
                                value="{{ auth()->user()->address->zip_code ?? '' }}" id="zip_code"
                                @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOneOne')->exists()) disabled="" @endif
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOneOne')->exists()) cursor-not-allowed @endif rounded-lg border border-gray-300 bg-gray-50 p-2.5 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:placeholder-gray-400 sm:text-sm @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOneOne')->exists()) opacity-90 @else text-gray-900 dark:text-white @endif">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="region" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('client.state_region_provice') !!}
                            </label>
                            <input type="text" placeholder="{{ __('client.region') }}" name="region"
                                value="{{ auth()->user()->address->region ?? '' }}" id="region"
                                @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOneOne')->exists()) disabled="" @endif
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOne')->exists()) cursor-not-allowed @endif rounded-lg border border-gray-300 bg-gray-50 p-2.5 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:placeholder-gray-400 sm:text-sm @if(auth()->user()->groups()->where('name', 'UserVerifiedOneOneOneOne')->exists()) opacity-90 @else text-gray-900 dark:text-white @endif">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" value="1" name="is_subscribed" class="peer sr-only"
                                    @if (auth()->user()->is_subscribed) checked @endif />
                                <div
                                    class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-primary-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 dark:border-gray-600 dark:bg-gray-700 dark:peer-focus:ring-primary-800">
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                    {!! __('client.subscribe_to_emails') !!}
                                </span>
                            </label>
                        </div>
                        <div class="sm:col-full col-span-6">
                            <button
                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:ring-4"
                                type="submit">
                                {!! __('client.save_all') !!}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <h3 class="mb-4 text-xl font-bold dark:text-white">{!! __('auth.update_email') !!}</h3>
                <form method="post" action="{{ route('update-email') }}" autocomplete="off">
                    @csrf
                    <div class="">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="current-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.current_password') !!}
                            </label>
                            <input type="password" name="current_password" id="current-password"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                        </div>
                        <div class="col-span-6 mb-6 mt-6 sm:col-span-3">
                            <label for="new_email" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.new_email') !!}
                            </label>
                            <input type="email" name="new_email" id="new_email"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                required>
                        </div>
                        <div class="sm:col-full col-span-6">
                            <button
                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:ring-4"
                                type="submit">{!! __('client.save_all') !!}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                <h3 class="mb-4 text-xl font-bold dark:text-white">{!! __('auth.update_password') !!}</h3>
                <form method="post" action="{{ route('update-password') }}">
                    @csrf
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="current-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.current_password') !!}
                            </label>
                            <input type="password" name="current_password" id="current-password"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="new-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.new_password') !!}
                            </label>
                            <input type="password" name="new_password" id="new-password" oninput="validatePassword(this.value)"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                            <p class="mt-1 text-xs text-primary-600 hover:underline cursor-pointer" data-modal-target="password-generator-modal" data-modal-toggle="password-generator-modal">{!! __('client.generate_password') !!}</p>
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="confirm-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.confirm_password') !!}
                            </label>
                            <input type="password" name="new_password_confirmation" id="confirm-password"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                        </div>
                        <div class="col-span-full">
                            <div class="mb-1 text-sm font-normal text-gray-500 dark:text-gray-400">
                                {!! __('auth.password_recommended_requirements_desc') !!}:
                            </div>
                            <ul class="space-y-1 pl-4 text-gray-500 dark:text-gray-400">
                                <li id="length-requirement" class="text-xs font-normal flex items-center">
                                    <span class="mr-2" id="length-icon">•</span>
                                    {!! __('auth.password_recommended_requirements_chracters') !!}
                                </li>
                                <li id="lowercase-requirement" class="text-xs font-normal flex items-center">
                                    <span class="mr-2" id="lowercase-icon">•</span>
                                    {!! __('auth.at_least_lowercase_character') !!}
                                </li>
                                <li id="special-requirement" class="text-xs font-normal flex items-center">
                                    <span class="mr-2" id="special-icon">•</span>
                                    {!! __('auth.inclusion_least_special_character') !!}
                                </li>
                            </ul>
                        </div>
                        <div class="sm:col-full col-span-6">
                            <button
                                class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:ring-4"
                                type="submit">
                                {!! __('client.save_all') !!}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @if (settings('delete_user_account', true))
                <div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
                    <h3 class="text-xl font-bold dark:text-white">{!! __('client.delete_my_account') !!}</h3>
                    <p class="mt-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                        {!! __('client.delete_account_description') !!}
                    </p>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li class="py-4">
                            <div class="flex justify-end space-x-4">
                                <div class="inline-flex items-center">
                                    @if ($request = auth()->user()->deletion_requests()->first())
                                        <a href="{{ route('user.cancel-removal') }}"
                                            class="mb-2 mr-2 rounded-lg border border-red-700 px-5 py-2.5 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">{!! __('client.cancel') !!}</a>
                                    @else
                                        <button type="button" data-modal-target="deleteAccountModal" data-modal-toggle="deleteAccountModal"
                                            class="mb-2 mr-2 rounded-lg border border-red-700 px-5 py-2.5 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">{!! __('client.delete_account') !!}</button>
                                    @endif
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            @endif
        </div>

    </div>

    @if (Auth::user()->TwoFa()->exists())
        <!-- Disable 2FA modal -->
        <div id="disableTwoFA" tabindex="-1" aria-hidden="true"
            class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full overflow-y-auto overflow-x-hidden p-4 md:inset-0">
            <div class="relative max-h-full w-full max-w-2xl">
                <!-- Modal content -->
                <form action="{{ route('2fa.disable') }}" method="POST">
                    @csrf
                    <div class="relative rounded-lg bg-white shadow dark:bg-gray-700">
                        <!-- Modal header -->
                        <div class="flex items-start justify-between rounded-t border-b p-4 dark:border-gray-600">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {!! __('client.two_factor_authentication') !!}
                            </h3>
                            <button type="button"
                                class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-hide="disableTwoFA">
                                <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">{{ __('client.disable') }}</span>
                            </button>
                        </div>

                        <!-- Modal body -->
                        <div class="space-y-6 p-6">
                            <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                                {!! __('client.two_factor_authentication_desc') !!}
                            </p>
                            <div>
                                <label for="opt"
                                    class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-300">{!! __('auth.2fa_code') !!}</label>
                                <input type="text" name="OPT" id="opt"
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="XXXXXX" required="">
                            </div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('2fa.recover') }}"
                                        class="text-primary-600 dark:text-primary-500 text-sm font-medium hover:underline">

                                        {!! __('auth.lost_access_to_device') !!}
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="flex items-center justify-end space-x-2 rounded-b border-t border-gray-200 p-6 dark:border-gray-600">
                            <button type="submit"
                                class="rounded-lg bg-red-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">{!! __('client.disable') !!}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Disable 2FA modal -->
    @endif

    <!-- Download User Data modal -->
    <div id="downloadUserData" tabindex="-1" aria-hidden="true"
        class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full overflow-y-auto overflow-x-hidden p-4 md:inset-0">
        <div class="relative max-h-full w-full max-w-2xl">
            <!-- Modal content -->
            <form action="{{ route('user.download-data') }}" method="POST">
                @csrf
                <div class="relative rounded-lg bg-white shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between rounded-t border-b p-4 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {!! __('client.download_my_data') !!}
                        </h3>
                        <button type="button"
                            class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="downloadUserData">
                            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">{{ __('client.close_menu') }}</span>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="space-y-6 p-6">
                        <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                            {!! __('client.download_data_description') !!}
                        </p>
                        <div class="">
                            <label for="confirm-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.confirm_password') !!}
                            </label>
                            <input type="password" name="current_password" id="confirm-password"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                        </div>
                        @if (auth()->user()->TwoFa()->exists())
                            <div>
                                <label for="opt"
                                    class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-300">{!! __('auth.2fa_code') !!}</label>
                                <input type="text" name="OPT" id="opt"
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="XXXXXX" required="">
                            </div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('2fa.recover') }}"
                                        class="text-primary-600 dark:text-primary-500 text-sm font-medium hover:underline">

                                        {!! __('auth.lost_access_to_device') !!}
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center justify-end space-x-2 rounded-b border-t border-gray-200 p-6 dark:border-gray-600">
                        <button data-modal-hide="downloadUserData" type="submit"
                            class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">{!! __('client.download') !!}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Download User Data modal -->

    <!-- User Deletion modal -->
    <div id="deleteAccountModal" tabindex="-1" aria-hidden="true"
        class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full overflow-y-auto overflow-x-hidden p-4 md:inset-0">
        <div class="relative max-h-full w-full max-w-2xl">
            <!-- Modal content -->
            <form action="{{ route('user.request-removal') }}" method="POST">
                @csrf
                <div class="relative rounded-lg bg-white shadow dark:bg-gray-700">
                    <!-- Modal header -->
                    <div class="flex items-start justify-between rounded-t border-b p-4 dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {!! __('client.delete_my_account') !!}
                        </h3>
                        <button type="button"
                            class="ml-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="deleteAccountModal">
                            <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">{{ __('client.close_menu') }}</span>
                        </button>
                    </div>

                    <!-- Modal body -->
                    <div class="space-y-6 p-6">
                        <p class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                            {!! __('client.delete_account_description') !!}
                        </p>
                        <div class="">
                            <label for="confirm-password" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {!! __('auth.confirm_password') !!}
                            </label>
                            <input type="password" name="current_password" id="confirm-password"
                                class="focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-500 dark:focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-900 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 sm:text-sm"
                                placeholder="••••••••" required>
                        </div>
                        @if (auth()->user()->TwoFa()->exists())
                            <div>
                                <label for="opt"
                                    class="mb-2 block text-sm font-medium text-gray-900 dark:text-gray-300">{!! __('auth.2fa_code') !!}</label>
                                <input type="text" name="OPT" id="opt"
                                    class="focus:ring-primary-500 focus:border-primary-500 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="XXXXXX" required="">
                            </div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('2fa.recover') }}"
                                        class="text-primary-600 dark:text-primary-500 text-sm font-medium hover:underline">

                                        {!! __('auth.lost_access_to_device') !!}
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="flex">
                            <div class="flex h-5 items-center">
                                <input id="helper-checkbox" aria-describedby="helper-checkbox-text" name="disclosure" type="checkbox"
                                    value="1" required
                                    class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-primary-600 focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 dark:focus:ring-primary-600">
                            </div>
                            <div class="ml-2 text-sm">
                                <label for="helper-checkbox" class="font-medium text-gray-900 dark:text-gray-300"></label>
                                <p id="helper-checkbox-text" class="text-xs font-normal text-gray-500 dark:text-gray-300">
                                    {!! __('client.delete_account_disclosure') !!}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="flex items-center justify-end space-x-2 rounded-b border-t border-gray-200 p-6 dark:border-gray-600">
                        <button data-modal-hide="deleteAccountModal" type="button"
                            class="bg-primary-700 hover:bg-primary-800 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">{!! __('client.cancel') !!}</button>
                        <button type="submit"
                            class="rounded-lg border border-red-700 px-5 py-2.5 text-center text-sm font-medium text-red-700 hover:bg-red-800 hover:text-white focus:outline-none focus:ring-4 focus:ring-red-300 dark:border-red-500 dark:text-red-500 dark:hover:bg-red-600 dark:hover:text-white dark:focus:ring-red-900">{!! __('client.delete_account') !!}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- User Deletion modal -->

    <!-- Password Generator Modal -->
    <div id="password-generator-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ __('client.generate_password') }}
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="password-generator-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <div>
                        <label for="settings-quantity-input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('client.minimum_characters') }}</label>
                        <div class="relative flex items-center max-w-[8rem]">
                            <button type="button" id="settings-decrement-button" onclick="changePasswordLength(-1)" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                                </svg>
                            </button>
                            <input type="text" min="6" id="settings_password_length" value="12" onchange="regenSettingsPassword()" class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-primary-500 focus:border-primary-500 block w-full py-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" placeholder="12" required />
                            <button type="button" id="settings-increment-button" onclick="changePasswordLength(1)" class="bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:border-gray-600 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 dark:focus:ring-gray-700 focus:ring-2 focus:outline-none">
                                <svg class="w-3 h-3 text-gray-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="settings-generated-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('client.generated_password') }}</label>
                        <input type="text" id="settings_generated_password" class="bg-gray-50 border border-gray-300 mb-3 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <button onclick="regenSettingsPassword()" type="button" class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            <svg class="w-4 h-4 me-2 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/>
                              </svg>
                                {{ __('client.regenerate') }}
                        </button>
                        <button onclick="copySettingsPassword()" type="button" class="px-3 py-2 text-xs font-medium text-center inline-flex items-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            <svg class="w-4 h-4 me-2 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M7 9v6a4 4 0 0 0 4 4h4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h1v2Z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M13 3.054V7H9.2a2 2 0 0 1 .281-.432l2.46-2.87A2 2 0 0 1 13 3.054ZM15 3v4a2 2 0 0 1-2 2H9v6a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-3Z" clip-rule="evenodd"/>
                              </svg>
                                <span id="settings_copy_password">{{ __('client.copy') }}</span>
                        </button>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button data-modal-hide="password-generator-modal" type="button" class="py-2.5 px-5 mr-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">{{ __('client.dismiss') }}</button>
                    <button onclick="copyAndUseSettings()" data-modal-hide="password-generator-modal" type="button" class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">{{ __('client.copy_and_insert') }}</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Override text color for disabled fields */
        input[disabled],
        select[disabled] {
            color: #6b7280 !important; /* text-gray-800 */
        }
        
        /* Dark mode with highest specificity */
        html[data-bs-theme="dark"] input[disabled],
        html[data-bs-theme="dark"] select[disabled],
        html.dark input[disabled],
        html.dark select[disabled],
        [data-bs-theme="dark"] input[disabled],
        [data-bs-theme="dark"] select[disabled],
        body.dark input[disabled],
        body.dark select[disabled] {
            color: #d1d5db !important; /* text-gray-300 */
        }
        
        /* Force address fields specifically - more gray */
        input[name="address"][disabled],
        input[name="city"][disabled], 
        input[name="region"][disabled],
        input[name="zip_code"][disabled] {
            color: #9ca3af !important; /* text-gray-400 - more gray */
        }
        
        html[data-bs-theme="dark"] input[name="address"][disabled],
        html[data-bs-theme="dark"] input[name="city"][disabled],
        html[data-bs-theme="dark"] input[name="region"][disabled], 
        html[data-bs-theme="dark"] input[name="zip_code"][disabled],
        html.dark input[name="address"][disabled],
        html.dark input[name="city"][disabled],
        html.dark input[name="region"][disabled],
        html.dark input[name="zip_code"][disabled],
        [data-bs-theme="dark"] input[name="address"][disabled],
        [data-bs-theme="dark"] input[name="city"][disabled],
        [data-bs-theme="dark"] input[name="region"][disabled],
        [data-bs-theme="dark"] input[name="zip_code"][disabled],
        body.dark input[name="address"][disabled],
        body.dark input[name="city"][disabled], 
        body.dark input[name="region"][disabled],
        body.dark input[name="zip_code"][disabled] {
            color: #9ca3af !important; /* text-gray-400 - more gray */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('dropzone-file');
            const filePreview = document.getElementById('file-preview');
            const uploadSuccess = document.getElementById('upload-success');
            const dropzoneLabel = document.getElementById('dropzone-label');
            const previewImage = document.getElementById('preview-image');
            const fileInfo = document.getElementById('file-info');
            const removeFileBtn = document.getElementById('remove-file');
            const uploadBtn = document.getElementById('upload-btn');
            const deleteAvatarBtn = document.getElementById('delete-avatar-btn');
            const uploadText = document.getElementById('upload-text');
            const uploadIcon = document.getElementById('upload-icon');
            const form = document.getElementById('avatar-upload-form');

            // Handle file selection
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file
                    if (!validateFile(file)) {
                        return;
                    }

                    // Show preview
                    showPreview(file);
                }
            });

            // Drag-and-drop on the dropzone label.
            // Browsers don't auto-forward dropped files into a nested <input type="file">,
            // so we have to wire dragover/drop ourselves and assign files via DataTransfer.
            const highlightClasses = ['border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20'];

            ['dragenter', 'dragover'].forEach(evt => {
                dropzoneLabel.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (e.dataTransfer) e.dataTransfer.dropEffect = 'copy';
                    dropzoneLabel.classList.add(...highlightClasses);
                });
            });

            ['dragleave', 'drop'].forEach(evt => {
                dropzoneLabel.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzoneLabel.classList.remove(...highlightClasses);
                });
            });

            dropzoneLabel.addEventListener('drop', function(e) {
                if (!e.dataTransfer || !e.dataTransfer.files || e.dataTransfer.files.length === 0) return;

                const file = e.dataTransfer.files[0];
                if (!validateFile(file)) return;

                // Assign the dropped file to the hidden input so the form submits it normally.
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;

                // Reuse the existing change handler for preview/validation.
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            });

            // Prevent the browser from opening files dropped outside the dropzone.
            ['dragover', 'drop'].forEach(evt => {
                window.addEventListener(evt, function(e) {
                    if (e.target.closest && e.target.closest('#dropzone-label')) return;
                    e.preventDefault();
                });
            });

            // Remove file
            removeFileBtn.addEventListener('click', function() {
                resetForm();
            });

            // Delete avatar button
            if (deleteAvatarBtn) {
                deleteAvatarBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete your profile picture?')) {
                        deleteAvatar();
                    }
                });
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                // Show loading state
                showLoadingState();
                
                // Submit form via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showUploadSuccess();
                        // Update the main avatar image
                        const mainAvatar = document.querySelector('.items-center img[src*="avatar"]');
                        if (mainAvatar && data.avatar_url) {
                            mainAvatar.src = data.avatar_url + '?t=' + Date.now();
                        }
                    } else {
                        showError(data.message || 'Error uploading image');
                        resetUploadButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Error uploading image');
                    resetUploadButton();
                });
            });

            function validateFile(file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                const maxSize = 2 * 1024 * 1024; // 2MB

                if (!validTypes.includes(file.type)) {
                    showError('Only JPG, PNG files are allowed');
                    fileInput.value = '';
                    return false;
                }

                if (file.size > maxSize) {
                    showError('File must be smaller than 2MB');
                    fileInput.value = '';
                    return false;
                }

                return true;
            }

            function showPreview(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
                    
                    // Show preview, hide dropzone
                    filePreview.classList.remove('hidden');
                    dropzoneLabel.classList.add('hidden');
                    
                    // Enable upload button
                    uploadBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            }

            function showLoadingState() {
                uploadBtn.disabled = true;
                uploadText.textContent = 'Uploading...';
                uploadIcon.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            }

            function showUploadSuccess() {
                // Hide preview and show success
                filePreview.classList.add('hidden');
                uploadSuccess.classList.remove('hidden');
                
                // Reset form after a delay
                setTimeout(() => {
                    resetForm();
                    hideUploadSuccess();
                    // Reload page to show updated avatar and delete button
                    location.reload();
                }, 2000);
            }

            function resetForm() {
                fileInput.value = '';
                filePreview.classList.add('hidden');
                dropzoneLabel.classList.remove('hidden');
                uploadBtn.disabled = true;
                resetUploadButton();
            }

            function resetUploadButton() {
                uploadBtn.disabled = true;
                uploadBtn.classList.remove('hidden');
                uploadText.textContent = '{{ __("client.upload") }}';
                uploadIcon.innerHTML = `
                    <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"></path>
                    <path d="M9 13h2v5a1 1 0 11-2 0v-5z"></path>
                `;
            }

            function hideUploadSuccess() {
                uploadSuccess.classList.add('hidden');
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function deleteAvatar() {
                // Show loading state on delete button
                if (deleteAvatarBtn) {
                    deleteAvatarBtn.disabled = true;
                    deleteAvatarBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Deleting...
                    `;
                }

                // Send delete request
                fetch('{{ route("delete-profile-picture") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to show changes
                        location.reload();
                    } else {
                        showError(data.message || 'Error deleting image');
                        resetDeleteButton();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Error deleting image');
                    resetDeleteButton();
                });
            }

            function resetDeleteButton() {
                if (deleteAvatarBtn) {
                    deleteAvatarBtn.disabled = false;
                    deleteAvatarBtn.innerHTML = `
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        Delete Photo
                    `;
                }
            }

            function showError(message) {
                // You can implement a toast notification or alert here
                alert(message);
            }
        });

        // Password validation functions
        function validatePassword(password) {
            const lengthReq = document.getElementById('length-requirement');
            const lowercaseReq = document.getElementById('lowercase-requirement');
            const specialReq = document.getElementById('special-requirement');
            
            const lengthIcon = document.getElementById('length-icon');
            const lowercaseIcon = document.getElementById('lowercase-icon');
            const specialIcon = document.getElementById('special-icon');

            // Check length (10-100 characters)
            if (password.length >= 10 && password.length <= 100) {
                lengthReq.classList.remove('text-gray-500', 'dark:text-gray-400');
                lengthReq.classList.add('text-green-600', 'dark:text-green-400');
                lengthIcon.innerHTML = '✓';
            } else {
                lengthReq.classList.remove('text-green-600', 'dark:text-green-400');
                lengthReq.classList.add('text-gray-500', 'dark:text-gray-400');
                lengthIcon.innerHTML = '•';
            }

            // Check lowercase
            if (/[a-z]/.test(password)) {
                lowercaseReq.classList.remove('text-gray-500', 'dark:text-gray-400');
                lowercaseReq.classList.add('text-green-600', 'dark:text-green-400');
                lowercaseIcon.innerHTML = '✓';
            } else {
                lowercaseReq.classList.remove('text-green-600', 'dark:text-green-400');
                lowercaseReq.classList.add('text-gray-500', 'dark:text-gray-400');
                lowercaseIcon.innerHTML = '•';
            }

            // Check special characters
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\?]/.test(password)) {
                specialReq.classList.remove('text-gray-500', 'dark:text-gray-400');
                specialReq.classList.add('text-green-600', 'dark:text-green-400');
                specialIcon.innerHTML = '✓';
            } else {
                specialReq.classList.remove('text-green-600', 'dark:text-green-400');
                specialReq.classList.add('text-gray-500', 'dark:text-gray-400');
                specialIcon.innerHTML = '•';
            }
        }

        // Password generator functions for settings
        function regenSettingsPassword() {
            const length = document.getElementById('settings_password_length').value;
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+';
            const charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            document.getElementById('settings_generated_password').value = result;
        }

        function changePasswordLength(change) {
            const lengthInput = document.getElementById('settings_password_length');
            let currentLength = parseInt(lengthInput.value);
            currentLength += change;
            if (currentLength < 6) currentLength = 6;
            if (currentLength > 100) currentLength = 100;
            lengthInput.value = currentLength;
            regenSettingsPassword();
        }

        function copySettingsPassword() {
            const copyText = document.getElementById("settings_generated_password");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            document.getElementById('settings_copy_password').innerHTML = '{{ __("client.copied") }}';
            setTimeout(() => {
                document.getElementById('settings_copy_password').innerHTML = '{{ __("client.copy") }}';
            }, 2000);
        }

        function copyAndUseSettings() {
            copySettingsPassword();
            const generatedPassword = document.getElementById('settings_generated_password').value;
            document.getElementById('new-password').value = generatedPassword;
            document.getElementById('confirm-password').value = generatedPassword;
            
            // Trigger validation
            validatePassword(generatedPassword);
            
            // Make the password fields visible temporarily
            document.getElementById('new-password').type = 'text';
            document.getElementById('confirm-password').type = 'text';
            setTimeout(() => {
                document.getElementById('new-password').type = 'password';
                document.getElementById('confirm-password').type = 'password';
            }, 3000);
        }

        // Initialize password generator
        document.addEventListener('DOMContentLoaded', function() {
            regenSettingsPassword();
        });
    </script>
@endsection
