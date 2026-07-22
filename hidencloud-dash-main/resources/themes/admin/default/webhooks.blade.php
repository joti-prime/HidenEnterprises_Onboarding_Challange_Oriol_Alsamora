@extends(AdminTheme::wrapper(), ['title' => __('admin.webhooks'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
            <div class="card-header">
              <h4>{!! __('admin.webhooks') !!}</h4>
            </div>
            <div class="card-body">
                @csrf
              <div class="row">
                <div class="form-group col-12">
                    <label>Webhook URL</label>
                    <input type="text" name="event_webhook_url" value="@settings('event_webhook_url')" class="form-control">
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Created
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_created', true)) /admin/settings/store?webhook_order_created=0 @else /admin/settings/store?webhook_order_created=1 @endif';">
                        <input type="checkbox" name="webhook_order_created" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_created', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Deleted
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_deleted', true)) /admin/settings/store?webhook_order_deleted=0 @else /admin/settings/store?webhook_order_deleted=1 @endif';">
                        <input type="checkbox" name="webhook_order_deleted" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_deleted', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Cancelled
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_cancelled', true)) /admin/settings/store?webhook_order_cancelled=0 @else /admin/settings/store?webhook_order_cancelled=1 @endif';">
                        <input type="checkbox" name="webhook_order_cancelled" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_cancelled', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Renewed
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_renewed', true)) /admin/settings/store?webhook_order_renewed=0 @else /admin/settings/store?webhook_order_renewed=1 @endif';">
                        <input type="checkbox" name="webhook_order_renewed" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_renewed', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Suspended
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_suspended', true)) /admin/settings/store?webhook_order_suspended=0 @else /admin/settings/store?webhook_order_suspended=1 @endif';">
                        <input type="checkbox" name="webhook_order_suspended" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_suspended', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Unsuspended
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_unsuspended', true)) /admin/settings/store?webhook_order_unsuspended=0 @else /admin/settings/store?webhook_order_unsuspended=1 @endif';">
                        <input type="checkbox" name="webhook_order_unsuspended" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_unsuspended', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>

                <div class="form-group col-6">
                    <div class="control-label">
                        Order Terminated
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_terminated', true)) /admin/settings/store?webhook_order_terminated=0 @else /admin/settings/store?webhook_order_terminated=1 @endif';">
                        <input type="checkbox" name="webhook_order_terminated" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_terminated', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
                </div>


                <div class="form-group col-6">
                    <div class="control-label">
                        Order Upgraded
                    </div>
                    <label class="custom-switch mt-2"
                           onclick="location.href = '@if(settings('webhook_order_upgraded', true)) /admin/settings/store?webhook_order_upgraded=0 @else /admin/settings/store?webhook_order_upgraded=1 @endif';">
                        <input type="checkbox" name="webhook_order_upgraded" value="1" class="custom-switch-input"
                               @if(settings('webhook_order_upgraded', true)) checked @endif>
                        <span class="custom-switch-indicator"></span>
                    </label>
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
<style>
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>
@endsection
