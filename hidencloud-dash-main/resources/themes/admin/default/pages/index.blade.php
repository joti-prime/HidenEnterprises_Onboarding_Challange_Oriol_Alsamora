@extends(AdminTheme::wrapper(), ['title' =>  __('admin.pages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!!  __('admin.pages') !!}</div>

                <div class="card-body">
                    <a href="{{ route('pages.create') }}" class="btn btn-primary">{!! __('admin.create_page') !!}</a>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.id') !!}</th>
                                <th>{!! __('admin.name') !!}</th>
                                <th>{!! __('admin.url', ['default' => 'URL']) !!}</th>
                                <th>{!! __('admin.status') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($pages as $page)
                                <tr>
                                    <td>{{ $page->id }}</td>
                                    <td>{{ $page->title }}</td>
                                    <td>
                                        <a href="{{ route('page', $page->path) }}" target="_blank"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           title="{{ route('page', $page->path) }}">{{__('admin.link')}}</a>
                                    </td>
                                    <td>@if($page->is_enabled)
                                            <i class="fas fa-solid fa-circle text-success "
                                               style="font-size: 11px;"></i> {!! __('admin.active') !!}
                                        @else
                                            <i class="fas fa-solid fa-circle text-danger "
                                               style="font-size: 11px;"></i> {!! __('admin.inactive') !!}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('pages.translation', $page->id) }}"
                                           class="btn btn-primary">
                                            <i class="fas fa-language" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="{{ __('admin.translations') }}"></i>
                                        </a>
                                        <a href="{{ route('pages.edit', $page->id) }}"
                                           class="btn btn-primary">
                                            <i class="fas fa-edit" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="{{ __('admin.edit') }}"></i>
                                        </a>

                                        <form action="{{ route('pages.destroy', $page->id) }}" method="POST"
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="deleteItem(event)" type="submit"
                                                    class="btn btn-danger">
                                                <i class="fas fa-trash-alt"
                                                   data-bs-toggle="tooltip"
                                                   data-bs-placement="top"
                                                   title="{{ __('admin.delete') }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
