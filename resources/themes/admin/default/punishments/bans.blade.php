@extends(AdminTheme::wrapper(), ['title' => 'Bans', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}" />
<link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
<script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
<script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <section class="section">
        <div class="col-12">
            <div class="">
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.punishments') !!} - {{ __('admin.bans') }}</h4>
                        <div class="card-header-action">

                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($punishments->count() == 0)
                            @includeIf(AdminTheme::path('empty-state'), ['title' => __('admin.no_bans_found'), 'description' => __('admin.no_bans_found_desc')])
                        @else
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                <tr>
                                    <th>{!! __('admin.id') !!}</th>
                                    <th>{!! __('admin.user') !!}</th>
                                    <th>{!! __('admin.staff') !!}</th>
                                    <th>{!! __('admin.type') !!}</th>
                                    <th>{!! __('admin.reason') !!}</th>
                                    <th>{!! __('admin.create_at') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>

                                @foreach($punishments as $punishment)
                                    <tr>
                                        <td>{{ $punishment->id }}</td>
                                        <td>
                                            <a href="{{ route('users.edit', ['user' => $punishment->user->id]) }}"
                                               style="display: flex; color: #6c757d">
                                                <img alt="{!! __('image') !!}" src="{{ $punishment->user->avatar() }}"
                                                     class="rounded-circle mr-1 mt-1" width="32px" height="32px"
                                                     data-toggle="tooltip" title=""
                                                     data-original-title="{{ $punishment->user->first_name }} {{ $punishment->user->last_name }}">
                                                <div class="flex">
                                                    {{ $punishment->user->username }} <br>
                                                    <small>{{ $punishment->user->email }}</small>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            @isset($punishment->staff)
                                            <a href="{{ route('users.edit', ['user' => $punishment->staff->id]) }}"
                                               style="display: flex; color: #6c757d">
                                                <img alt="{!! __('image') !!}" src="{{ $punishment->staff->avatar() }}"
                                                     class="rounded-circle mr-1 mt-1" width="32px" height="32px"
                                                     data-toggle="tooltip" title=""
                                                     data-original-title="{{ $punishment->staff->first_name }} {{ $punishment->staff->last_name }}">
                                                <div class="flex">
                                                    {{ $punishment->staff->username }} <br>
                                                    <small>{{ $punishment->staff->email }}</small>
                                                </div>
                                            </a>
                                            @endisset
                                        </td>
                                        <td>
                                            <div class="flex align-items-center">
                                                <i class="fas fa-solid fa-circle
                                                @if(in_array($punishment->type, ['ban', 'ipban'])) text-danger @else text-warning @endif"
                                                   style="font-size: 11px;"></i> {{ ucfirst($punishment->type) }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ $punishment->reason }}
                                        </td>
                                        <td>
                                            {!! __('admin.created') !!}: {{ $punishment->created_at->translatedFormat(settings('date_format', 'd M Y')) }}
                                            <br>
                                            {!! __('admin.expires_in') !!}: @isset($punishment->expires_at) {{ $punishment->expires_at->diffForHumans() }} @else {{ __('admin.never') }} @endisset
                                            <br>
                                        </td>
                                        <td class="text-right">
                                            @if(in_array($punishment->type, ['ban', 'ipban']))
                                                <button type="button" class="btn btn-warning" onclick="document.getElementById('unban-popup-{{ $punishment->id }}').style.display='flex';">
                                                    {{ __('admin.unban') }}
                                                </button>
                                                @include(AdminTheme::path('punishments._unban_modal'), ['punishment' => $punishment])
                                            @endif
                                                <button type="button" class="btn btn-danger" onclick="document.getElementById('delete-popup-{{ $punishment->id }}').style.display='flex';">
                                                    <i class="fas fa-solid fa-trash"></i>
                                                </button>
                                                @include(AdminTheme::path('punishments._delete_modal'), ['punishment' => $punishment])
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer text-right">
                      {{ $punishments->links(AdminTheme::pagination()) }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
