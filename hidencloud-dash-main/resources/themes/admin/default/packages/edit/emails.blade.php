@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Package Emails', 'tab' => 'emails'])

@section('content')
<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary mt-4 mb-4" data-toggle="modal"
            data-target="#createEmail">
        {{ __('admin.create') }}
    </button>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {!! __('admin.edit_email_alert') !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="{{ __('admin.close') }}">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    @if (PackageEmail::where('package_id', $package->id)->count() == 0)
        @include(AdminTheme::path('empty-state'), [
            'title' => 'No emails found',
            'description' => 'You haven\'t created any emails for this package',
        ])
    @endif

    <!-- Create Email Modal -->
    <div class="modal fade bd-example-modal-lg" id="createEmail" tabindex="-1" role="dialog"
         aria-labelledby="createEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEmailLabel">{{ __('admin.email_event') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('packages.emails.create', $package->id) }}" method="POST"
                      enctype="multipart/form-data">
                    <div class="modal-body">
                        @csrf
                        <div class="mb-4">
                            <label for="event">{{ __('admin.event') }}</label>
                            <select class="form-control select2 select2-hidden-accessible"
                                    name="event"
                                    tabindex="-1" aria-hidden="true">
                                <option value="creation">
                                    {{ __('admin.creation') }}
                                </option>
                                <option value="renewal">
                                    {{ __('admin.renewal') }}
                                </option>
                                <option value="upgrade">
                                    {{ __('admin.upgrade') }}
                                </option>
                                <option value="suspension">
                                    {{ __('admin.suspension') }}
                                </option>
                                <option value="unsuspension">
                                    {{ __('admin.unsuspension') }}
                                </option>
                                <option value="cancellation">
                                    {{ __('admin.cancellation') }}
                                </option>
                                <option value="termination">
                                    {{ __('admin.termination') }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="title">{{ __('admin.email_title') }}</label>
                            <input type="text" name="title" id="title" placeholder="{{ __('admin.subject') }}"
                                   class="form-control" required=""/>
                        </div>

                        <div class="">
                            <label for="body">{{ __('admin.email_body') }}</label>
                            <textarea class="form-control" name="body" id="body"></textarea>
                            <small class="form-text text-muted"></small>
                        </div>

                        <div class="form-group" style="display: flex;flex-direction: column;">
                            <label for="myfile">{{ __('admin.select_a_file_optional') }}</label>
                            <input class="" type="file" id="myfile" name="attachment">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            {{ __('admin.close') }}
                        </button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-md">
                <tbody>
                @if($package->emails->count() > 0)
                    <tr>
                        <th>{{ __('admin.event') }}</th>
                        <th>{{ __('admin.title') }}</th>
                        <th class="text-right">{{ __('admin.last_updated') }}</th>
                        <th class="text-right">{{ __('admin.action') }}</th>
                    </tr>
                @endif
                @foreach($package->emails->all() as $email)
                    <tr>
                        <td>{{ $email->event }}</td>
                        <td>{{ $email->title }}</td>
                        <td class="text-right">{{ $email->updated_at->diffForHumans() }}</td>
                        <td class="text-right">
                            <a href="{{ route('packages.emails.delete', ['email' => $email->id]) }}"
                               class="btn btn-icon btn-danger"><i class="fas fa-trash-alt"></i></a>
                            <button data-toggle="modal" data-target="#editEmail{{$email->id}}"
                                    class="btn btn-primary">{{ __('admin.manage') }}
                            </button>
                        </td>
                    </tr>

                    <!-- Create Email Modal -->
                    <div class="modal fade bd-example-modal-lg" id="editEmail{{$email->id}}"
                         tabindex="-1" role="dialog"
                         aria-labelledby="editEmail{{$email->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editEmail{{$email->id}}Label">
                                        {{ __('admin.email_event') }}</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-label="{{ __('admin.close') }}">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form
                                    action="{{ route('packages.emails.update', ['email' => $email->id]) }}"
                                    method="POST" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="event">{{ __('admin.event') }}</label>
                                            <select
                                                class="form-control select2 select2-hidden-accessible"
                                                name="event"
                                                tabindex="-1" aria-hidden="true">
                                                <option value="creation"
                                                        @if($email->event == 'creation') selected @endif>
                                                    {{ __('admin.creation') }}
                                                </option>
                                                <option value="renewal"
                                                        @if($email->event == 'renewal') selected @endif>
                                                    {{ __('admin.renewal') }}
                                                </option>
                                                <option value="upgrade"
                                                        @if($email->event == 'upgrade') selected @endif>
                                                    {{ __('admin.upgrade') }}
                                                </option>
                                                <option value="suspension"
                                                        @if($email->event == 'suspension') selected @endif>
                                                    {{ __('admin.suspension') }}
                                                </option>
                                                <option value="unsuspension"
                                                        @if($email->event == 'unsuspension') selected @endif>
                                                    {{ __('admin.unsuspension') }}
                                                </option>
                                                <option value="cancellation"
                                                        @if($email->event == 'cancellation') selected @endif>
                                                    {{ __('admin.cancellation') }}
                                                </option>
                                                <option value="termination"
                                                        @if($email->event == 'termination') selected @endif>
                                                    {{ __('admin.termination') }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label for="title">{{ __('admin.email_title') }}</label>
                                            <input type="text" name="title" id="title"
                                                   value="{{ $email->title }}"
                                                   placeholder="{{ __('admin.subject') }}"
                                                   class="form-control" required=""/>
                                        </div>

                                        <div class="">
                                            <label for="body">{{ __('admin.email_body') }}</label>
                                            <textarea class="form-control" name="body" id="body">{!! $email->body !!}</textarea>
                                            <small class="form-text text-muted"></small>
                                        </div>

                                        <div class="form-group"
                                             style="display: flex;flex-direction: column;">
                                            <label for="myfile">{{ __('admin.select_a_file_optional') }}</label>
                                            <input class="" type="file" id="myfile"
                                                   name="attachment">
                                        </div>

                                        @if($email->attachment)
                                            <span
                                                class="badge badge-pill badge-secondary">{{ basename($email->attachment) }}</span>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">{{ __('admin.close') }}
                                        </button>
                                        <button type="submit" class="btn btn-primary">{{ __('admin.update') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
