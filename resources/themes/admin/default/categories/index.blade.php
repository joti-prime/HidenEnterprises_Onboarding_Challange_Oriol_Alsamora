@extends(AdminTheme::wrapper(), ['title' => 'Categories', 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
@php
    $groups = $categories->whereNull('parent_id')->sortBy('order');
    $byParent = $categories->whereNotNull('parent_id')->groupBy('parent_id');
    $statusBadge = function ($status) {
        return [
            'active'          => '<span class="badge badge-success">Active</span>',
            'unlisted'        => '<span class="badge badge-secondary">Unlisted</span>',
            'restricted'      => '<span class="badge badge-warning">Admin Only</span>',
            'admin_unlisted'  => '<span class="badge badge-warning">Admin + Unlisted</span>',
            'inactive'        => '<span class="badge badge-dark">Inactive</span>',
        ][$status] ?? '<span class="badge badge-light">'.e($status).'</span>';
    };
@endphp

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{!! __('admin.categories') !!}</span>
                <div>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {!! __('admin.create_category') !!}</a>
                </div>
            </div>

            <div class="card-body">
                @if($categories->count() == 0)
                    @include(AdminTheme::path('empty-state'), ['title' => 'We couldn\'t find any categories', 'description' => 'You haven\'t created any categories yet.'])
                @else
                    @foreach($groups as $group)
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $group->name }}</strong>
                                    <span class="ml-2 small">{{ $group->link }}</span>
                                    <span class="ml-2">{!! $statusBadge($group->status) !!}</span>
                                    <span class="ml-2 badge badge-light">{{ ($byParent[$group->id] ?? collect())->count() }} children</span>
                                </div>
                                <div>
                                    <a href="{{ route('admin.change-order', ['id' => $group->id, 'model' => 'categories', 'direction' => 'up']) }}" class="btn btn-light btn-sm" title="Move up"><i class="fas fa-caret-up"></i></a>
                                    <a href="{{ route('admin.change-order', ['id' => $group->id, 'model' => 'categories', 'direction' => 'down']) }}" class="btn btn-light btn-sm" title="Move down"><i class="fas fa-caret-down"></i></a>
                                    <a href="{{ route('categories.edit', $group->id) }}" class="btn btn-light btn-sm">{!! __('admin.edit') !!}</a>
                                    <form action="{{ route('categories.destroy', $group->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="deleteItem(event)" type="submit" class="btn btn-danger btn-sm">{!! __('admin.delete') !!}</button>
                                    </form>
                                </div>
                            </div>
                            @php $children = ($byParent[$group->id] ?? collect())->sortBy('order'); @endphp
                            @if($children->isEmpty())
                                <div class="card-body text-muted text-center small">No children in this group.</div>
                            @else
                                <div class="table-responsive">
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width:60px">{!! __('admin.id') !!}</th>
                                                <th style="width:60px">{!! __('admin.icon') !!}</th>
                                                <th>{!! __('admin.name') !!}</th>
                                                <th>{!! __('admin.link') !!}</th>
                                                <th>Status</th>
                                                <th>{!! __('admin.description') !!}</th>
                                                <th class="text-right">{!! __('admin.actions') !!}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($children as $category)
                                            <tr>
                                                <td>{{ $category->order ?? 0 }}</td>
                                                <td><img alt="image" src="{{ asset('storage/products/' . $category->icon) }}" class="rounded-circle" width="35" title="{{ $category->name }}"></td>
                                                <td>{{ $category->name }}</td>
                                                <td>{{ $category->link }}</td>
                                                <td>{!! $statusBadge($category->status) !!}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($category->description, 80) }}</td>
                                                <td class="text-right">
                                                    <a href="{{ route('admin.change-order', ['id' => $category->id, 'model' => 'categories', 'direction' => 'up']) }}" class="btn btn-primary btn-sm" title="Move up"><i class="fas fa-caret-up"></i></a>
                                                    <a href="{{ route('admin.change-order', ['id' => $category->id, 'model' => 'categories', 'direction' => 'down']) }}" class="btn btn-primary btn-sm" title="Move down"><i class="fas fa-caret-down"></i></a>
                                                    <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-primary btn-sm">{!! __('admin.edit') !!}</a>
                                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display:inline-block">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="deleteItem(event)" type="submit" class="btn btn-danger btn-sm">{!! __('admin.delete') !!}</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
