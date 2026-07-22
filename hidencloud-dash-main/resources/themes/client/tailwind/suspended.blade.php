@extends(Theme::wrapper())

@section('title', 'Suspended')

@section('header')
    <link rel="stylesheet" href="{{ Theme::get('Default')->assets }}assets/css/typography.min.css">
@endsection

@section('container')
    <section>
        <div class="mx-auto max-w-screen-md px-4 py-8 text-center lg:px-12 lg:py-16">
            <a href="#" class="mb-5 flex items-center justify-center text-3xl font-semibold text-gray-900 dark:text-white">
                <img src="@settings('logo')" style="width: 40px;" class="mr-2 rounded" alt="">
                @settings('app_name')
            </a>
            <h1 class="mb-4 text-4xl font-bold leading-none tracking-tight text-gray-900 dark:text-white md:text-5xl lg:mb-6 xl:text-6xl">
                {{ __('client.account_suspended') }}</h1>

            @php
                // Show category only when this is an automated abuse-checker
                // punishment. Keywords / paths stay admin-only.
                // $ban may be a Punishment model OR a stdClass fake when the
                // user is status='suspended' without a punishment row, so
                // guard each property access.
                $banCategory = null;
                $banSource = $ban->source ?? null;
                $banMetadata = $ban->metadata ?? null;
                $isSecondStrike = is_array($banMetadata) && !empty($banMetadata['second_strike']);
                if ($banSource && str_starts_with($banSource, 'abuse_checker_') && is_array($banMetadata) && !empty($banMetadata['category'])) {
                    $banCategory = ucfirst($banMetadata['category']);
                }
            @endphp

            @if($isSecondStrike)
                <div class="mx-auto mt-6 mb-10 max-w-xl rounded-lg border border-red-500 bg-red-50 dark:bg-red-900/30 p-4 text-left">
                    <p class="font-semibold text-red-700 dark:text-red-300">
                        Second detection. This ban is final.
                    </p>
                    <p class="mt-2 text-sm text-red-700 dark:text-red-300">
                        This account previously received a one-time amnesty for a similar Terms of Service violation. The current detection is the second one and no further appeals will be considered.
                    </p>
                </div>
            @endif

            @if($banCategory)
                <p class="font-light text-gray-500 dark:text-gray-400 md:text-lg xl:text-xl">
                    Reason: <span class="font-medium text-gray-900 dark:text-white">{{ $banCategory }}</span>
                </p>
            @endif

            <p class="font-light text-gray-500 dark:text-gray-400 md:text-lg xl:text-xl"><br>{{ __('client.reference_id') }}
                <span class="font-medium text-gray-900 dark:text-white">#{{ $ban->id }}</span>
            </p>
            @isset($ban->expires_at)
                <p class="font-light text-gray-500 dark:text-gray-400 md:text-lg xl:text-xl">{{ __('admin.expires_in') }}:
                    {{ $ban->expires_at->diffForHumans() }}
                </p>
            @endisset
            
            <div class="mt-8">
                @php
                    // Form 13 = suspension appeal. Show the existing submissions plus a
                    // button to open a NEW appeal, unless any previous submission is
                    // terminally rejected (rejected_1 / rejected_2 still allow the user
                    // to reply on the existing submission, so they are NOT terminal).
                    $appealFormId = 13;
                    $userSubmissions = class_exists('\Modules\Forms\Entities\Submission')
                        ? \Modules\Forms\Entities\Submission::where('user_id', auth()->id())
                            ->where('form_id', $appealFormId)
                            ->orderBy('created_at', 'desc')
                            ->get()
                        : collect();
                    $hasRejected = $userSubmissions->contains(fn($s) => $s->status === 'rejected');
                    $activeStatuses = ['open', 'pending', 'in_progress', 'under_review', 'on_hold', 'rejected_1', 'rejected_2', 'rejected_pending'];
                    $hasActiveAppeal = $userSubmissions->contains(fn($s) => in_array($s->status, $activeStatuses, true));
                    $hasSubmissions = $userSubmissions->isNotEmpty();
                    $statusBadge = [
                        'open' => 'Open',
                        'pending' => 'Pending Review',
                        'rejected_1' => 'Rejected-1',
                        'rejected_2' => 'Rejected-2',
                        'rejected_pending' => 'Rejected-Pending',
                        'rejected' => 'Rejected',
                        'approved' => 'Approved',
                    ];
                @endphp

                @if($hasSubmissions && Route::has('forms.view-submission'))
                    <div class="space-y-2 flex flex-col items-center">
                        @foreach($userSubmissions as $submission)
                            <a href="{{ route('forms.view-submission', $submission->token) }}" class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:focus:ring-blue-900">
                                <i class="bx bx-file mr-2"></i>
                                Check Appeal #{{ $submission->id }}
                                <span class="ml-2 text-sm opacity-75">({{ $statusBadge[$submission->status] ?? ucfirst($submission->status) }} · {{ $submission->created_at->format('d/m/Y H:i') }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($isSecondStrike)
                    <p class="mt-4 text-sm text-red-500 dark:text-red-400">The amnesty for the previous violation has already been used. No further appeals will be considered.</p>
                @elseif($hasRejected)
                    <p class="mt-4 text-sm text-red-500 dark:text-red-400">A previous appeal was rejected after all response rounds. You cannot file another one.</p>
                @elseif(!$hasActiveAppeal && Route::has('forms.view'))
                    <a href="{{ route('forms.view', 'appeal-suspended-by-system') }}" class="inline-flex items-center justify-center mt-4 px-5 py-3 text-base font-medium text-center text-white bg-primary-700 rounded-lg hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 dark:focus:ring-primary-900">
                        {{ $hasSubmissions ? 'Open a new appeal' : 'Appeal Suspension' }}
                        <svg class="w-5 h-5 ml-2 -mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
