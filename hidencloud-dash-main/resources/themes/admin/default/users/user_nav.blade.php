<div class="card">
    <div class="card-body">
        <ul class="nav nav-pills">
            <li class="nav-item">
              <a class="nav-link {{ Route::currentRouteName() === 'users.edit' ? 'active' : '' }}"
                 href="{{ route('users.edit', ['user' => $user->id]) }}"><i class="fas fa-user"></i> {!! __('admin.settings') !!}</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Route::currentRouteName() === 'admin.user.orders' ? 'active' : '' }}"
                 href="{{ route('admin.user.orders', ['user' => $user->id]) }}"><i class="fas fa-box"></i> {!! __('admin.orders') !!}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() === 'admin.user.invoices' ? 'active' : '' }}"
                   href="{{ route('admin.user.invoices', ['user' => $user->id]) }}"><i class="fas fa-solid fa-receipt"></i> {!! __('admin.invoices') !!}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() === 'admin.user.emails' ? 'active' : '' }}"
                   href="{{ route('admin.user.emails', ['user' => $user->id]) }}"><i class="fas fa-solid fa-envelope"></i> {!! __('admin.emails') !!}</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Route::currentRouteName() === 'admin.user.punishments' ? 'active' : '' }}"
                 href="{{ route('admin.user.punishments', ['user' => $user->id]) }}"><i class="fas fa-solid fa-gavel"></i> {!! __('admin.punishments') !!}</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ Route::currentRouteName() === 'admin.user.activity' ? 'active' : '' }}"
                 href="{{ route('admin.user.activity', ['user' => $user->id]) }}"><i class="fas fa-solid fa-compass"></i> {!! __('admin.activity') !!}</a>
            </li>
          </ul>
    </div>
</div>
