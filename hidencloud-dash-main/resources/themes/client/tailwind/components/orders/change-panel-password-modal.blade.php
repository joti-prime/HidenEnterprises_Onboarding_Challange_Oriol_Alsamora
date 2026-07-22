@props([
    'order' => $order,
])

@php
    $svc = strtolower($order->package->service ?? '');
    $isPtero = in_array($svc, ['pterodactyl', 'freepterodactyl'], true);
    $extId = $order->external_id ?? '';
    // Skip placeholder marker that exists briefly during creation.
    $hasReadyServer = $isPtero && !empty($extId) && !str_starts_with($extId, 'pending-');

    // Detect admin acting on a server they do not own — show extra warning + JS confirm.
    $adminOnOtherUser = $hasReadyServer
        && auth()->check()
        && auth()->id() !== $order->user_id
        && auth()->user()->isRootAdmin();
    $ownerLabel = $adminOnOtherUser
        ? ($order->user->email ?? $order->user->username ?? ('user #' . $order->user_id))
        : null;

    if ($hasReadyServer) {
        // Prime the OrderServer permission cache so the existing change_password route accepts the request.
        // This route normally relies on a cache populated when the user visits the server panel sub-pages.
        $orderServerClass = $svc === 'freepterodactyl'
            ? \App\Services\FreePterodactyl\Http\Controllers\OrderServer::class
            : \App\Services\Pterodactyl\Http\Controllers\OrderServer::class;
        $orderServerClass::savePermission($order->id, $extId);

        $routeName = $svc . '.settings.change_password';
        $formAction = route($routeName, ['order' => $order->id, 'server' => $extId]);
    }
@endphp

@if ($hasReadyServer)
    <button type="button" data-modal-target="changePanelPassword-{{ $order->id }}"
            data-modal-toggle="changePanelPassword-{{ $order->id }}"
            class="bg-indigo-700 hover:bg-indigo-800 focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800 flex items-center rounded-lg px-3 py-2 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">
        <i class='bx bxs-key mr-1'></i>
        Change Panel Password
    </button>

    <div id="changePanelPassword-{{ $order->id }}" data-modal-backdrop="static" tabindex="-1" aria-hidden="true"
         class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full overflow-y-auto overflow-x-hidden p-4 md:inset-0">
        <div class="relative max-h-full w-full max-w-md">
            <div class="relative rounded-lg bg-white shadow dark:bg-gray-700">
                <div class="flex items-start justify-between rounded-t border-b p-4 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Change Panel Password
                    </h3>
                    <button type="button" data-modal-hide="changePanelPassword-{{ $order->id }}"
                            class="ml-auto inline-flex items-center rounded-lg bg-transparent p-1.5 text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                  clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ $formAction }}" method="POST"
                      @if ($adminOnOtherUser)
                          onsubmit="return confirm('You are changing the panel password of {{ $ownerLabel }} (NOT yours). Continue?');"
                      @endif>
                    @csrf
                    <div class="space-y-4 p-6">
                        @if ($adminOnOtherUser)
                            <div class="rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                                <div class="flex items-start gap-2">
                                    <i class='bx bxs-shield-x mt-0.5 text-base'></i>
                                    <div>
                                        <p class="font-semibold">Admin override</p>
                                        <p class="mt-1">You are not the owner of this server. Submitting will change the panel password of <span class="font-mono">{{ $ownerLabel }}</span>, not yours.</p>
                                    </div>
                                </div>
                                <label class="mt-3 flex items-start gap-2 text-xs">
                                    <input type="checkbox" required
                                           class="mt-0.5 h-4 w-4 rounded border-red-400 text-red-600 focus:ring-red-500 dark:border-red-600 dark:bg-gray-700">
                                    <span>I understand I am modifying another user's account.</span>
                                </label>
                            </div>
                        @endif
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('client.change_service_password', ['service' => 'HidenCloud Panel']) }}
                        </p>
                        <div>
                            <label for="cppm-password-{{ $order->id }}"
                                   class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('auth.new_password') }}
                            </label>
                            <input type="password" name="password" id="cppm-password-{{ $order->id }}"
                                   minlength="8" required autocomplete="new-password"
                                   class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                   placeholder="{{ __('auth.new_password') }}">
                        </div>
                        <div>
                            <label for="cppm-password-confirmation-{{ $order->id }}"
                                   class="mb-1 block text-sm font-medium text-gray-900 dark:text-white">
                                {{ __('auth.confirm_new_password') }}
                            </label>
                            <input type="password" name="password_confirmation"
                                   id="cppm-password-confirmation-{{ $order->id }}"
                                   minlength="8" required autocomplete="new-password"
                                   class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                   placeholder="{{ __('auth.confirm_new_password') }}">
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 rounded-b border-t border-gray-200 p-4 dark:border-gray-600">
                        <button type="button" data-modal-hide="changePanelPassword-{{ $order->id }}"
                                class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:z-10 focus:outline-none focus:ring-4 focus:ring-blue-300 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-600">
                            {{ __('client.discard') }}
                        </button>
                        <button type="submit"
                                class="rounded-lg bg-indigo-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-800 focus:outline-none focus:ring-4 focus:ring-indigo-300 dark:bg-indigo-600 dark:hover:bg-indigo-700 dark:focus:ring-indigo-800">
                            Change Panel Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
