@extends(AdminTheme::wrapper(), ['title' => 'Updates', 'keywords' => 'HCTestDash Updates, HCTestDash Panel'])

@section('container')
<div class="card mb-6">
    @if(Cache::has('app_updating'))
    <div class="card-body">
      <div class="empty-state" data-height="400" style="height: 400px;">
          <div class="empty-state-icon" style="background-color: #059669; display: flex; justify-content: center; align-items: center">
            <img src="/assets/src/spinners/blocks.svg" style="width: 50px;">
          </div>
          <h2>{{ __('admin.installing') }} {{ Cache::get('app_updating')['type'] }} {{ Cache::get('app_updating')['version'] }}</h2>
          <p class="lead">
            <span id="progress">{{ Cache::get('app_updating')['progress'] }}</span><span id="loading">...</span>
          </p>
          <a href="https://wemx.net/news" target="_blank" class="mt-4 bb">{{ __('admin.update_manually') }}</a>
        </div>
    </div>
    <script>
      let count = 0;
      const loadingElement = document.getElementById('loading');

      setInterval(() => {
          count++;
          loadingElement.textContent = '.'.repeat(count % 4);
      }, 500);

      const progressElement = document.getElementById('progress');
      setInterval(async () => {
        try {
            let response = await fetch('{{ route("updates.progress") }}');

            // Check if the response is successful
            if (!response.ok) {
                throw new Error(`Failed to load update progress, please wait 2 min or perform a manual update.`);
            }

            let data = await response.json();
            if(data.updating) {
              progressElement.innerHTML = data.progress;
            }
        } catch (error) {
            progressElement.innerHTML = error;
            console.error("There was an error fetching the data:", error);
        }
    }, 1500);
  </script>
    @else
    @if(config('app.version') >= $latest_version->version)
    <div class="card-body">
        <div class="empty-state" data-height="400" style="height: 400px;">
            <div class="empty-state-icon" style="background-color: #059669;">
              <i class="fas fa-check"></i>
            </div>
            <h2>{{ __('admin.running_latest_version') }} ({{config('app.version')}})</h2>
            <p class="lead">
              {{ __('admin.running_latest_version_desc') }}
            </p>
            <a href="https://wemx.net/news" target="_blank" class="btn btn-success mt-4">{{ __('client.latest_news') }}</a>
            @if(config('app.version') == 'dev')
              <a href="{{ route('updates.install', ['version' => 'latest', 'type' => 'dev']) }}" class="btn btn-success mt-4">{{ __('admin.install_dev_version') }}</a>
            @endif
            <a href="{{ route('updates.index') }}" class="mt-4 bb">{{ __('admin.refresh') }}</a>
          </div>
    </div>
    @else
    <div class="card-body">
      <div class="empty-state" data-height="400" style="height: 400px;">
          <div class="empty-state-icon" style="background-color: #059669; display: flex; justify-content: center; align-items: center">
            <i class="fas fa-download"></i>
            {{-- <img src="/assets/src/spinners/blocks.svg" style="width: 50px;"> --}}
          </div>
          <h2>{{ __('admin.update_available') }}</h2>
          <p class="lead">
           {{ __('admin.update_available_desc') }}
          </p>
          <button type="button" data-toggle="modal" data-target="#installUpdateModal" class="btn btn-success mt-4">{{ __('admin.install') }} v{{ $latest_version->version }}</button>
          <a href="https://wemx.net/news" target="_blank" class="mt-4 bb">{{ __('admin.view_changelog') }}</a>
        </div>
    </div>
    @endif
    @endif
</div>

<div class="card">
    <div class="card-body">
        @foreach($versions as $version)
        <div class="media mb-4">
            <img class="mr-3" src="/assets/core/img/logo.png" alt="wemx logo" style="width: 46px; height: 46px; border-radius: 10px">
            <div class="media-body">
              <h5 class="mt-0">{{ __('admin.version') }} {{ $version->version }} ({{ $version->type }})</h5>
              <p class="mb-0">
                {!! $version->changelog ?? __('admin.no_changelog_provided') !!}
              </p>
              <small>{{ Carbon::parse($version->created_at)->diffForHumans() }} ({{ Carbon::parse($version->created_at)->translatedFormat('d M Y') }})</small>
            </div>
            @if(config('app.version') == $version->version)
              <a href="#" class="btn btn-success mt-4 disabled">{{ __('admin.installed') }}</a>
            @endif
        </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="installUpdateModal" tabindex="-1" role="dialog" aria-labelledby="installUpdateModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="installUpdateModalLabel">{{ __('admin.installing_update') }} {{ $latest_version->version }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info" role="alert">
          {{ __('admin.installing_update_warn') }}
        </div>
        {!! $latest_version->changelog !!}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">{!! __('admin.cancel') !!}</button>
        <a href="{{ route('updates.install', ['version' => $latest_version->version, 'type' => 'stable']) }}" class="btn btn-success">{{ __('admin.install') }} {{ $latest_version->version }}</a>
      </div>
    </div>
  </div>
</div>
@endsection
