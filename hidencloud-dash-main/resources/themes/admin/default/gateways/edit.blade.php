@extends(AdminTheme::wrapper(), ['title' => __('admin.gateways', ['default' => 'Gateways']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{!! __('admin.edit_gateway') !!}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('gateways.update', $gateway->id) }}" method="POST">
                @csrf
                @method('PUT')

                @foreach ($gateway->config() as $key => $config)
                    <div class="form-group">
                        @if (!is_array($config))
                            <label for="{{ $key }}">{{ $key }}</label>
                            @if (is_bool($config) or $config === 'false' or $config === 'true')
                                <select class="form-control" id="{{ $key }}" name="{{ $key }}">
                                    <option value="false" @if (!$config === false || $config === 'false') selected="selected" @endif>
                                        {{ __('admin.false') }}</option>
                                    <option value="true" @if ($config === true || $config === 'true') selected="selected" @endif>{{ __('admin.true') }}
                                    </option>
                                </select>
                            @else
                                <input class="form-control" id="{{ $key }}" name="{{ $key }}"
                                    value="{{ $config }}">
                            @endif
                        @endif

                        @if(is_array($config))
                            <label for="{{ $key }}">{{ $key }}</label>
                            <select class="form-control" id="{{ $key }}" name="{{ $key }}">
                                @foreach($config as $key => $value)
                                    <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                @endforeach
                <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
            </form>
        </div>
    </div>
    @includeIf(AdminTheme::path($gateway->blade_edit_path))
    @includeIf($gateway->blade_edit_path)
@endsection
