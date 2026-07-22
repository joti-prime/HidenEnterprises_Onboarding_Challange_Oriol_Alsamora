@extends(AdminTheme::wrapper(), ['title' => __('admin.pterodactyl')])
@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.css')) }}"/>
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">
@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/summernote/summernote-bs4.js')) }}"></script>
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection
@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{!! __('admin.pterodactyl') !!}</h4>
                    <div class="card-header-action">
                        <button href="#" class="btn btn-icon icon-left btn-primary" data-toggle="modal"
                                data-target="#addModal">
                            <i class="fas fa-solid fa-plus"></i>
                            {{ __('admin.create') }}
                        </button>
                    </div>

                </div>


                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>{{ __('admin.id') }}</th>
                                <th>{{ __('admin.package') }}</th>
                                <th>{{ __('admin.type') }}</th>
                                <th class="text-right">{{ __('admin.actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->package->name }}</td>
                                    <td>{{ cfHelper()::getTypeData($item->type)['name'] }}</td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-icon btn-primary" data-toggle="modal"
                                           data-target="#editModal{{ $item->id }}"
                                           data-original-title="{{ __('admin.edit') }}">
                                            <i class="fas fa-solid fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.cf.pterodactyl.destroy', $item->id) }}"
                                           onclick="return confirm('{{ __('admin.you_sure') }}');"
                                           class="btn btn-icon btn-danger" data-toggle="tooltip"
                                           data-original-title="{{ __('admin.delete') }}">
                                            <i class="fas fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1" role="dialog"
                                     aria-labelledby="editModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.cf.pterodactyl.update', $item->id) }}"
                                                  method="post">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $item->id }}">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"
                                                        id="editModalLabel{{ $item->id }}">{{ __('admin.update') }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                        <span aria-hidden="true">×</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="form-row">
                                                        <div class="form-group col-12">
                                                            <label for="type"
                                                                   class="col-form-label">{{ __('admin.type') }}</label>
                                                            <select id="type" class="form-control" name="type">
                                                                @foreach(cfHelper()::getTypeData('all') as $key => $type)
                                                                    <option @if($item->type == $key) selected @endif
                                                                    value="{{ $key }}">{{ $type['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="zones"
                                                               class="col-form-label">{{ __('admin.domain') }}</label>
                                                        <select id="zones"
                                                                class="form-control select2 select2-hidden-accessible"
                                                                name="zones_ids[]"
                                                                multiple>
                                                            @foreach($domains as $key => $domain)
                                                                <option @if(is_array($item->zones_ids) && in_array($key, $item->zones_ids)) selected
                                                                        @endif value="{{ $key }}">{{ $domain }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn"
                                                            data-dismiss="modal">{{ __('admin.close') }}</button>
                                                    <button type="submit"
                                                            class="btn btn-primary">{{ __('admin.update') }}</button>
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
                <div class="card-footer text-right">

                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.cf.pterodactyl.store') }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">{{ __('admin.create') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="package_id" class="col-form-label">{{ __('admin.package') }}</label>
                                <select id="package_id" class="form-control" name="package_id">
                                    @foreach($packages as $package)
                                        @if($items->where('package_id', $package->id)->count())
                                            @continue
                                        @endif
                                    @if($package->service != 'pterodactyl') @continue @endif
                                        <option value="{{ $package->id }}">{{ $package->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="type" class="col-form-label">{{ __('admin.type') }}</label>
                                <select id="type" class="form-control" name="type">
                                    @foreach(cfHelper()::getTypeData('all') as $key => $type)
                                        <option value="{{ $key }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="zones" class="col-form-label">{{ __('admin.domain') }}</label>
                            <select id="zones" class="form-control select2 select2-hidden-accessible" name="zones_ids[]"
                                    multiple>
                                @foreach($domains as $key => $domain)
                                    <option value="{{ $key }}">{{ $domain }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn " data-dismiss="modal">{{ __('admin.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
