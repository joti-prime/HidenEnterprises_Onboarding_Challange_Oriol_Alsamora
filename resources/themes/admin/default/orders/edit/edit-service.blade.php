@extends(AdminTheme::path('orders.edit.wrapper'), ['active' => 'service'])

@section('order-section')
    <div class="card">
        <div class="card-body">
            @include(AdminTheme::path('empty-state'), ['title' => 'Interface not available', 'description' => 'This interface is not available for this order.'])
        </div>
    </div>
@endsection