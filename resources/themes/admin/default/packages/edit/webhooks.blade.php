@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Package Webhooks', 'tab' => 'webhooks'])

@section('content')
<div>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary mt-4 mb-4" data-toggle="modal"
            data-target="#createWebhook">
        {{ __('admin.create') }}
    </button>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {!! __('admin.webhooks_desc') !!}
        <a href="https://docs.wemx.net/en/setup/packages#package-webhooks" target="_blank">{!! __('admin.learn_more') !!}</a>
    </div>

    @if ($package->webhooks->count() == 0)
        @include(AdminTheme::path('empty-state'), [
            'title' => 'No webhooks found',
            'description' => 'You haven\'t created any webhooks for this package',
        ])
    @endif

    <!-- Create Webhook Modal -->
    <div class="modal fade bd-example-modal-lg" id="createWebhook" tabindex="-1" role="dialog"
         aria-labelledby="createWebhookLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createWebhookLabel">{{ __('admin.webhook_event') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('packages.webhooks.create', $package->id) }}"
                      method="POST" enctype="multipart/form-data">
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
                            <label for="method">{{ __('admin.method') }}</label>
                            <select class="form-control select2 select2-hidden-accessible"
                                    name="method"
                                    tabindex="-1" aria-hidden="true">

                                @foreach(['get', 'post', 'put', 'patch', 'delete', 'head'] as $key => $method)
                                    <option value="{{ $method }}"
                                            style="text-transform: uppsercase">
                                        {{ strtoupper($method) }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="url">{{ __('admin.url') }}</label>
                            <input type="url" name="url" id="url"
                                   placeholder="https://example.com/api/v1"
                                   class="form-control" required=""/>
                        </div>

                        <div class="mb-4">
                            <label for="data">{{ __('admin.data') }}</label>
                            <textarea class="form-control" name="data" id="data"
                                      placeholder='{"key": "value"}'
                                      style="height: 200px !important"></textarea>
                            <small class="form-text text-muted"></small>
                        </div>

                        <div class="mb-4">
                            <label for="headers">{{ __('admin.headers') }}</label>
                            <textarea class="form-control" name="headers" id="headers"
                                      placeholder='{"Authorization": "Bearer apikey"}'></textarea>
                            <small class="form-text text-muted"></small>
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
                @if($package->webhooks->count() > 0)
                    <tr>
                        <th>{{ __('admin.event') }}</th>
                        <th>{{ __('admin.method') }}</th>
                        <th class="text-right">{{ __('admin.last_updated') }}</th>
                        <th class="text-right">{{ __('admin.action') }}</th>
                    </tr>
                @endif
                @foreach($package->webhooks->all() as $webhook)
                    <tr>
                        <td>{{ $webhook->event }}</td>
                        <td>{{ $webhook->method }}</td>
                        <td class="text-right">{{ $webhook->updated_at->diffForHumans() }}</td>
                        <td class="text-right">
                            <a href="{{ route('packages.webhooks.delete', ['webhook' => $webhook->id]) }}"
                               class="btn btn-icon btn-danger"><i class="fas fa-trash-alt"></i></a>
                            <button data-toggle="modal"
                                    data-target="#editWebhook{{$webhook->id}}"
                                    class="btn btn-primary">{{ __('admin.manage') }}
                            </button>
                        </td>
                    </tr>

                    <!-- Create Email Modal -->
                    <div class="modal fade bd-example-modal-lg" id="editWebhook{{$webhook->id}}"
                         tabindex="-1" role="dialog"
                         aria-labelledby="editWebhook{{$webhook->id}}Label" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"
                                        id="editWebhook{{$webhook->id}}Label">{{ __('admin.webhook_event') }}</h5>
                                    <button type="button" class="close" data-dismiss="modal"
                                            aria-label="{{ __('admin.close') }}">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form
                                    action="{{ route('packages.webhooks.update', ['webhook' => $webhook->id]) }}"
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
                                                        @if($webhook->event == 'creation') selected @endif>
                                                    {{ __('admin.creation') }}
                                                </option>
                                                <option value="renewal"
                                                        @if($webhook->event == 'renewal') selected @endif>
                                                    {{ __('admin.renewal') }}
                                                </option>
                                                <option value="upgrade"
                                                        @if($webhook->event == 'upgrade') selected @endif>
                                                    {{ __('admin.upgrade') }}
                                                </option>
                                                <option value="suspension"
                                                        @if($webhook->event == 'suspension') selected @endif>
                                                    {{ __('admin.suspension') }}
                                                </option>
                                                <option value="unsuspension"
                                                        @if($webhook->event == 'unsuspension') selected @endif>
                                                    {{ __('admin.unsuspension') }}
                                                </option>
                                                <option value="cancellation"
                                                        @if($webhook->event == 'cancellation') selected @endif>
                                                    {{ __('admin.cancellation') }}
                                                </option>
                                                <option value="termination"
                                                        @if($webhook->event == 'termination') selected @endif>
                                                    {{ __('admin.termination') }}
                                                </option>
                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label for="method">{{ __('admin.method') }}</label>
                                            <select
                                                class="form-control select2 select2-hidden-accessible"
                                                name="method"
                                                tabindex="-1" aria-hidden="true">

                                                @foreach(['get', 'post', 'put', 'patch', 'delete', 'head'] as $key => $method)
                                                    <option value="{{ $method }}"
                                                            style="text-transform: uppsercase"
                                                            @if($webhook->method == $method) selected @endif>
                                                        {{ strtoupper($method) }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="mb-4">
                                            <label for="url">{{ __('admin.url') }}</label>
                                            <input type="url" name="url" id="url"
                                                   placeholder="https://example.com/api/v1"
                                                   class="form-control"
                                                   value="{{ $webhook->url }}" required=""/>
                                        </div>

                                        <div class="mb-4">
                                            <label for="data">{{ __('admin.data') }}</label>
                                            <textarea class="form-control" name="data" id="data"
                                                      placeholder='{"key": "value"}'
                                                      style="height: 200px !important">{{ json_encode($webhook->data, JSON_PRETTY_PRINT) }}</textarea>
                                            <small class="form-text text-muted"></small>
                                        </div>

                                        <div class="mb-4">
                                            <label for="headers">{{ __('admin.headers') }}</label>
                                            <textarea class="form-control" name="headers"
                                                      id="headers"
                                                      placeholder='{"Authorization": "Bearer apikey"}'>{{ json_encode($webhook->headers, JSON_PRETTY_PRINT) }}</textarea>
                                            <small class="form-text text-muted"></small>
                                        </div>

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
