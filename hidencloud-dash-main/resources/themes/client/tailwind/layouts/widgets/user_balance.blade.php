<div
    class="mb-4 rounded-lg bg-white p-3 p-6 leading-6 text-gray-500 text-slate-700 shadow rounded-lg dark:bg-gray-800 dark:text-gray-400">
    <div class="text-center text-gray-500 dark:text-gray-400">
        @if (auth()->user()->avatar !== null)
            <img class="mx-auto mb-4 h-20 w-20 rounded-full" src="{{ auth()->user()->avatar() }}" alt="user photo" />
        @else
            <div
                class="relative mb-4 inline-flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-gray-100 dark:bg-gray-600">
                <span
                    class="font-medium text-gray-600 dark:text-gray-300">{{ substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1) }}</span>
            </div>
        @endif
        <h3 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
            <a href="#">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</a>
        </h3>
        <p class="font-light text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>

        @php
            $userGroups = auth()->user()->groups->pluck('name');
            $userBadges = collect([
                ['group' => 'RootSuperAdmin', 'icon' => 'bxs-crown', 'short' => 'Root Admin', 'tooltip' => 'Root Administrator (full system access)', 'color' => 'bg-red-700 ring-red-900/60'],
                ['group' => 'UserVerified', 'icon' => 'bxs-badge-check', 'short' => 'Verified', 'tooltip' => 'Verified user', 'color' => 'bg-blue-700 ring-blue-900/60'],
                ['group' => 'Technical support (viewer+tickets+forms+orders-actions+punishments)', 'icon' => 'bxs-wrench', 'short' => 'Tech Support', 'tooltip' => 'Technical Support (tickets, forms, orders, punishments)', 'color' => 'bg-orange-700 ring-orange-900/60'],
                ['group' => 'Ticekts (only tickets)', 'icon' => 'bxs-purchase-tag-alt', 'short' => 'Ticket Support', 'tooltip' => 'Ticket Support (tickets only)', 'color' => 'bg-purple-700 ring-purple-900/60'],
            ])->filter(fn ($b) => $userGroups->contains($b['group']));
        @endphp
        @if ($userBadges->isNotEmpty())
            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                @foreach ($userBadges as $badge)
                    <div class="group relative inline-block">
                        <span class="inline-flex cursor-help items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold text-white shadow-sm ring-1 transition hover:scale-105 {{ $badge['color'] }}">
                            <i class="bx {{ $badge['icon'] }}"></i> {{ $badge['short'] }}
                        </span>
                        <span class="pointer-events-none invisible absolute left-1/2 top-full z-20 mt-1.5 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:visible group-hover:opacity-100 dark:bg-gray-700">
                            {{ $badge['tooltip'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
        <a href="{{ route('user.settings') }}"
            class="hover:text-primary-700 my-5 inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">
            {!! __('client.update') !!}
        </a>
        <div class="mb-4 leading-none text-gray-900 dark:text-gray-200"
            style="display: flex; justify-content: space-between; align-items: center;">
            {!! __('client.visibility') !!}
            <span class="mr-2 rounded bg-gray-100 px-2.5 py-0.5 text-sm font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                @if (auth()->user()->visibility == 'online')
                    <span class="flex items-center"><i class='bx bxs-circle mr-1 text-green-600'></i> {!! __('client.online') !!}</span>
                @elseif(auth()->user()->visibility == 'away')
                    <span class="flex items-center"><i class='bx bxs-circle mr-1 text-yellow-500'></i> {!! __('client.away') !!}</span>
                @elseif(auth()->user()->visibility == 'busy')
                    <span class="flex items-center"><i class='bx bxs-minus-circle mr-1 text-red-500'></i> {!! __('client.busy') !!}</span>
                @elseif(auth()->user()->visibility == 'offline')
                    <span class="flex items-center"><i class='bx bxs-circle mr-1 text-gray-600'></i> {!! __('client.appear_offline') !!}</span>
                @endif
            </span>
        </div>
        <div class="mb-4 leading-none text-gray-900 dark:text-gray-200"
            style="display: flex; justify-content: space-between; align-items: center;">
            {!! __('client.last_login_at') !!}
            <span class="mr-2 rounded bg-gray-100 px-2.5 py-0.5 text-sm font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                @if (auth()->user()->last_login_at == null)
                    {!! __('client.never') !!}
                @else
                    {{ auth()->user()->last_login_at->diffForHumans() }}
                @endif
            </span>
        </div>
        <div class="mb-4 leading-none text-gray-900 dark:text-gray-200"
            style="display: flex; justify-content: space-between; align-items: center;">
            {!! __('client.member_since') !!}
            <span class="mr-2 rounded bg-gray-100 px-2.5 py-0.5 text-sm font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                {{ auth()->user()->created_at->translatedFormat(settings('date_format', 'd M Y')) }}
            </span>
        </div>
    </div>
</div>
