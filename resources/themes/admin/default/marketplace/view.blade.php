@extends(AdminTheme::wrapper(), ['title' => __('admin.marketplace', ['default' => 'Marketplace']), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
<div class="row">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-body">
                <li class="media">
                    <img class="mr-3 mb-4" style="with: 64px; height: 64px; border-radius: 10px;" src="{{ $resource['icon'] }}" alt="Resource Logo" />
                    <div class="media-body">
                        <h5 class="mt-0 mb-1">{{ $resource['name'] }}</h5>
                        <p>{{ $resource['short_desc'] }}</p>
                    </div>
                </li>
                <ul class="nav nav-tabs" id="myTab2" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="resource-tab2" data-toggle="tab" href="#resource" role="tab" aria-controls="resource" aria-selected="true">{{ __('admin.resource') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="releases-tab2" data-toggle="tab" href="#releases" role="tab" aria-controls="releases" aria-selected="false">{{ __('admin.releases') }}</a>
                    </li>
                </ul>
                <div class="tab-content tab-bordered" id="myTab3Content">
                    <div class="tab-pane fade show active" id="resource" role="tabpanel" aria-labelledby="resource-tab2">
                        {!! $resource['description'] !!}
                    </div>
                    <div class="tab-pane fade" id="releases" role="tabpanel" aria-labelledby="releases-tab2">
                        <div class="table-responsive">
                            <table class="table table-striped table-md">
                                <tbody>
                                    <tr>
                                        <th>{{ __('admin.version') }}</th>
                                        <th>{{ __('admin.wemx_versions') }}</th>
                                        <th>{{ __('admin.created_at') }}</th>
                                        <th class="text-right">{{ __('admin.action') }}</th>
                                    </tr>
                                    @foreach($resource['versions'] as $version)
                                    <tr>
                                        <td>{{ $version['version'] }}</td>
                                        <td>
                                            @foreach($version['wemx_version'] as $wemx_version)
                                                {{ $wemx_version }}
                                            @endforeach
                                        </td>
                                        <td>2017-01-09</td>
                                        <td class="text-right"><a href="#" class="btn btn-primary">{{ __('admin.download') }}</a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card profile-widget" style="margin-top: 0;">
            <div class="profile-widget-header">
                <div class="profile-widget-items">
                    <div class="profile-widget-item">
                        <div class="profile-widget-item-label">{{ __('admin.price') }}</div>
                        <div class="profile-widget-item-value">{{ (!$resource['is_free']) ? $resource['price'] : 'FREE' }}</div>
                    </div>
                    <div class="profile-widget-item">
                        <div class="profile-widget-item-label">{{ __('admin.downloads') }}</div>
                        <div class="profile-widget-item-value">{{ $resource['download_count'] }}</div>
                    </div>
                    <div class="profile-widget-item">
                        <div class="profile-widget-item-label">{{ __('admin.views') }}</div>
                        <div class="profile-widget-item-value">{{ $resource['views_count'] }}</div>
                    </div>
                    <div class="profile-widget-item">
                        <div class="profile-widget-item-label">{{ __('admin.rating') }}</div>
                        <div class="profile-widget-item-value" style="display: flex;align-items: center;justify-content: center;">
                            {{ $resource['rating'] }} <i class="fas fa-star ml-1 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="profile-widget-description">
                <p>
                    {{ $resource['short_desc'] }}
                </p>
                <div>
                    @if($resource['purchased'])
                        <a href="{{ route('admin.resource.install', ['resource_id' => $resource['id'], 'version_id' => $resource['version_id']]) }}"
                           class="btn btn-primary">
                            {!! __('admin.install') !!}
                        </a>
                    @else
                        <a href="{{ $resource['view_url'] }}" target="_blank"
                           class="btn btn-primary">
                            {!! __('admin.purchased') !!}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-widget .profile-widget-items:after {
        left: 0px;
    }
</style>
@endsection
