@extends(AdminTheme::wrapper(), ['title' => $service->service->getName() . ' '.__('admin.configuration'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <form action="{{ route('services.store', $service->service->getLowerName()) }}" method="POST">
                    <div class="card-header">
                      <h4>{{ $service->module()->getName() }} {!! __('admin.configuration') !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                      <div class="row">

                        @foreach($service->getConfig()->all() ?? [] as $name => $field)
                        <div class="form-group @isset($field['col']) {{$field['col']}} @else col-6 @endisset" style="display: flex;flex-direction: column;">
                            <label>{!! $field['name'] !!}</label>
                            @if($field['type'] == 'select')
                            <select class="form-control select2 select2-hidden-accessible" tabindex="-1" aria-hidden="true"
                            name="{{ $field['key'] }}"
                            id="{{ $field['key'] }}"
                            @if(isset($field['multiple']) AND $field['multiple']) multiple @endif
                            >
                                @foreach($field['options'] ?? [] as $key => $option)
                                <option value="{{ $key }}"
                                @if(in_array($key, (array) settings(Str::remove("[]", $field['key']), $field['default_value'] ?? ''))) selected @endif
                                >{{ $option }}</option>
                                @endforeach
                            </select>
                            @elseif($field['type'] == 'bool')
                            <label class="custom-switch mt-2">
                                <input type="hidden" name="{{ $field['key'] }}" value="0">
                                <input type="checkbox" name="{{ $field['key'] }}" value="1" class="custom-switch-input" @if(settings($field['key'], $field['default_value'] ?? '')) checked @endif>
                                <span class="custom-switch-indicator"></span>
                              </label>
                            @else
                            <input class="form-control"
                            type="{{ $field['type'] }}"
                            name="{{ $field['key'] }}"
                            id="{{ $field['key'] }}"
                            @isset($field['min']) min="{{$field['min']}}" @endisset
                            @isset($field['max']) max="{{$field['max']}}" @endisset
                            value="{{ settings($field['key'], $field['default_value'] ?? '') }}"
                            placeholder="@isset($field['placeholder']){{$field['placeholder']}} @else{{ $field['name'] }} @endisset"
                            @if(in_array('required', $field['rules'])) required="" @endif>
                            @endif
                            <small class="form-text text-muted">
                                {!! $field['description'] !!}
                            </small>
                        </div>
                        @endforeach

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        @if($service->canTestConnection())
                            <a href="{{ route('services.test-connection', $service->service->getLowerName()) }}" class="btn btn-success mr-2">Test Connection</a>
                        @endif
                        <button type="submit" class="btn btn-primary">{!! __('admin.update') !!}</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
    </div>
@endsection
