@extends(AdminTheme::wrapper(), ['title' => __('admin.emails'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <section>
        <div style="margin-top: -7px;">
            <div class="col-12">
                @includeIf(AdminTheme::path('users.user_nav'))
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>{!! __('admin.emails') !!}</h4>
                    <div class="card-header-action">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#emailModal" class="btn btn-icon icon-left btn-primary"><i class="fas fa-solid fa-plus"></i>
                            {!! __('admin.create') !!}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($user->emails->count() < 0)
                        @includeIf(AdminTheme::path('empty-state'), ['title' => 'No emails found', 'description' => 'This user has no emails in history'])
                    @else

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tbody>
                                <tr>
                                    <th>{!!  __('admin.client', ['default' => 'Client']) !!}</th>
                                    <th>{!!  __('admin.subject', ['default' => 'Subject']) !!}</th>
                                    <th>{!!  __('admin.status', ['default' => 'Status']) !!}</th>
                                    <th>{!!  __('admin.created', ['default' => 'Created at']) !!}</th>
                                    <th class="text-right">{!!  __('admin.actions', ['default' => 'Action']) !!}</th>
                                </tr>
                                @foreach($user->emails()->latest()->paginate(10) as $email)
                                    <tr>
                                        <td>
                                            <a style="display: flex; color: #6c757d"
                                               href="{{ route('users.edit', ['user' => $email->user->id ]) }}">
                                                <img alt="image" src="{{ $email->user->avatar() }}"
                                                     class="rounded-circle mr-2"
                                                     width="35" height="35" data-toggle="tooltip" title=""
                                                     data-original-title="{{ $email->user->first_name }} {{ $email->user->last_name }}">
                                                <div class="flex">
                                                    {{ $email->user->username }} <br>
                                                    <small>{{ $email->user->email }}</small>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            {{ $email->subject }}
                                        </td>
                                        <td>
                                            <div
                                                class="badge @if($email->is_sent) badge-success @else badge-warning @endif">
                                                @if($email->is_sent)
                                                    {!!  __('admin.send', ['default' => 'send']) !!}
                                                @else
                                                    {!!  __('admin.pending', ['default' => 'pending']) !!}
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $email->created_at->diffForHumans() }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('emails.destroy', $email->id) }}" class="btn btn-danger"><i class="fas fa-solid fa-trash"></i></a>
                                            <a href="{{ route('emails.resend', ['email' => $email->id]) }}"
                                               class="btn btn-icon icon-left btn-success"><i
                                                    class="fas fa-check"></i>
                                                {!!  __('admin.resend', ['default' => 'Resend']) !!}
                                            </a>
                                            <button type="button" data-toggle="modal"
                                                    data-target="#previewEmail-{{ $email->id }}"
                                                    class="btn btn-icon icon-left btn-dark">
                                                <i class="fas fa-solid fa-magnifying-glass"></i>
                                                {!!  __('admin.preview', ['default' => 'Preview']) !!}
                                            </button>

                                            <!-- Modal {{ $email->id }}-->
                                            <div class="modal fade" id="previewEmail-{{ $email->id }}" tabindex="-1"
                                                 role="dialog" aria-labelledby="previewEmail-{{ $email->id }}Label"
                                                 aria-hidden="true">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="previewEmail-{{ $email->id }}Label">
                                                                {!!  __('admin.preview_email', ['default' => 'Previewing Email']) !!}
                                                            </h5>
                                                            <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="{{ __('admin.close') }}">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body" style="padding: 0;">
                                                            <iframe srcdoc="{{ (new \App\Mail\ClientEmail($email))->render() }}"
                                                                    style="width: 100%; height: 600px; border: 0; display: block;"></iframe>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">
                                                                {!!  __('admin.close', ['default' => 'Close']) !!}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="card-footer text-right">
                    {{ $user->emails()->latest()->paginate(10)->links(AdminTheme::pagination()) }}
                </div>
            </div>

        </div>
        <style>
        body {
            background-color: var(--primary-bg) !important;
        }
        </style>
    </section>

<!-- Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" role="dialog" aria-labelledby="emailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="emailModalLabel">{{ __('admin.email') }} {{ $user->email }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="{{ route('emails.send') }}" method="POST">
            @csrf
            <div class="modal-body">

                <input type="hidden" name="email" value="{{ $user->email }}">

                <div class="mb-4">
                    <label for="subject">{{ __('admin.subject') }}</label>
                    <input type="text" name="subject" id="subject" placeholder="{{ __('admin.subject') }}"
                           class="form-control" required=""/>
                </div>

                <div class="mb-4">
                    <label for="body">{{ __('admin.email_body') }}</label>
                    <textarea class="form-control" name="body" id="body"></textarea>
                    <small class="form-text text-muted"></small>
                </div>

            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.close') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('admin.send') }}</button>
            </div>
        </form>
      </div>
    </div>
  </div>
@endsection
