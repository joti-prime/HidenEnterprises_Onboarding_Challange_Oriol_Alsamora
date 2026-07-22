@extends(AdminTheme::wrapper(), ['title' => __('admin.portal'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
                  <h4>{!! __('admin.portal_select', ['default' => 'Select Portal']) !!}</h4>
                </div>
                <div class="card-body">
                  <div class="row">

                    <div class="form-group col-12">
                        {{-- <label class="form-label">Default Theme Layout</label> --}}
                        <div class="row gutters-sm">

                        @foreach(Portal::list() as $portal)
                          <div class="col-6 col-sm-3">
                            <label class="imagecheck mb-4">
                              <h6 class="text-dark">{{ $portal->name }} {!! __('admin.developed_by') !!} {{ $portal->author }} (v{{$portal->version}})</h6>

                              <input name="portal" type="radio" value="{{ $portal->name }}"
                                     class="imagecheck-input" @if(Portal::active()->name == $portal->name) checked="" @endif>
                              <figure class="imagecheck-figure">
                                <img src="{{ $portal->image }}" alt="" class="imagecheck-image">
                              </figure>
                            </label>
                          </div>
                        @endforeach

                        </div>
                      </div>
                  </div>
                </div>
                <div class="card-footer text-right">
                  <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
                </div>
              </div>
            </form>
        </div>
</div>

<div class="card">
  <div class="card-header">
    <h4>{!! __('admin.portal_settings', ['default' => 'Portal Settings']) !!}</h4>
  </div>
  <div class="card-body">
      @if (View::exists(Portal::path('admin-settings')))
        @includeIf(Portal::path('admin-settings'))
      @else
        <div class="alert alert-warning">
        <div class="alert-title">{!! __('admin.warning') !!}</div>
        {{ Portal::active()->name }} {!! __('admin.portal_warning', ['default' => 'portal does not have configurable settings.']) !!}
        </div>
      @endif
  </div>
</div>

<style>
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>

@includeIf(AdminTheme::path('marketplace.resources-card'))
@endsection
