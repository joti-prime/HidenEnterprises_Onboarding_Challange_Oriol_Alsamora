@extends(AdminTheme::wrapper(), ['title' => __('admin.admin_themes'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{!! __('admin.admin_themes') !!}</h4>
                        <div class="card-header-action">
                            <form>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="{!! __('admin.search') !!}"/>
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body ">
                        <div class="table-responsive">
                            <table class="table table-striped" id="sortable-table">
                                <thead>
                                <tr>
                                    <th class="text-center">
                                        <i class="fas fa-th"></i>
                                    </th>
                                    <th>{!! __('admin.theme') !!}</th>
                                    <th>{!! __('admin.authors', ['default' => 'Author(s)']) !!}</th>
                                    <th>{!! __('admin.version') !!}</th>
                                    <th>{!! __('admin.status') !!}</th>
                                    <th class="text-right">{!! __('admin.actions') !!}</th>
                                </tr>
                                </thead>
                                <tbody class="ui-sortable">
                                @foreach(AdminTheme::list() as $theme)
                                    <tr class="" style="">
                                        <td>
                                            <div class="sort-handler ui-sortable-handle">
                                                <i class="fas fa-th"></i>
                                            </div>
                                        </td>
                                        <td>{{ $theme->name }}</td>
                                        <td>
                                            {{ $theme->author }}
                                        </td>
                                        <td>{{ $theme->version }}</td>


                                        <td>
                                            <div
                                                class="badge @if($theme->name == AdminTheme::active()->name) badge-success @else  badge-danger  @endif">
                                                @if($theme->name == AdminTheme::active()->name)
                                                    {!! __('admin.active') !!}
                                                @else
                                                    {!! __('admin.inactive') !!}
                                                @endif</div>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.theme.files', ['folder' => 'admin/'. $theme->folder]) }}"
                                               class="btn btn-primary">
                                                {!! __('admin.files') !!}
                                            </a>
                                            <a href="{{ route('admin.admin_theme.activate', ['theme' => $theme->name]) }}"
                                               class="btn @if($theme->name == AdminTheme::active()->name) btn-primary disabled
                                       @else  btn-primary  @endif">{!! __('admin.activate') !!}</a>
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

        @includeIf(AdminTheme::path('marketplace.resources-card'))
    </section>
@endsection
