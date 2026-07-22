@extends(AdminTheme::wrapper(), ['title' => __('admin.files'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
<section class="section">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>{!! __('admin.editing_theme_files', ['default' => 'Editing theme Files']) !!}</h4>
                <div class="card-header-action">
                    <form>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="{{ __('admin.search') }}" />
                            <div class="input-group-btn">
                                <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body ">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.themes') }}">{!! __('admin.themes') !!}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $folder }}</li>
                  </ol>
                <div class="table-responsive">
                    <table class="table table-striped" id="sortable-table">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <i class="fas fa-th" style="font-size: 20px"></i>
                                </th>
                                <th>{!! __('admin.files') !!}</th>
                                <th class="text-right">{!! __('admin.actions') !!}</th>
                            </tr>
                        </thead>
                        <tbody class="ui-sortable">
                            @foreach($directories as $dir)
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <i class="fas fa-solid fa-folder" style="font-size: 20px"></i>
                                    </div>
                                </td>
                                <td>{{ File::basename($dir, '/') }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.theme.files', ['folder' => $folder . '/' .File::basename($dir, '/')]) }}"
                                       class="btn btn-icon icon-left btn-dark"><i class="far fa-file"></i>
                                        {!! __('admin.view_files', ['default' => 'View Files']) !!}
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @foreach($files as $file)
                            <tr>
                                <td>
                                    <div class="text-center">
                                        <i class="fas fa-solid fa-file" style="font-size: 20px"></i>
                                    </div>
                                </td>
                                <td>{{ File::basename($file) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('admin.theme.files.edit') }}?file={{ $folder . '/' . File::basename($file) }}"
                                       class="btn btn-icon icon-left btn-dark"><i class="far fa-edit"></i>
                                        {!! __('admin.edit') !!}
                                    </a>
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
</section>
@endsection
