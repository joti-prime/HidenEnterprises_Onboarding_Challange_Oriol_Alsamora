@extends(AdminTheme::wrapper(), ['title' => __('admin.settings'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    <div class="card-header">
                        <h4>{!! __('admin.config', ['default' => 'Config']) !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="form-group col-12">
                                <label>{!! __('admin.date_format', ['default' => 'Date Format']) !!}</label>
                                <input type="text" name="date_format" value="@settings('date_format', 'd M Y')"
                                       class="form-control">
                            </div>
                            <div class="form-group col-12">
                                <label>{!! __('admin.terminate_order_after_days') !!}</label>
                                <input type="number" min="1" name="orders::terminate_suspended_after"
                                       value="@settings('orders::terminate_suspended_after', 7)" class="form-control">
                            </div>
                            <div class="form-group col-12">
                                <label>{!! __('admin.delete_terminated_orders') !!}</label>
                                <select class="form-control select2 select2-hidden-accessible" name="orders::delete_terminated" tabindex="-1" aria-hidden="true">
                                    <option value="1" @if(settings('orders::delete_terminated', false) == 1) selected @endif>{!! __('admin.yes') !!}</option>
                                    <option value="0" @if(settings('orders::delete_terminated', false) == 0) selected @endif>{!! __('admin.no') !!}</option>
                                </select>
                            </div>
                            <div class="form-group col-12">
                                <label>{!! __('admin.maxmimum_members_per_order') !!}</label>
                                <input type="number" min="1" name="orders::maxmimum_members"
                                       value="@settings('orders::maxmimum_members', 5)" class="form-control">
                            </div>
                            <div class="form-group col-12">
                                <div class="control-label">
                                    Enable Upcoming invoice reminders
                                </div>
                                <label class="custom-switch mt-2"
                                       onclick="location.href = '@if(settings('email:renewal_reminder', true)) /admin/settings/store?email:renewal_reminder=0 @else /admin/settings/store?email:renewal_reminder=1 @endif';">
                                    <input type="checkbox" name="email:renewal_reminder" value="1"
                                           class="custom-switch-input"
                                           @if(settings('email:renewal_reminder', true)) checked @endif>
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">
                                        Enable email reminders for upcoming invoices
                                    </span>
                                </label>
                            </div>
                            <div class="form-group col-12">
                                <label>Remind upcoming invoice before days</label>
                                <input type="number" min="1" name="first_renewal_reminder_frequency"
                                       value="@settings('first_renewal_reminder_frequency', 3)" class="form-control">
                                <small>How many days before should the upcoming invoice reminder be sent</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">{!! __('admin.submit') !!}</button>
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
