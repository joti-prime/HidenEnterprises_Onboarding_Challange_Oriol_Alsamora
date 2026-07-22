@extends(AdminTheme::wrapper(), ['title' => __('admin.packages'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.packages') !!}</div>

                <div class="card-body">
                    <a href="{{ route('packages.create') }}" class="btn btn-primary"><i
                            class="fas fa-solid fa-plus"></i>
                        {!! __('admin.create_package', ['default' => 'Create Package']) !!}
                    </a>
                    <hr>
                    @if($packages->count() == 0)
                        @include(AdminTheme::path('empty-state'),
                        ['title' => __('admin.packages_not_found', ['default' => 'We couldn\'t find any packages']),
                        'description' => __('admin.packages_not_found_desc', ['default' => 'You haven\'t created any packages yet.'])])
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{!! __('admin.id') !!}</th>
                                    <th>{!! __('admin.icon') !!}</th>
                                    <th>{!! __('admin.name') !!}</th>
                                    <th>{!! __('admin.category') !!}</th>
                                    <th>{!! __('admin.service') !!}</th>
                                    <th>{!! __('admin.status') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $categories = Categories::all();
                                    $i = 1;
                                @endphp
                                @foreach ($categories as $category)
                                    @if(!$packages->where('category_id', $category->id)->count())
                                        @continue
                                    @endif
                                    <tr>
                                        <td colspan="7" data-toggle="collapse"
                                            data-target=".category-{{ $category->id }}"
                                            class="bg-light text-primary clickable" style="cursor: pointer;">
                                            {{ $category->name }}
                                        </td>
                                    </tr>

                                    @foreach ($packages as $package)
                                        @if($package->category_id == $category->id)
                                            <tr class="expand category-{{ $category->id }} @if($i == 1) show @endif">
                                                <td>{{ $package->order ?? 0 }}</td>
                                                <td>
                                                    <img alt="image"
                                                         src="{{ asset('storage/products/' . $package->icon) }}"
                                                         class="rounded-circle" width="35" data-toggle="tooltip"
                                                         title="" data-original-title="{{ $package->name }}">
                                                </td>
                                                <td>{{ $package->name }}</td>
                                                <td>{{ $category->name }}</td>
                                                <td>{{ $package->service }}</td>
                                                <td><span
                                                        class="badge badge-secondary">{!! __('admin.' . $package->status) !!}</span>
                                                </td>
                                                <td class="text-right">
                                                    <a href="{{ route('admin.change-order', ['id' => $package->id, 'model' => 'packages', 'direction' => 'up']) }}"
                                                       class="btn btn-primary"><i class="fas fa-solid fa-caret-up"></i></a>
                                                    <a href="{{ route('admin.change-order', ['id' => $package->id, 'model' => 'packages', 'direction' => 'down']) }}"
                                                       class="btn btn-primary"><i
                                                            class="fas fa-solid fa-caret-down"></i></a>
                                                    <a href="{{ route('packages.clone', $package->id) }}"
                                                       class="btn btn-primary"><i class="fas fa-clone"></i></a>
                                                    <a href="{{ route('packages.edit', $package->id) }}"
                                                       class="btn btn-primary">{!! __('admin.edit') !!}</a>

                                                    <form action="{{ route('packages.destroy', $package->id) }}"
                                                          method="POST"
                                                          style="display: inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="deleteItem(event)" type="submit"
                                                                class="btn btn-danger">{!! __('admin.delete') !!}</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @php($i++)
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
