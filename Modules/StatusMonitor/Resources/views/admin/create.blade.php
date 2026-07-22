@extends(AdminTheme::wrapper(), ['title' => 'Add Monitor', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add monitor</div>
                <div class="card-body">
                    <form action="{{ route('admin.statusmonitor.store') }}" method="POST">
                        @csrf
                        @include('statusmonitor::admin._form')

                        <button type="submit" class="btn btn-primary">Create monitor</button>
                        <a href="{{ route('admin.statusmonitor.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
