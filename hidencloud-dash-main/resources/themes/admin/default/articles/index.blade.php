@extends(AdminTheme::wrapper(), ['title' =>  'News', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('admin.news') }}</div>

                <div class="card-body">
                    <a href="{{ route('articles.create') }}"
                       class="btn btn-primary">{{ __('admin.create_article') }}</a>
                    <hr>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{!! __('admin.id') !!}</th>
                                <th>{!! __('admin.name') !!}</th>
                                <th>{!! __('admin.url', ['default' => 'URL']) !!}</th>
                                <th>{!! __('admin.status') !!}</th>
                                <th>{!! __('admin.views') !!}</th>
                                <th>{!! __('admin.comments') !!}</th>
                                <th>{!! __('admin.likes') !!}</th>
                                <th>{!! __('admin.dislikes') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($articles as $article)
                                <tr>
                                    <td>{{ $article->id }}</td>
                                    <td>{{ $article->title }}</td>
                                    <td><a href="{{ route('news.article', $article->path) }}" target="_blank"
                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                           title="{{ route('news.article', $article->path) }}">{{__('admin.link')}}</a>
                                    </td>
                                    <td>@if(true)
                                            <i class="fas fa-solid fa-circle text-success "
                                               style="font-size: 11px;"></i> {!! __('admin.active') !!}
                                        @else
                                            <i class="fas fa-solid fa-circle text-danger "
                                               style="font-size: 11px;"></i> {!! __('admin.inactive') !!}
                                        @endif
                                    </td>
                                    <td>{{ $article->views }}</td>
                                    <td>{{ $article->comments()->count() }}</td>
                                    <td>{{ $article->likes }}</td>
                                    <td>{{ $article->dislikes }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('articles.translation', $article->id) }}"
                                           class="btn btn-primary"><i class="fas fa-language" data-bs-toggle="tooltip"
                                                                      data-bs-placement="top"
                                                                      title="{{ __('admin.translations') }}"></i></a>
                                        <a href="{{ route('articles.edit', $article->id) }}"
                                           class="btn btn-primary"><i class="fas fa-edit" data-bs-toggle="tooltip"
                                                                      data-bs-placement="top"
                                                                      title="{{ __('admin.edit') }}"></i></a>

                                        <form action="{{ route('articles.destroy', $article->id) }}" method="POST"
                                              style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button onclick="deleteItem(event)" type="submit"
                                                    class="btn btn-danger"><i class="fas fa-trash-alt"
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
