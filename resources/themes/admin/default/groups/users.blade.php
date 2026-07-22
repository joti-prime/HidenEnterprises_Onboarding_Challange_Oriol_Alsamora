@extends(AdminTheme::wrapper(), ['title' => __('admin.users'), 'keywords' => 'HCTestDash Dashboard, HCTestDash Panel'])

@section('container')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{!! __('admin.users') !!}</div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#exampleModal">
                        {!! __('admin.search', ['default' => 'Search']) !!}
                    </button>
                    <div class="table-responsive">
                      <table class="table table-striped table-md">
                          <tbody>
                              <tr>
                                  <th>{{ __('admin.username') }}</th>
                                  <th>{{ __('admin.email') }}</th>
                                  <th>{{ __('admin.balance') }}</th>
                                  <th>{{ __('admin.total_spent') }}</th>
                                  <th>{{ __('admin.vissibility') }}</th>
                                  <th>{{ __('admin.dates') }}</th>
                                  <th class="text-right">{{ __('admin.action') }}</th>
                              </tr>

                              @foreach ($users as $user)
                              <tr>
                                  <td>
                                    <div style="display: flex;align-items: center;">
                                      <img src="{{ $user->avatar() }}" alt="{{ __('admin.avatar') }}" style="width: 32px; border-radius: 20px; margin-right: 10px">
                                      <div>
                                        {{ $user->first_name }} {{ $user->last_name }} @if($user->is_admin()) <i class="fas fa-solid fa-star" style="color: gold"></i> @endif <br>
                                        <small>{{ $user->username }}</small>
                                      </div>
                                    </div>
                                  </td>
                                  <td>{{ $user->email }}</td>
                                  <td>{{ price($user->balance) }}</td>
                                  <td>{{ price(number_format($user->payments->where('status', 'paid')->sum('amount'))) }}</td>
                                  <td>
                                    <div>
                                      {{ ucfirst($user->visibility)  }}
                                    </div>
                                    <small>{{ $user->last_login_at->diffForHumans() }}</small>
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
    <div class="modal fade" tabindex="-1" role="dialog" id="exampleModal" aria-hidden="true" style="display: none;">
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
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
