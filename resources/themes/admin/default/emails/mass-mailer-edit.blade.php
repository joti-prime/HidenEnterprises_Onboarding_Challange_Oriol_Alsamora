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
{{--  Massive email block   --}}
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="{{ route('emails.mass-mailer.update', ['mass_mail' => $email->id]) }}" method="POST">
                    @csrf
                    <div class="card-header">
                        <h4>Create Mass Mail</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ __('admin.subject', ['default' => 'Subject']) }}</label>
                            <div class="col-md-9">
                                <input type="text" class="form-control" name="subject" value="{{ old('subject', $email->subject) }}" placeholder="{{ __('admin.subject', ['default' => 'Subject']) }}" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ __('admin.email_content', ['default' => 'Email Content']) }}</label>
                            <div class="col-md-9">
                                <textarea class="summernote" name="content" required>{{ old('content', $email->content) }}</textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ __('admin.button') }}</label>
                            <div class="col-3">
                                <input type="text" class="form-control" name="button_name" value="{{ old('button_name', $email->button_text) }}" placeholder="{{ __('admin.button_name') }}">
                                <small>Leave empty if you dont wish to include a button</small>
                            </div>
                            <div class="col-6">
                                <input type="url" class="form-control" name="button_url" value="{{ old('button_url', $email->button_url) }}" placeholder="{{ __('admin.button_url') }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Preview</label>
                            <div class="col-9">
                                <a href="#" onclick="preview()" id="preview_link" target="_blank">Preview</a>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">{{ __('admin.users') }}</label>
                            <div class="col-md-9">
                                <select class="form-control select2" name="audience" onchange="audienceChange(this.value)" required>
                                    <option value="all_users" @if(old('audience', $email->audience) == 'all_users') selected @endif>{{ __('admin.all_users') }} ({{ App\Models\MassMail::getMailAudience('all_users')->count() }} {{ __('admin.users') }})</option>
                                    <option value="custom_selection" @if(old('audience', $email->audience) == 'custom_selection') selected @endif>{{ __('admin.custom_selection') }}</option>
                                    <option value="has_orders" @if(old('audience', $email->audience) == 'has_orders') selected @endif>{{ __('admin.users_with_orders') }} ({{ App\Models\MassMail::getMailAudience('has_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="active_orders" @if(old('audience', $email->audience) == 'active_orders') selected @endif>{{ __('admin.active_orders_users') }} ({{ App\Models\MassMail::getMailAudience('active_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="inactive_orders" @if(old('audience', $email->audience) == 'inactive_orders') selected @endif>{{ __('admin.inactive_orders_users') }} ({{ App\Models\MassMail::getMailAudience('inactive_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="suspended_orders" @if(old('audience', $email->audience) == 'suspended_orders') selected @endif>{{ __('admin.suspended_orders_users') }} ({{ App\Models\MassMail::getMailAudience('suspended_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="terminated_orders" @if(old('audience', $email->audience) == 'terminated_orders') selected @endif>{{ __('admin.terminated_orders_users') }} ({{ App\Models\MassMail::getMailAudience('terminated_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="no_orders" @if(old('audience', $email->audience) == 'no_orders') selected @endif>{{ __('admin.no_orders_users') }} ({{ App\Models\MassMail::getMailAudience('no_orders')->count() }} {{ __('admin.users') }})</option>
                                    <option value="subscribed" @if(old('audience', $email->audience) == 'subscribed') selected @endif>{{ __('admin.subscribed_users') }} ({{ App\Models\MassMail::getMailAudience('subscribed')->count() }} {{ __('admin.users') }})</option>
                                    @foreach(Service::all() as $service)
                                        <option value="service_{{ $service->module()->getLowerName() }}" @if(old('audience', $email->audience) == "service_{$service->module()->getLowerName()}") selected @endif>{{ $service->about()->display_name }} {{ __('admin.service') }} ({{ App\Models\MassMail::getMailAudience("service_{$service->module()->getLowerName()}")->count() }} {{ __('admin.users') }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row @if(old('audience', $email->audience) != 'custom_selection') d-none @endif" id="custom_selection_div">
                            <label class="col-md-3 col-form-label">Custom Selection</label>
                            <div class="col-md-9">
                                <select class="form-control select2" name="custom_selection[]" multiple>
                                    @foreach(User::all() as $user)
                                        <option value="{{ $user->id }}" @if(old('custom_selection', $email->custom_selection) && in_array($user->id, old('custom_selection', $email->custom_selection))) selected @endif>{{ $user->username }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Repeat</label>
                            <div class="col-md-9">
                                <select class="form-control select2" name="repeat">
                                    <option value="" @if(!old('repeat', $email->repeat)) selected @endif>Never</option>
                                    <option value="1" @if(old('repeat', $email->repeat) == '1') selected @endif>Every day</option>
                                    <option value="2" @if(old('repeat', $email->repeat) == '2') selected @endif>Every 2 days</option>
                                    <option value="3" @if(old('repeat', $email->repeat) == '3') selected @endif>Every 3 days</option>
                                    <option value="4" @if(old('repeat', $email->repeat) == '4') selected @endif>Every 4 days</option>
                                    <option value="5" @if(old('repeat', $email->repeat) == '5') selected @endif>Every 5 days</option>
                                    <option value="6" @if(old('repeat', $email->repeat) == '6') selected @endif>Every 6 days</option>
                                    <option value="7" @if(old('repeat', $email->repeat) == '7') selected @endif>Every week</option>
                                    <option value="14" @if(old('repeat', $email->repeat) == '14') selected @endif>Every 2 weeks</option>
                                    <option value="30" @if(old('repeat', $email->repeat) == '30') selected @endif>Every month</option>
                                    <option value="60" @if(old('repeat', $email->repeat) == '60') selected @endif>Every 2 months</option>
                                    <option value="90" @if(old('repeat', $email->repeat) == '90') selected @endif>Every 3 months</option>
                                    <option value="120" @if(old('repeat', $email->repeat) == '120') selected @endif>Every 4 months</option>
                                    <option value="150" @if(old('repeat', $email->repeat) == '150') selected @endif>Every 5 months</option>
                                    <option value="180" @if(old('repeat', $email->repeat) == '180') selected @endif>Every 6 months</option>
                                    <option value="210" @if(old('repeat', $email->repeat) == '210') selected @endif>Every 7 months</option>
                                    <option value="240" @if(old('repeat', $email->repeat) == '240') selected @endif>Every 8 months</option>
                                    <option value="270" @if(old('repeat', $email->repeat) == '270') selected @endif>Every 9 months</option>
                                    <option value="300" @if(old('repeat', $email->repeat) == '300') selected @endif>Every 10 months</option>
                                    <option value="330" @if(old('repeat', $email->repeat) == '330') selected @endif>Every 11 months</option>
                                    <option value="360" @if(old('repeat', $email->repeat) == '360') selected @endif>Every year</option>
                                    <option value="720" @if(old('repeat', $email->repeat) == '720') selected @endif>Every 2 years</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-3 col-form-label">Scheduled at</label>
                            <div class="col-md-9">
                                <input type="datetime-local" class="form-control" name="scheduled_at" value="{{ old('scheduled_at', $email->scheduled_at) }}" placeholder="Scheduled at">
                                <small>Leave empty to start sending right away</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary">{{ __('admin.update', ['default' => 'Update']) }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function audienceChange(value) {
        var customSelectionDiv = document.getElementById('custom_selection_div');
        if(value == 'custom_selection') {
            customSelectionDiv.classList.remove('d-none');
        } else {
            customSelectionDiv.classList.add('d-none');
        }
    }

    function preview()
    {
        var previewLink = "{{ route('emails.preview', ['subject' => old('subject'), 'content' => old('content'), 'button[name]' => old('button_name'), 'button[url]' => old('button_url')]) }}";
        var subject = document.querySelector('input[name="subject"]').value;
        var content = document.querySelector('textarea[name="content"]').value;
        var buttonName = document.querySelector('input[name="button_name"]').value;
        var buttonUrl = document.querySelector('input[name="button_url"]').value;
        var previewLink = document.getElementById('preview_link');
        previewLink.href = "{{ route('emails.preview') }}?subject=" + subject + "&content=" + content + "&button[name]=" + buttonName + "&button[url]=" + buttonUrl;
    }
</script>
@endsection