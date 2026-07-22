@extends(AdminTheme::wrapper(), ['title' => __('admin.activity'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <section class="section">
        <div class="section-body">
            <div class="col-12">
                @includeIf(AdminTheme::path('users.user_nav'))
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{!! __('admin.alt_accounts', ['default' => 'Alt Accounts']) !!}</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{!! __('admin.user') !!}</th>
                                <th>{!! __('admin.email') !!}</th>
                                <th>{!! __('admin.create_at') !!}</th>
                                <th>{!! __('admin.ip_address', ['default' => 'IP Address']) !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            @foreach($ips->get() as $ip)
                                @if($ip->hasDuplicateIp($user->id) !== NULL)
                                    <tr>
                                        <td class="text-left">
                                            <img alt="{{__('Image')}}" src="{{ $ip->hasDuplicateIp($user->id)->avatar() }}"
                                                 class="rounded-circle mr-2" width="35" data-toggle="tooltip" title=""
                                                 data-original-title="{{ $ip->hasDuplicateIp($user->id)->first_name }} {{ $ip->hasDuplicateIp($user->id)->last_name }}">
                                            {{ $ip->hasDuplicateIp($user->id)->username }}
                                        </td>
                                        <td class="align-middle">
                                            {{ $ip->hasDuplicateIp($user->id)->email }}
                                        </td>
                                        <td>{{ $ip->hasDuplicateIp($user->id)->created_at }}</td>
                                        <td>{{ $ip->ip_address}}</td>
                                        <td class="text-right">
                                            <a href="#" class="btn btn-danger"><i
                                                    class="fas fa-solid fa-trash-can"></i> {!! __('admin.delete') !!}</a>
                                            <a href="{{ route('users.edit', ['user' => $ip->hasDuplicateIp($user->id)->id]) }}"
                                               class="btn btn-primary"><i
                                                    class="fas fa-solid fa-user"></i> {!! __('admin.edit') !!}</a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">{!! __('admin.ip_address', ['default' => 'IP Address']) !!}</th>
                            <th scope="col">{{ __('admin.times_used') }}</th>
                            <th class="text-right" scope="col">{!! __('admin.data') !!}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($ips->paginate(15) as $ip)
                            <tr>
                                <td>{{ $ip->ip_address }}</td>
                                <td>{{ $ip->uses }}</td>
                                <td class="text-right">{{ $ip->updated_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $ips->paginate(15)->links(AdminTheme::pagination()) }}
                </div>
            </div>
        </div>
    </section>
@endsection
