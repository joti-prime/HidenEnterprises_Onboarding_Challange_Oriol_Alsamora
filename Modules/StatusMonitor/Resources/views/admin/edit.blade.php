@extends(AdminTheme::wrapper(), ['title' => 'Edit Monitor', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit monitor: {{ $monitor->name }}</div>
                <div class="card-body">
                    <form action="{{ route('admin.statusmonitor.update', $monitor) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('statusmonitor::admin._form')

                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <a href="{{ route('admin.statusmonitor.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
