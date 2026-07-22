@extends(AdminTheme::path('orders.edit.wrapper'), ['active' => 'details'])

@section('order-section')
<div class="card">
    <div class="card-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Package</strong></span> <span class="float-right"><a href="{{ route('packages.edit', $order->package->id) }}" target="_blank">{{ $order->package->name }}</a></span>
            </li>
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>User</strong></span> <span class="float-right"><a href="{{ route('users.edit', $order->user->id) }}" target="_blank">{{ $order->user->username }} ({{ $order->user->email }})</a></span>
            </li>
            @if($order->external_id)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>External ID</strong></span> 
                <span class="float-right">
                    <span style="cursor: pointer" onclick="copyToClipboard('{{ $order->external_id }}', this)" data-tooltip="Copiar al portapapeles">
                        {{ $order->external_id }} 
                        <i class="fa-solid fa-copy ml-1"></i>
                    </span>
                </span>
            </li>
            @endif
            @if($externalUser = $order->getExternalUser())
            @if($externalUser->external_id)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>External User Id</strong></span> 
                <span class="float-right">
                    <span style="cursor: pointer" onclick="copyToClipboard('{{ $externalUser->external_id }}', this)" data-tooltip="Copiar al portapapeles">
                        {{ $externalUser->external_id }}
                        <i class="fa-solid fa-copy ml-1"></i>
                    </span>
                </span>
            </li>
            @endif
            @if($externalUser->username)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>External Username</strong></span> 
                <span class="float-right">
                    <span style="cursor: pointer" onclick="copyToClipboard('{{ $externalUser->username }}', this)" data-tooltip="Copiar al portapapeles">
                        {{ $externalUser->username }}
                        <i class="fa-solid fa-copy ml-1"></i>
                    </span>
                </span>
            </li>
            @endif 
            @if($externalUser->password)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>External Password</strong></span> <span class="float-right" style="cursor: pointer" onclick="this.innerHTML = '{{ decrypt($externalUser->password) }}'">******************** <span><i class="fa-solid fa-eye"></i></span></span>
            </li>
            @endif
            @endif
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Status</strong></span> <span class="float-right">
                    <a href="#" class="badge
                    @if($order->status == 'active') badge-success @elseif($order->status == 'suspended') badge-warning @elseif($order->status == 'cancelled' OR $order->status == 'terminated') badge-danger
                    @else badge-primary @endif">
                    {!!  __('admin.' . $order->status, ['default' => $order->status]) !!}
                    </a>
                </span>
            </li>
            @if($order->domain)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Domain</strong></span> 
                <span class="float-right">
                    <span style="cursor: pointer" onclick="copyToClipboard('{{ $order->domain }}', this)" data-tooltip="Copiar al portapapeles">
                        {{ $order->domain }}
                        <i class="fa-solid fa-copy ml-1"></i>
                    </span>
                </span>
            </li>
            @endif
            <li class="list-group-item">
                @if($order->isRecurring())
                    <span class="text-dark text-bold"><strong>Renewal Price</strong></span> <span class="float-right">{{ price($order->price()->renewal_price) }} @ {{ $order->periodToHuman() }}</span>
                @else
                    <span class="text-dark text-bold"><strong>Price</strong></span> <span class="float-right">{{ price($order->price()->base_price) }} @ {{ $order->periodToHuman() }}</span>
                @endif
            </li>
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Setup Fee</strong></span> <span class="float-right">{{ price($order->price()->setup_fee) }}</span>
            </li>
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Upgrade Fee</strong></span> <span class="float-right">{{ price($order->price()->upgrade_fee) }}</span>
            </li>
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Cancellation Fee</strong></span> <span class="float-right">{{ price($order->price()->cancellation_fee) }}</span>
            </li>
            @if($order->last_renewed_at)
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Last Renewed at</strong></span> <span class="float-right">{{ $order->last_renewed_at->format(settings('date_format', 'd M Y')) }} ({{ $order->last_renewed_at->diffForHumans() }})</span>
            </li>
            @endif
            @if($order->due_date AND $order->isRecurring())
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Due date</strong></span> <span class="float-right">{{ $order->due_date->format(settings('date_format', 'd M Y')) }} ({{ $order->due_date->diffForHumans() }}) {{-- (in {{ $order->due_date->diffInDays() }} days) --}}</span>
            </li>
            @endif
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Created at</strong></span> <span class="float-right">{{ $order->created_at->format(settings('date_format', 'd M Y')) }} ({{ $order->created_at->diffForHumans() }})</span>
            </li>
            <li class="list-group-item">
                <span class="text-dark text-bold"><strong>Updated at</strong></span> <span class="float-right">{{ $order->updated_at->format(settings('date_format', 'd M Y')) }} ({{ $order->updated_at->diffForHumans() }})</span>
            </li>
          </ul>
    </div>
</div>
<div class="card">
    <form method="post" action="{{ route('orders.update', ['order' => $order->id]) }}"
          class="needs-validation" novalidate="">
        @csrf
        <div class="card-header">
            <h4>{!! __('admin.update_service', ['default' => 'Update Service']) !!}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-12 col-12">
                    <label>{!! __('admin.order_name', ['default' => 'Order Name']) !!}</label>
                    <input type="text" class="form-control" name="name" value="{{ $order->name }}"
                           required/>
                    <small class="form-text text-muted"></small>
                </div>

                {{--
                <div class="form-group col-md-12 col-12">
                    <label for="user">{!! __('admin.user', ['default' => 'User']) !!}</label>
                    <select class="form-control select2 select2-hidden-accessible" name="user_id"
                            tabindex="-1" aria-hidden="true">
                        @foreach (User::get() as $user)
                            <option value="{{ $user->id }}"
                                    @if($order->user->id == $user->id) selected="" @endif>{{ $user->username }}
                                ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                --}}


                <div class="form-group col-md-12 col-12">
                    <label
                        for="package_id">{!! __('admin.package', ['default' => 'Package']) !!}</label>
                    <select class="form-control select2 select2-hidden-accessible" name="package_id"
                            tabindex="-1" aria-hidden="true">
                        @foreach (Package::where('category_id',$order->package->category->id)->get() as $package)
                            <option value="{{ $package->id }}"
                                    @if($order->package->id == $package->id) selected="" @endif>{{ $package->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if($order->external_id)
                <div class="form-group col-md-12 col-12">
                    <label
                        for="external_id">External ID</label>
                    <input type="text" class="form-control" name="external_id"
                           value="{{ $order->external_id }}" required/>
                    <small class="form-text text-muted">The external id attached to this order</small>
                </div>
                @endif

                <div class="form-group col-md-12 col-12">
                    <label>{!! __('admin.notes', ['default' => 'Notes']) !!} {!! __('admin.optional', ['default' => '(optional)']) !!}</label>
                    <textarea type="text" class="form-control" rows="4"
                              name="notes">{{ $order->notes }}</textarea>
                </div>

                <div class="form-group col-md-6 col-6">
                    <label for="status">{!! __('admin.domain', ['default' => 'Domain']) !!}</label>
                    <input type="text" class="form-control" name="domain" value="{{ $order->domain }}"
                           placeholder="example.com"/>
                    <small class="form-text text-muted"></small>
                </div>

                <div class="form-group col-md-6 col-6">
                    <label
                        for="status">{!! __('admin.service_provider', ['default' => 'Service Provider']) !!}</label>
                    <input type="text" class="form-control" name="service" value="{{ $order->service }}"
                           disabled/>
                    <small class="form-text text-muted"></small>
                </div>

                <div class="form-group col-md-6 col-6">
                    <label
                        for="last_renewed_at">{!! __('admin.last_renewed_at', ['default' => 'Last Renewed at']) !!}</label>
                    <input type="date" class="form-control" name="last_renewed_at"
                           value="{{ $order->last_renewed_at->translatedFormat('Y-m-d') }}" required/>
                    <small class="form-text text-muted">{!! __('admin.last_renewed_at_service_desc', ['default' => 'The date service was last renewed at']) !!}</small>
                </div>

                <div class="form-group col-md-6 col-6">
                    <label
                        for="cancelled_at">{!! __('admin.cancelled_at', ['default' => 'Cancelled at']) !!}</label>
                    <input type="date" class="form-control" name="cancelled_at"
                           value="@isset($order->cancelled_at){{ $order->cancelled_at->translatedFormat('Y-m-d') }}@endisset"
                           @if($order->status == 'cancelled') required @else disabled @endif/>
                    <small class="form-text text-muted">
                        {!! __('admin.cancelled_at_service_desc', ['default' => 'The date the service is set to be cancelled. Only fillable if status is cancelled']) !!}
                    </small>
                </div>

                @if($order->data !== NULL)
                    <div class="form-group col-md-12 col-12">
                        <label>{!! __('admin.service_data', ['default' => 'Service Data']) !!}</label>
                        <textarea type="text" class="codeeditor" style="display: none;" rows="4"
                                  name="data">{{ $casts['data'] }}</textarea>
                        <small class="form-text text-muted">
                            {!! __('admin.service_data_warn', ['default' => '
                            Please refrain from modifying service data,
                            only proceed if you are sure of what you are doing and know proper
                            syntax.
                            ']) !!}
                        </small>
                    </div>
                @endif

                @if($order->options !== NULL)
                    <div class="form-group col-md-12 col-12">
                        <label>{!! __('admin.service_options', ['default' => 'Service Options']) !!}</label>
                        <textarea type="text" class="codeeditor" style="display: none;" rows="4"
                                  name="options">{{ $casts['options'] }}</textarea>
                        <small class="form-text text-muted">
                            {!! __('admin.service_options_warn', ['default' => '
                            Please refrain from modifying service
                            options, only proceed if you are sure of what you are doing and know proper
                            syntax.
                            ']) !!}
                        </small>
                    </div>
                @endif

            </div>
        </div>
        <div class="card-footer text-right">
            <button class="btn btn-dark" type="submit">{!! __('admin.update_changes', ['default' => 'Update Changes']) !!}</button>
        </div>
    </form>
</div>

<!-- Añade este tooltip CSS -->
<style>
[data-tooltip] {
    position: relative;
}

[data-tooltip]:hover:after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background-color: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 3px;
    white-space: nowrap;
    font-size: 12px;
    z-index: 1000;
    margin-bottom: 5px;
}
</style>

<!-- Añade este script para la funcionalidad de copiar al portapapeles -->
<script>
function copyToClipboard(text, element) {
    // Crear un elemento de texto temporal
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    
    // Intenta usar el API moderno primero
    try {
        navigator.clipboard.writeText(text).then(function() {
            showCopiedFeedback(element);
        }).catch(function() {
            // Si el API moderno falla, usa el método de fallback
            document.execCommand('copy');
            showCopiedFeedback(element);
        });
    } catch (err) {
        // Fallback para navegadores que no soportan clipboard API
        document.execCommand('copy');
        showCopiedFeedback(element);
    }
    
    // Eliminar el elemento temporal
    document.body.removeChild(tempInput);
}

function showCopiedFeedback(element) {
    // Guardar el HTML original
    const originalHTML = element.innerHTML;
    const originalTooltip = element.getAttribute('data-tooltip');
    
    // Cambiar el ícono y el tooltip
    element.innerHTML = element.innerText + ' <i class="fa-solid fa-check ml-1 text-success"></i>';
    element.setAttribute('data-tooltip', '¡Copiado!');
    
    // Restaurar después de 2 segundos
    setTimeout(function() {
        element.innerHTML = originalHTML;
        element.setAttribute('data-tooltip', originalTooltip);
    }, 2000);
}
</script>
@endsection