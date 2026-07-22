@extends(AdminTheme::wrapper(), ['title' => __('admin.email', ['default' => 'Emails']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between">
                    <h4>{!!  __('admin.email_content', ['default' => 'Email content']) !!}</h4>
                    <div class="form-group col-3">
                        <label for="lang"></label>
                        <select class="form-control" id="lang" name="lang" onchange="window.location.href = '{{ route('emails.messages') }}?lang=' + this.value">
                            @if(Module::isEnabled('locales'))
                                @foreach(lang_module()->getInstalled() as $key => $name)
                                    <option @if($lang == $key) selected @endif value="{{$key}}">{{$name}}</option>
                                @endforeach
                            @else
                                <option value="en">English</option>
                            @endif
                        </select>
                    </div>
                </div>
                <form action="{{ route('emails.messages.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <div class="card-body">

                        <div class="row">
                            @foreach($messages as $key => $massage)
                                <div class="form-group col-md-12 col-12">
                                    <label
                                        for="{{ $key }}"> {{ ucfirst(implode(' ', explode('_', $key))) }} </label>
                                    <textarea class="summernote form-control" id="{{ $key }}" name="messages[{{ $key }}]"
                                              style="display: none;">
                                        {!! $massage !!}
                                    </textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit"
                                class="btn btn-primary">{!!  __('admin.submit', ['default' => 'Submit']) !!}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
    </style>
@endsection
