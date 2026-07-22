@extends(AdminTheme::wrapper(), ['title' => __('admin.users'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('css_libraries')
    <link rel="stylesheet" href="{{ asset(AdminTheme::assets('modules/select2/dist/css/select2.min.css')) }}">

@endsection

@section('js_libraries')
    <script src="{{ asset(AdminTheme::assets('modules/select2/dist/js/select2.full.min.js')) }}"></script>
@endsection

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.users') !!}</div>
                <div class="card-body">
                   <div style="display: flex; justify-content: space-between" class="mb-3">
                      <button class="btn btn-icon icon-left btn-primary" data-toggle="modal" data-target="#userSearchModal">
                        <i class="fas fa-search"></i> {!! __('admin.search', ['default' => 'Search']) !!}
                      </button>

                      <div>
                        <button class="btn btn-icon icon-left btn-primary" data-toggle="modal" data-target="#userFilterModal">
                          <i class="fas fa-filter"></i> {!! __('admin.filter', ['default' => 'Filter']) !!}
                        </button>

                        <div class="dropdown d-inline">
                          <button class="btn btn-primary btn-icon icon-left dropdown-toggle" type="button" id="sortUsersDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-sort-alpha-up"></i> {!! __('admin.sort_by', ['default' => 'Sort By']) !!}
                          </button>
                          <div class="dropdown-menu" x-placement="bottom-start">
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'latest']) }}">{{ __('admin.latest') }}</a>
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'oldest']) }}">{{ __('admin.oldest') }}</a>
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'online']) }}">{!! __('admin.online_users') !!}</a>
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'balance']) }}">{!! __('admin.balance') !!}</a>
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'subscribed']) }}">{!! __('admin.subscribed') !!}</a>
                            <a class="dropdown-item" href="{{ route('users.index', ['sort' => 'random']) }}">{{ __('admin.random') }}</a>
                          </div>
                        </div>
                        <a class="btn btn-icon icon-left btn-primary" href="{{ route('users.create') }}">
                          <i class="fas fa-plus"></i> {!! __('admin.create', ['default' => 'Create']) !!}
                        </a>
                      </div>
                   </div>
                    <div class="table-responsive">
                      <table class="table table-striped table-md">
                          <tbody>
                              <tr>
                                  <th>{{ __('admin.username') }}</th>
                                  <th>{{ __('admin.email') }}</th>
                                  <th>{{ __('admin.country') }}</th>
                                  <th>{{ __('admin.balance') }}</th>
                                  <th>{{ __('admin.total_spent') }}</th>
                                  <th>{{ __('admin.vissibility') }}</th>
                                  <th>{{ __('admin.dates') }}</th>
                                  <th class="text-right">{{ __('admin.action') }}</th>
                              </tr>

                              @foreach ($users as $user)
                              <tr>
                                  <td>
                                    <a href="{{ route('users.edit', $user) }}" style="color: inherit">
                                      <div style="display: flex;align-items: center;">
                                        <img src="{{ $user->avatar() }}" alt="{{ __('admin.avatar') }}" style="width: 32px; border-radius: 20px; margin-right: 10px">
                                        <div>
                                          {{ $user->first_name }} {{ $user->last_name }} @if($user->is_admin()) <i class="fas fa-solid fa-star" style="color: gold"></i> @endif <br>
                                          <small>{{ $user->username }}</small>
                                        </div>
                                      </div>
                                    </a>
                                  </td>
                                  <td>{{ $user->email }}</td>
                                  <td>
                                    @if($user->address->country)
                                    <div class="d-flex align-items-center">
                                      <img src="/assets/core/img/flags/{{ strtolower($user->address->country) }}.svg" class="mr-2" style="border-radius: 1px; height: 15px;" alt="">
                                      {{ $user->address->country }}
                                    </div>
                                    @else 
                                      Unknown
                                    @endif
                                  </td>
                                  <td>{{ price($user->balance) }}</td>
                                  <td>{{ price($user->payments->where('status', 'paid')->sum('amount')) }}</td>
                                  <td>
                                    <div>
                                      @if($user->isOnline())
                                        <span class="beep-online"></span>
                                      @endif
                                      {{ ucfirst($user->visibility)  }}
                                    </div>
                                    <small>{{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'never' }}</small>
                                  </td>
                                  <td>
                                    <small>{{ __('admin.created') }}: {{ $user->created_at->diffForHumans() }}</small> <br>
                                    <small>{{ __('admin.updated') }}: {{ $user->updated_at->diffForHumans() }}</small>
                                  </td>
                                  <td class="text-right">
                                      <a href="{{ route('users.edit', $user) }}"
                                          class="btn btn-sm btn-primary">{!! __('admin.edit') !!}</a>
                                  </td>
                              </tr>
                            @endforeach
                          </tbody>
                      </table>
                  </div>

                </div>
            </div>
              {{ $users->links(AdminTheme::pagination()) }}
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="userSearchModal" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{!! __('admin.search_engine', ['default' => 'Search Engine']) !!}</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
                <form id="search-form">
                    <div class="form-group">
                        <label>{!! __('admin.search_users', ['default' => 'Search Users']) !!}</label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-search"></i>
                            </div>
                          </div>
                          <input type="text" id="search-input"class="form-control" placeholder="{!! __('admin.start_typing', ['default' => 'Start typing...']) !!}">
                        </div>
                      </div>
                  </form>

                  <div id="search-results"></div>
            </div>
            <div class="modal-footer bg-whitesmoke br">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close') !!}</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" tabindex="-1" role="dialog" id="userFilterModal" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
            <form action="">
            <div class="modal-header">
              <h5 class="modal-title">{!! __('admin.filter', ['default' => 'Filter']) !!}</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
                @csrf
                <div id="filters-container">
                    @if(isset(request()->filter))
                      @foreach(request()->filter as $key => $filter)
                      <div class="row filter" id="filter">
                        <div class="form-group col-4">
                          <label>{{ __('admin.key') }}</label>
                          <select class="form-control select2 select2-hidden-accessible" required="" name="filter[{{$key}}][key]" tabindex="-1" aria-hidden="true">
                            @foreach(User::$filters as $userFilter)
                              <option value="{{ $userFilter }}" @if($filter['key'] == $userFilter) selected @endif>{{ $userFilter }}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="form-group col-4">
                          <label>{{ __('admin.operator') }}</label>
                          <select class="form-control select2 select2-hidden-accessible" required="" name="filter[{{$key}}][operator]" tabindex="-1" aria-hidden="true">
                              <option value="=" @if($filter['operator'] == '=') selected @endif>Equals</option>
                              <option value="!=" @if($filter['operator'] == '!=') selected @endif>Does not Equal</option>
                              <option value="LIKE" @if($filter['operator'] == 'LIKE') selected @endif>Contains</option>
                              <option value="NOT LIKE" @if($filter['operator'] == 'NOT LIKE') selected @endif>Does not contain</option>
                              <option value=">" @if($filter['operator'] == '>') selected @endif>Greater Than</option>
                              <option value="<" @if($filter['operator'] == '<') selected @endif>Less Than</option>
                          </select>
                        </div>
                        <div class="form-group col-4">
                          <label>{{ __('admin.value') }}</label>
                          <input type="text" placeholder="Value" required="" value="{{ $filter['value'] }}" name="filter[{{$key}}][value]" class="form-control">
                        </div>
                      </div>
                      @endforeach
                    @else
                    <div class="row filter" id="filter">
                      <div class="form-group col-4">
                        <label>{{ __('admin.key') }}</label>
                        <select class="form-control select2 select2-hidden-accessible" required="" name="filter[0][key]" tabindex="-1" aria-hidden="true">
                          @foreach(User::$filters as $filter)
                            <option value="{{ $filter }}">{{ $filter }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group col-4">
                        <label>{{ __('admin.operator') }}</label>
                        <select class="form-control select2 select2-hidden-accessible" required="" name="filter[0][operator]" tabindex="-1" aria-hidden="true">
                            <option value="=">{{ __('admin.equals') }}</option>
                            <option value="!=">{{ __('admin.not_equals') }}</option>
                            <option value="LIKE">{{ __('admin.contains') }}</option>
                            <option value="NOT LIKE">{{ __('admin.not_contains') }}</option>
                            <option value=">">{{ __('admin.greater_than') }}</option>
                            <option value="<">{{ __('admin.less_than') }}</option>
                        </select>
                      </div>
                      <div class="form-group col-4">
                        <label>{{ __('admin.value') }}</label>
                        <input type="text" placeholder="Value" required="" name="filter[0][value]" class="form-control">
                      </div>
                    </div>
                    @endif
                </div>
                <button type="button" id="remove-filter" class="btn btn-danger">{{ __('admin.remove_filter') }}</button>
                <button type="button" id="add-filter" class="btn btn-primary">{{ __('admin.add_filter') }}</button>

            </div>
            <div class="modal-footer bg-whitesmoke br">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">{!! __('admin.close') !!}</button>
              <button type="submit" class="btn btn-primary">{!! __('admin.filter') !!}</button>
            </div>
            </form>
          </div>
        </div>
      </div>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('#add-filter').click(function() {
        // Clone the first filter div
        var newFilter = $('.filter:first').clone();
        
        // Find the highest existing index to ensure uniqueness
        var highestIndex = -1;
        $('[name^="filter["]').each(function() {
            var name = $(this).attr('name');
            var result = name.match(/\[(\d+)\]/);
            if (result && parseInt(result[1]) > highestIndex) {
                highestIndex = parseInt(result[1]);
            }
        });
        var newIndex = highestIndex + 1;
        
        // Update the 'name' attributes with the new index
        newFilter.find('[name]').each(function() {
            var name = $(this).attr('name').replace(/\[\d+\]/, '[' + newIndex + ']');
            $(this).attr('name', name);
        });
        
        // Append the new filter div to the container
        $('#filters-container').append(newFilter);
    });

    $('#remove-filter').click(function() {
        // Only remove the filter if there is more than one
        if ($('.filter').length > 1) {
            $('.filter:last').remove();
        }
    });
});
</script>

<script>
$(document).ready(function() {
  $('#search-input').on('input', function() {
    var query = $(this).val();
    $.ajax({
      url: '/admin/users/search',
      type: 'GET',
      data: {query: query},
      success: function(data) {
        var options = '';
        $.each(data, function(index, value) {
          options +=
        '<li class="media">' +
            '<img alt="image" class="mr-3 mb-3 rounded-circle" width="50" src="/storage/avatars/' + (value.avatar == null ? 'default.jpg' : value.avatar) + '">' +
            '<div class="media-body">' +
            '<div class="media-title">' + value.first_name + ' ' + value.last_name + ' [' + value.username +']</div>' +
            '<div class="text-job text-muted">'+ value.email +'</div>' +
            '</div>' +
            '<div class="media-items">' +
            '<div class="media-item">' +
                '<a href="/admin/users/' + value.id +'/edit" class="btn btn-sm btn-primary">View</a>' +
            '</div>' +
            '</div>' +
        '</li>'
          ;
        });
        $('#search-results').html(options);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  });
});
</script>
@endsection