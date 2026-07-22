@extends(Theme::wrapper())

@section('title')
    {!! __('client.payment_successfully') !!}
@endsection

@section('container')
    <section>
        <div class="mx-auto max-w-screen-xl px-4 py-8 lg:px-6 lg:py-16">
            <div class="mx-auto max-w-screen-sm text-center">
<div class="flex justify-center mx-auto mb-6 items-center w-40 h-40 sm:w-48 sm:h-48">
    <img src="https://www.hidencloud.com/hidencloudstorage/LogoHidenCloud.png" 
         class="mr-4 rounded w-64 h-auto" 
         alt="@settings('app_name', 'HCTestDash')" />                     
</div>

                <p class="mb-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-white md:text-4xl">
                    {!! __('client.payment_successfully') !!}
                </p>
                <p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">{!! __('client.payment_successfully_desc') !!}</p>
                <a href="{{ route('dashboard') }}"
                    class="bg-primary-600 hover:bg-primary-800 focus:ring-primary-300 dark:focus:ring-primary-900 my-4 inline-flex rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">
                    {!! __('client.back_dashboard') !!}
                </a>
                @if ($payment->status == 'paid')
                    <a href="{{ route('invoice', $payment->id) }}"
                        class="bg-primary-600 hover:bg-primary-800 focus:ring-primary-300 dark:focus:ring-primary-900 my-4 inline-flex rounded-lg px-5 py-2.5 text-center text-sm font-medium text-white focus:outline-none focus:ring-4">
                        {!! __('client.view_invoice') !!}
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
