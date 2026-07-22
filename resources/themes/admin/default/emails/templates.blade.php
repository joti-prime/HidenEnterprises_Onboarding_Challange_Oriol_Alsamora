@extends(AdminTheme::wrapper(), ['title' => __('admin.email', ['default' => 'Emails']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
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
            <form action="{{ route('admin.settings.store') }}" method="POST">
                @csrf
                <div class="card-header">
                  <h4>{!!  __('admin.email_templates', ['default' => 'Email Templates']) !!}</h4>
                </div>
                <div class="card-body">
                  <div class="row">

                    <div class="form-group col-12">
                        {{-- <label class="form-label">Default Theme Layout</label> --}}
                        <div class="row gutters-sm">

                        @foreach(EmailTemplate::list() as $template)
                          <div class="col-6 col-sm-3">
                            <label class="imagecheck mb-4">
                              <h6 class="text-dark">{{ $template->name }} {!!  __('admin.developed_by', ['default' => 'developed by']) !!}
                                  {{ $template->author }} (v{{$template->version}})</h6>

                              <input name="email::template" type="radio" value="{{ $template->name }}"
                                     class="imagecheck-input" @if(EmailTemplate::active()->name == $template->name) checked="" @endif>
                              <figure class="imagecheck-figure">
                                <img src="{{ $template->image }}" alt="" class="imagecheck-image">
                              </figure>
                            </label>
                          </div>
                        @endforeach

                        </div>
                      </div>
                  </div>
                </div>
                <div class="card-footer text-right">
                  <button type="submit" class="btn btn-primary">{!!  __('admin.submit', ['default' => 'Submit']) !!}</button>
                </div>
              </div>
            </form>
        </div>
</div>

<style>
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>

@includeIf(AdminTheme::path('marketplace.resources-card'))
@endsection
