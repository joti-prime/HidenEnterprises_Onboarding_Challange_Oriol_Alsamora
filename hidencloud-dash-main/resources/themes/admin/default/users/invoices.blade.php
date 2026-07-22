@extends(AdminTheme::wrapper(), ['title' => __('admin.invoices'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
<section class="section">
    <div class="section-body">
        <div class="col-12">
            @includeIf(AdminTheme::path('users.user_nav'))
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($user->payments->count() < 0)
                    @includeIf(AdminTheme::path('empty-state'), ['title' => 'No Active Invoices', 'description' => 'This user has no invoices in history'])
                @else
                <table class="table table-striped table-md">
                    <tbody><tr>
                      <th>{!! __('admin.id') !!}</th>
                      <th>{!! __('admin.user') !!}</th>
                      <th>{!! __('admin.description') !!}</th>
                      <th>{!! __('admin.amount') !!}</th>
                      <th>{!! __('admin.type') !!}</th>
                      <th>{!! __('admin.status') !!}</th>
                      <th>{!! __('admin.create_at') !!}</th>
                      <th class="text-right">{!! __('admin.actions') !!}</th>
                    </tr>

                    @foreach($user->payments()->latest()->paginate(15) as $payment)
                    <tr>
                      <td>{{ Str::substr($payment->id, 0, 8) }}</td>
                      <td>
                        <a href="{{ route('users.edit', ['user' => $payment->user->id]) }}" style="display: flex; color: #6c757d">
                          <img alt="image" src="{{ $payment->user->avatar() }}"
                               class="rounded-circle mr-1 mt-1" width="32px" height="32px" data-toggle="tooltip" title=""
                               data-original-title="{{ $payment->user->first_name }} {{ $payment->user->last_name }}">
                          <div class="flex">
                            {{ $payment->user->username }} <br>
                            <small>{{ $payment->user->email }}</small>
                          </div>
                        </a>
                      </td>
                      <td>{{ $payment->description }}</td>
                      <td>{{ price($payment->amount) }}</td>
                      <td>{{ $payment->type }}</td>
                      <td>
                          <div class="@if($payment->status == 'paid') badge badge-success
                            @elseif($payment->status == 'unpaid') badge badge-danger @endif">{!! __('admin.' . $payment->status) !!}
                          </div>
                      </td>
                      <td>{{ $payment->created_at->translatedFormat(settings('date_format', 'd M Y')) }}</td>
                      <td class="text-right">
                          <a href="{{ route('payments.edit', ['payment' => $payment->id]) }}" class="btn btn-primary">{!! __('admin.manage') !!}</a>
                      </td>
                    </tr>
                    @endforeach

                  </tbody></table>
                </div>
                @endif
                <div class="card-footer text-right">
                    {{ $user->payments()->latest()->paginate(15)->links(AdminTheme::pagination()) }}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
