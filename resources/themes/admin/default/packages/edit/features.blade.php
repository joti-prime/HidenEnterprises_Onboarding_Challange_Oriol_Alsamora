@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Package Features', 'tab' => 'features'])

@section('content')
    <div>
        <form action="{{ route('package.create-feature', $package->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-md-3 col-6" data-toggle="modal" data-target="#IconModal">
                    <label for="name">{{ __('admin.icon') }}</label>
                    <input type="text" name="icon" id="feature-icon" placeholder=""
                           class="form-control" value="" required=""/>
                </div>
                <div class="form-group col-md-3 col-6">
                    <label for="icon">{{ __('admin.color') }}</label>
                    <select class="form-control select2 select2-hidden-accessible"
                            name="color" id="color" tabindex="-1" aria-hidden="true">
                        @foreach (config('utils.colors') as $key => $color)
                            <option value="{{ $color }}">{{ $color }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6 col-12">
                    <label for="description">{{ __('admin.description') }}</label>
                    <input type="text" name="description" id="description" placeholder=""
                           class="form-control" value="" required=""/>
                </div>
            </div>
            <div class="text-right">
                <button class="btn btn-primary" type="submit">{{ __('admin.add_feature') }}</button>
            </div>
        </form>
        <!-- Modal -->
        <div class="modal fade" id="IconModal" tabindex="-1" role="dialog"
             aria-labelledby="IconModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="IconModalLabel">{{ __('admin.select_icon') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            @foreach(config('utils.icons') as $icon)
                                <div class="col-1 mb-4">
                                    <div class="bx-md d-flex justify-content-center"
                                         style="cursor: pointer;" onclick='setIcon("{{ $icon }}")'>
                                        {!! $icon !!}
                                    </div>
                                </div>
                            @endforeach
                            <div class="form-group col-md-12 col-12">
                                <label for="description">{{ __('admin.icon_font') }}</label>
                                <input type="text" name="description" id="custom-icon"
                                       value="<i class='bx bxs-check-shield' ></i>"
                                       class="form-control" value="" required=""/>
                                <small>{!! __('admin.custom_icons_on_boxicons_choose_icon') !!}</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('admin.close') }}</button>
                        <button type="button" onclick="setFeatureIcon()" class="btn btn-primary"
                                data-dismiss="modal">{{ __('admin.use_icon') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped" id="sortable-table">
                    <thead>
                    <tr>
                        <th>{{ __('admin.icon') }}</th>
                        <th>{{ __('admin.feature') }}</th>
                        <th>{{ __('admin.order_id') }}</th>
                        <th class="text-right">{{ __('admin.action') }}</th>
                    </tr>
                    </thead>
                    <tbody class="ui-sortable">
                    @foreach($package->features()->orderBy('order', 'desc')->get() as $feature)
                        <tr>
                            <td><span class='bx-sm text-primary'>{!! $feature->icon !!}</span></td>
                            <td class="align-middle">
                                {{ $feature->description }}
                            </td>
                            <td class="align-middle">
                                {{ $feature->order }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('package.move-feature', ['package' => $package->id, 'feature' => $feature->id, 'direction' => 'up']) }}"
                                   class="btn btn-primary"><i class="fas fa-solid fa-caret-up"></i></a>
                                <a href="{{ route('package.move-feature', ['package' => $package->id, 'feature' => $feature->id, 'direction' => 'down']) }}"
                                   class="btn btn-primary"><i
                                        class="fas fa-solid fa-caret-down"></i></a>
                                <button class="btn btn-primary" data-toggle="modal"
                                        data-target="#EditModal{{ $feature->id }}">
                                    <i class="fas fa-solid fa-edit"></i>
                                </button>
                                <a href="{{ route('package.destroy-feature', ['package' => $package->id, 'feature' => $feature->id]) }}"
                                   class="btn btn-danger"><i class="fas fa-solid fa-trash"></i></a>
                            </td>
                        </tr>

                        <div class="modal fade" id="EditModal{{ $feature->id }}" tabindex="-1" role="dialog"
                             aria-labelledby="EditModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="{{ __('admin.close') }}">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('package.feature-update', ['feature' => $feature->id, 'package' => $package->id]) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="">
                                                <div class="form-group">
                                                    <label for="feature-icon">{{ __('admin.icon') }}</label>
                                                    <input type="text" name="icon" id="feature-icon" placeholder=""
                                                           class="form-control" value="{{ $feature->icon }}"
                                                           required=""/>
                                                </div>
                                                <div class="form-group">
                                                    <label for="color">{{ __('admin.color') }}</label>
                                                    <select class="form-control select2 select2-hidden-accessible"
                                                            name="color" id="color" tabindex="-1"
                                                            aria-hidden="true">
                                                        @foreach (config('utils.colors') as $key => $color)
                                                            <option value="{{ $color }}">{{ $color }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="description">{{ __('admin.description') }}</label>
                                                    <input type="text" name="description" id="description"
                                                           placeholder=""
                                                           class="form-control" value="{{ $feature->description }}"
                                                           required=""/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">{{ __('admin.close') }}</button>
                                            <button class="btn btn-primary"
                                                    type="submit">{{ __('admin.update') }}</button>

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

    <script>
        function setIcon(icon) {
            document.getElementById("custom-icon").value = icon;
        }

        function setFeatureIcon() {
            document.getElementById("feature-icon").value = document.getElementById("custom-icon").value;
        }
    </script>
@endsection
