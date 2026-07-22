@extends(AdminTheme::wrapper(), ['title' => __('admin.gateways', ['default' => 'Gateways']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{!! __('admin.add_gateway') !!}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('gateways.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="driver">{!! __('admin.driver') !!}</label>
                    <select onchange="setDisplayName()" class="form-control" name="driver">
                        @foreach ($drivers as $key => $driver)
                            <option value="{{ $driver['driver'] }}">{{ Str::replace('_', ' ', $key) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">{!! __('admin.name') !!}</label>
                    <input type="text" name="name" id="name"
                        class="form-control" placeholder="{{ __('admin.credit_card') }}"
                        required>
                    <small class="form-text text-muted">
                        {!!  __('admin.gateway_display_name_desc', ['default' => 'This is the name of the gateway displayed to users']) !!}
                        </small>
                </div>
                <button type="submit" class="btn btn-primary">{!! __('admin.create') !!}</button>
            </form>
        </div>
    </div>

    <script>
        function setDisplayName() {
            document.getElementById('name').value = document.getElementById('driver').value;
        }
    </script>
@endsection
