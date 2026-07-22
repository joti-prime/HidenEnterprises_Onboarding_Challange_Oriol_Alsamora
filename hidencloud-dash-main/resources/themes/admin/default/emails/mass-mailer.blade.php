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
                <div class="card-header">
                    <h4>Mass Mailer</h4>
                    <div class="card-header-action">
                        <a href="{{ route('emails.mass-mailer.create') }}" class="btn btn-primary" class="btn btn-icon icon-left btn-primary"><i class="fas fa-solid fa-plus"></i>
                            {!! __('admin.create') !!}
                        </a>
                    </div>
                </div>
                <form action="#" method="POST">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                <tr>
                                    <th>Audience</th>
                                    <th>Subject</th>
                                    <th>{!!  __('admin.status', ['default' => 'Status']) !!}</th>
                                    <th>Sent / Pending Emails</th>
                                    <th>Repeat</th>
                                    <th>Scheduled at</th>
                                    <th>Last completed at</th>
                                    <th>{!!  __('admin.created', ['default' => 'Created at']) !!}</th>
                                    <th class="text-right">{!!  __('admin.actions', ['default' => 'Action']) !!}</th>
                                </tr>
                                @foreach($massMail as $email)
                                    <tr>
                                        <td>
                                            {{ $email->audience }}
                                        </td>
                                        <td>
                                            {{ $email->subject }}
                                        </td>
                                        <td>
                                            <div
                                                class="badge @if(in_array($email->status, ['completed', 'processing'])) badge-success @elseif(in_array($email->status, ['failed'])) badge-danger @else badge-warning @endif">
                                                {{ $email->status }}
                                            </div>
                                        </td>
                                        <td>{{ $email->sent_count }} / {{ $email->audience()->count() }}</td>
                                        <td>@if($email->repeat) every {{ $email->repeat }} days @else never @endif</td>
                                        <td>@if($email->scheduled_at) {{ $email->scheduled_at->translatedFormat(settings('date_format', 'd M Y')) }} {{ $email->scheduled_at->diffForHumans() }} @else never @endif</td>
                                        <td>@if($email->last_completed_at) {{ $email->last_completed_at->translatedFormat(settings('date_format', 'd M Y')) }} {{ $email->last_completed_at->diffForHumans() }} @else pending @endif</td>
                                        <td>{{ $email->created_at->translatedFormat(settings('date_format', 'd M Y')) }} {{ $email->created_at->diffForHumans() }}</td>
                                        <td  class="text-right">
                                            <a href="{{ route('emails.mass-mailer.destroy', $email->id) }}" class="btn btn-danger"><i class="fas fa-solid fa-trash"></i></a>
                                            <a href="{{ route('emails.mass-mailer.edit', $email->id) }}" class="btn btn-primary"><i class="fa-solid fa-pen-to-square"></i></a>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        {{ $massMail->links(AdminTheme::pagination()) }}
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        span.select2.select2-container.select2-container--default {
            width: 100% !important;
        }
        body {
            background-color: var(--primary-bg) !important;
        }
    </style>
@endsection
