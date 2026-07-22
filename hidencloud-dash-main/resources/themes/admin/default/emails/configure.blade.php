@extends(AdminTheme::wrapper(), ['title' =>  __('admin.email', ['default' => 'Emails']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

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

            <div class="alert alert-warning">
                <div class="alert-title">{!!  __('admin.important', ['default' => 'Important']) !!}</div>
                {!!  __('admin.important_emails_page', ["path"=> base_path('.env'), 'default' => "
                SMTP details are stored within your <code>:path</code> file. To update, you can edit
                this file and update the corresponding values. <br><br>
                You can setup an internal SMTP server or use services such as Google, MailGun, TrapMail etc... <br>
                Documentation: <a target='_blank' href='https://docs.wemx.net/en/setup/mail-configuration'>https://docs.wemx.net/en/setup/mail-configuration</a>"]) !!}

            </div>

            <div class="card">
                <form action="#" method="POST">
                    <div class="card-header">
                        <h4>{!!  __('admin.smtp_server', ['default' => 'SMTP Server']) !!}</h4>
                    </div>
                    <div class="card-body">
                        @csrf
                        <div class="row">

                            <div class="form-group col-6">
                                <label>{!!  __('admin.host', ['default' => 'Host']) !!}</label>
                                <input type="text" value="{{ config('mail.mailers.smtp.host', '') }}"
                                       class="form-control" disabled>
                            </div>

                            <div class="form-group col-3">
                                <label>{!!  __('admin.port', ['default' => 'Port']) !!}</label>
                                <input type="text" value="{{ config('mail.mailers.smtp.port', '') }}"
                                       class="form-control" disabled>
                            </div>

                            <div class="form-group col-3">
                                <label>{!!  __('admin.mailer', ['default' => 'Mailer']) !!}</label>
                                <input type="text" value="{{ config('mail.mailers.smtp.transport', '') }}"
                                       class="form-control" disabled>
                            </div>

                            <div class="form-group col-6">
                                <label>{!!  __('admin.username', ['default' => 'Username']) !!}</label>
                                <input type="text" value="{{ config('mail.mailers.smtp.username', '') }}"
                                       class="form-control" disabled>
                            </div>

                            <div class="form-group col-6">
                                <label>{!!  __('admin.password', ['default' => 'Password']) !!}</label>
                                <input type="password" value="*************" class="form-control" disabled>
                            </div>

                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModal">
                            {!!  __('admin.test_connection', ['default' => 'Test Connection']) !!}
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{!!  __('admin.you_sure', ['default' => 'Are you sure?']) !!}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-left">{!!  __('admin.test_mail_modal', ['default' => 'We will send a test email to']) !!}
                                             {{ auth()->user()->email }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                            {!!  __('admin.close', ['default' => 'Close']) !!}
                                        </button>
                                        <a href="{{ route('emails.test') }}" class="btn btn-primary">
                                            {!!  __('admin.confirm', ['default' => 'Confirm']) !!}
                                        </a>
                    </div>
                </div>
                </div>
            </div>
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
