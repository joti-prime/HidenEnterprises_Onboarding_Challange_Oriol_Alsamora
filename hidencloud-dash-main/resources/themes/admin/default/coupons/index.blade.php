@extends(AdminTheme::wrapper(), ['title' => __('admin.coupons', ['default' => 'Coupons']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!!  __('admin.coupons', ['default' => 'Coupons']) !!}</div>

                <div class="card-body">
                    <a href="{{ route('coupons.create') }}"
                       class="btn btn-primary">{!!  __('admin.create_coupon', ['default' => 'Create Coupon']) !!}</a>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!!  __('admin.code', ['default' => 'Code']) !!}</th>
                                <th>{!! __('admin.amount', ['default' => 'Amount']) !!}</th>
                                <th>{!!  __('admin.user_left', ['default' => 'Uses left']) !!}</th>
                                {{--                                <th>{!!  __('admin.is_recurring', ['default' => 'Is Recurring']) !!}</th>--}}
                                <th>{!!  __('admin.expires_at', ['default' => 'Expires At']) !!}</th>
                                <th class="text-right">{!! __('admin.actions', ['default' => 'Actions']) !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($coupons as $coupon)
                                <tr>
                                    <td>{{ $coupon->code }}</td>
                                    <td>{{ $coupon->discount_amount }}@if($coupon->discount_type == 'percentage')
                                            %
                                        @else
                                            {{ currency("symbol") }}
                                        @endif</td>
                                    <td>{{ $coupon->allowed_uses }}</td>
                                    {{--                                    <td>@if($coupon->recurring) {!!  __('admin.yes', ['default' => 'Yes']) !!} @else {!!  __('admin.no', ['default' => 'No']) !!} @endif</td>--}}
                                    <td>@if($coupon->expires_at)
                                            {{ $coupon->expires_at }}
                                        @else
                                            {!!  __('admin.never', ['default' => 'never']) !!}
                                        @endif</td>
                                    <td class="text-right">
                                        <a href="{{ route('coupons.edit', $coupon->id) }}"
                                           class="btn btn-primary">{!! __('admin.edit') !!}</a>

                                        <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST"
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="deleteItem(event)" type="submit"
                                                    class="btn btn-danger">{!! __('admin.delete') !!}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
