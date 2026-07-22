@extends(AdminTheme::wrapper(), ['title' =>  __('admin.pages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!!  __('admin.pages') !!}</div>

                <div class="card-body">
                    <a href="{{ route('pages.translation.edit', $id) }}"
                       class="btn btn-primary">{{ __('admin.create_translation') }}</a>
                    <hr>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.id') !!}</th>
                                <th>{!! __('admin.name') !!}</th>
                                <th>{!! __('admin.locations') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($translations as $translation)
                                <tr>
                                    <td>{{ $translation->id }}</td>
                                    <td>{{ $translation->title }}</td>
                                    <td>{{ $translation->locale }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('pages.translation.edit', ['id' => $id, 'locale' => $translation->locale]) }}"
                                           class="btn btn-primary">
                                            <i class="fas fa-edit" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="{{ __('admin.edit') }}"></i>
                                        </a>

                                        <form
                                            action="{{ route('pages.translation.destroy', ['translation' => $translation]) }}"
                                            method="POST"
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
