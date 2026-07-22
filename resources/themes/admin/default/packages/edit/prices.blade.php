@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Package Prices', 'tab' => 'prices'])

@section('content')
<div>
    <button class="btn btn-primary mt-4 mb-4" data-toggle="modal"
            data-target="#createPriceModal">{{ __('admin.new_price') }}
    </button>

    <table class="table table-striped">
        <thead>
        <tr>
            <th scope="col">{{ __('admin.period') }}</th>
            <th scope="col">{{ __('admin.status') }}</th>
            <th scope="col">{{ __('admin.price') }}</th>
            <th scope="col">{{ __('admin.renewal_price') }}</th>
            <th scope="col">{{ __('admin.setup_fee') }}</th>
            <th scope="col">{{ __('admin.cancellation_fee') }}</th>
            <th scope="col">{!! __('admin.actions') !!}</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($prices as $price)
            <tr>
                <td>{{ $price->type == 'single' ? ucfirst($price->type) : ucfirst($price->type). ' / ' . $price->periodToHuman() }}</td>
                <td>
                    @if($price->is_active)
                        <div class="flex align-items-center">
                            <i class="fas fa-solid fa-circle  text-success "
                               style="font-size: 11px;"></i> {!! __('admin.active') !!}
                        </div>
                    @else
                        <div class="flex align-items-center">
                            <i class="fas fa-solid fa-circle  text-danger "
                               style="font-size: 11px;"></i> {!! __('admin.inactive') !!}
                        </div>
                    @endif
                </td>
                <td>{{ price($price->price) }}</td>
                <td>@isset($price->renewal_price)
                        {{ price($price->renewal_price) }}
                    @else
                        {{ price($price->price) }}
                    @endif
                </td>
                <td>{{ price($price->setup_fee) }}</td>
                <td>{{ price($price->cancellation_fee) }}</td>
                <td>
                    <button class="btn btn-primary mt-4 mb-4" data-toggle="modal"
                            data-target="#editPriceModal-{{ $price->id }}">{{ __('admin.edit') }}
                    </button>
                    <a href="{{ route('package_price.delete', ['price' => $price->id]) }}"
                       class="btn btn-icon icon-left btn-danger">{!! __('admin.delete') !!}</a>
                </td>
            </tr>

            {{-- create editing modal for each instance --}}
            <div class="modal fade" tabindex="-1" role="dialog"
                 id="editPriceModal-{{ $price->id }}">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form
                            action="{{ route('package_price.update', ['price' => $price->id]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ __('admin.editing_price_cycle') }}</h5>
                                <button type="button" class="close" data-dismiss="modal"
                                        aria-label="{{ __('admin.close') }}">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body modal-lg">

                                <div class="form-group">
                                    <label for="type">{{ __('admin.type') }}</label>
                                    <select class="form-control select2 select2-hidden-accessible hide" id="type-{{$price->id}}"
                                            name="type" tabindex="-1" aria-hidden="true" onchange="setPriceType('{{$price->id}}')">
                                        <option value="single" @if($price->type == 'single') selected @endif>{{ __('admin.single') }}</option>
                                        <option value="recurring" @if($price->type == 'recurring') selected @endif>{{ __('admin.recurring') }}</option>
                                    </select>
                                </div>

                                <div class="row @if($price->type == 'single') d-none @endif" id="recurring-options-{{ $price->id }}">
                                    <div class="form-group col-md-12 col-12">
                                        <label for="period">{{ __('admin.period') }}</label>
                                        <select
                                            class="form-control select2 select2-hidden-accessible hide"
                                            id="period" name="period" tabindex="-1"
                                            aria-hidden="true">
                                            <option value="1"
                                                    @if ($price->period == 1) selected @endif>
                                                {{ __('admin.daily') }}
                                            </option>
                                            <option value="7"
                                                    @if ($price->period == 7) selected @endif>
                                                {{ __('admin.weekly') }}
                                            </option>
                                            <option value="30"
                                                    @if ($price->period == 30) selected @endif>
                                                {{ __('admin.monthly') }}
                                            </option>
                                            <option value="90"
                                                    @if ($price->period == 90) selected @endif>
                                                {{ __('admin.quaterly') }}
                                            </option>
                                            <option value="180"
                                            @if ($price->period == 180) selected @endif>
                                                {{ __('admin.semi_yearly') }}
                                            </option>
                                            <option value="365"
                                                    @if ($price->period == 365) selected @endif>
                                                {{ __('admin.yearly') }}
                                            </option>
                                            <option value="730"
                                                    @if ($price->period == 730) selected @endif>
                                                {!! __('admin.per_years', ['years' => 2]) !!}
                                            </option>
                                            <option value="1825"
                                                    @if ($price->period == 1825) selected @endif>
                                                {!! __('admin.per_years', ['years' => 5]) !!}
                                            </option>
                                            <option value="3650"
                                                    @if ($price->period == 3650) selected @endif>
                                                {!! __('admin.per_years', ['years' => 10]) !!}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12 col-12">
                                        <label for="price">{{ __('admin.price') }}</label>
                                        <input onInput="updateRenewal({{ $price->id }})"
                                               type="number" name="price"
                                               id="price-{{ $price->id }}" min="0"
                                               step="0.01" value="{{ $price->price }}"
                                               class="form-control" required=""/>
                                    </div>

                                    <div class="form-group col-md-12 col-12">
                                        <label for="setup_fee">{{ __('admin.setup_fee') }}</label>
                                        <input type="number" name="setup_fee" id="setup_fee"
                                               min="0.00" step="0.01"
                                               value="{{ $price->setup_fee }}"
                                               class="form-control"
                                               required=""/>
                                    </div>

                                    <div class="form-group col-md-12 col-12">
                                        <label for="upgrade_fee">{{ __('admin.upgrade_fee') }}</label>
                                        <input type="number" name="upgrade_fee" id="upgrade_fee"
                                               min="0.00" step="0.01"
                                               value="{{ $price->upgrade_fee }}"
                                               class="form-control"
                                               required=""/>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12 col-12">
                                        <label for="price">{{ __('admin.data') }}</label>
                                        <textarea type="text" name="data"
                                                  id="data-{{ $price->id }}"
                                                  class="form-control">@json($price->data)</textarea>
                                        <small>{{ __('admin.data_for_custom_gateways_should_be_left_empty') }}</small>
                                    </div>
                                </div>

                                <div class="row @if($price->type == 'single') d-none @endif" id="price-options-{{ $price->id }}">
                                    <div class="form-group col-md-6 col-6">
                                        <div class="control-label">{{ __('admin.renewal_price') }}</div>
                                        <label class="custom-switch mt-2">
                                            <input onchange="checkbox({{ $price->id }})"
                                                   type="checkbox"
                                                   id="enable-renewal-price-{{ $price->id }}"
                                                   name="enable-renewal-price"
                                                   class="custom-switch-input">
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description">{{ __('admin.use_custom_renewal_price') }}</span>
                                        </label>
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label for="renewal_price" data-toggle="tooltip"
                                               data-placement="right" title=""
                                               data-original-title="Renewal price refers to the cost of renewing a subscription, service or contract after the initial period at a possibly different rate.">{{ __('admin.renewal_price') }} <i
                                                class="fa-solid fa-circle-info"></i></label>
                                        <input type="number" name="renewal_price"
                                               id="renewal_price-{{ $price->id }}" min="0.00"
                                               value="{{ $price->renewal_price }}" step="0.01"
                                               class="form-control" disabled/>
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <div class="control-label">{{ __('admin.cancelled_fee') }}</div>
                                        <label class="custom-switch mt-2">
                                            <input type="checkbox"
                                                   onchange="checkbox({{ $price->id }})"
                                                   id="enable-cancellation-fee-{{ $price->id }}"
                                                   name="enable-cancellation-fee"
                                                   class="custom-switch-input">
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description">{{ __('admin.setup_cancellation_fee') }}</span>
                                        </label>
                                    </div>

                                    <div class="form-group col-md-6 col-6">
                                        <label for="cancellation_fee" data-toggle="tooltip"
                                               data-placement="right" title=""
                                               data-original-title="Cancellation fee is a charge for ending a contract or service agreement before its end date.">{{ __('admin.cancelled_fee') }} <i class="fa-solid fa-circle-info"></i></label>
                                        <input type="number" name="cancellation_fee"
                                               id="cancellation_fee-{{ $price->id }}"
                                               value="{{ $price->cancellation_fee }}" min="0.00"
                                               step="0.01" class="form-control" disabled/>
                                    </div>

                                </div>
                                <div class="">
                                    <div class="form-group">
                                        <div class="control-label">{{ __('admin.active') }}</div>
                                        <label class="custom-switch mt-2">
                                            <input type="checkbox" name="is_active"
                                                   class="custom-switch-input" value="1"
                                                   @if($price->is_active) checked @endif>
                                            <span class="custom-switch-indicator"></span>
                                            <span class="custom-switch-description">{{ __('admin.you_can_deactivate_price_if_you_no_longer') }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-whitesmoke br">
                                <button type="button" class="btn btn-secondary"
                                        data-dismiss="modal">{{ __('admin.close') }}
                                </button>
                                <button class="btn btn-primary" type="submit">{{ __('admin.update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
        </tbody>
    </table>

    @if (Prices::where('package_id', $package->id)->count() == 0)
        @include(AdminTheme::path('empty-state'), [
            'title' => 'No prices found',
            'description' => 'This package is unlisted, please create a price.',
        ])
    @endif
</div>

    {{-- Create Item Modal --}}
    <div class="modal fade" tabindex="-1" role="dialog" id="createPriceModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('package_price.create', ['package' => $package->id]) }}" method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('admin.create_price') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('admin.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group">
                            <label for="type">{{ __('admin.type') }}</label>
                            <select class="form-control select2 select2-hidden-accessible hide" id="type-0"
                                    name="type" tabindex="-1" aria-hidden="true" onchange="setPriceType('0')">
                                <option value="single">{{ __('admin.single') }}</option>
                                <option value="recurring" selected>{{ __('admin.recurring') }}</option>
                            </select>
                        </div>

                        <div class="row" id="recurring-options-0">
                            <div class="form-group col-md-12 col-12">
                                <label for="period">{{ __('admin.period') }}</label>
                                <select class="form-control select2 select2-hidden-accessible hide" id="period"
                                        name="period" tabindex="-1" aria-hidden="true">
                                    <option value="1">{{ __('admin.daily') }}</option>
                                    <option value="7">{{ __('admin.weekly') }}</option>
                                    <option value="30" selected>{{ __('admin.monthly') }}</option>
                                    <option value="90">{{ __('admin.quaterly') }}</option>
                                    <option value="180">{{ __('admin.semi_yearly') }}</option>
                                    <option value="365">{{ __('admin.yearly') }}</option>
                                    <option value="730">{!! __('admin.per_years', ['years' => 2]) !!}</option>
                                    <option value="1825">{!! __('admin.per_years', ['years' => 5]) !!}</option>
                                    <option value="3650">{!! __('admin.per_years', ['years' => 10]) !!}</option>

                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12 col-12">
                                <label for="price-0">{{ __('admin.price') }}</label>
                                <input onInput="updateRenewal(0)" type="number" name="price" id="price-0"
                                       min="0" step="0.01" value="1.00" class="form-control" required=""/>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="setup_fee">{{ __('admin.setup_fee') }}</label>
                                <input type="number" name="setup_fee" id="setup_fee" min="0.00" step="0.01"
                                       value="0.00" class="form-control" required=""/>
                            </div>

                            <div class="form-group col-md-12 col-12">
                                <label for="upgrade_fee">{{ __('admin.upgrade_fee') }}</label>
                                <input type="number" name="upgrade_fee" id="upgrade_fee" min="0.00" step="0.01"
                                       value="0.00" class="form-control" required=""/>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-12 col-12">
                                <label for="data">{{ __('admin.data') }}</label>
                                <textarea type="text" name="data" id="data"
                                          class="form-control"></textarea>
                                <small>{{ __('admin.data_for_custom_gateways_should_be_left_empty') }}</small>
                            </div>
                        </div>

                        <div class="row" id="price-options-0">
                            <div class="form-group col-md-6 col-6">
                                <div class="control-label">{{ __('admin.renewal_price') }}</div>
                                <label class="custom-switch mt-2">
                                    <input onchange="checkbox(0)" type="checkbox" id="enable-renewal-price-0"
                                           name="enable-renewal-price" class="custom-switch-input">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">{{ __('admin.use_custom_renewal_price') }}</span>
                                </label>
                            </div>

                            <div class="form-group col-md-6 col-6">
                                <label for="renewal_price-0" data-toggle="tooltip" data-placement="right" title=""
                                       data-original-title="Renewal price refers to the cost of renewing a subscription, service or contract after the initial period at a possibly different rate.">{!! __('admin.renewal_price') !!} <i class="fa-solid fa-circle-info"></i></label>
                                <input type="number" name="renewal_price" id="renewal_price-0" min="0.00"
                                       step="0.01" class="form-control" disabled/>
                            </div>

                            <div class="form-group col-md-6 col-6">
                                <div class="control-label">{{ __('admin.cancellation_fee') }}</div>
                                <label class="custom-switch mt-2">
                                    <input type="checkbox" onchange="checkbox(0)" id="enable-cancellation-fee-0"
                                           name="enable-cancellation-fee" class="custom-switch-input">
                                    <span class="custom-switch-indicator"></span>
                                    <span class="custom-switch-description">{{ __('admin.setup_cancellation_fee') }}</span>
                                </label>
                            </div>

                            <div class="form-group col-md-6 col-6">
                                <label for="cancellation_fee-0" data-toggle="tooltip" data-placement="right"
                                       title=""
                                       data-original-title="Cancellation fee is a charge for ending a contract or service agreement before its end date.">
                                    {!! __('admin.cancellation_fee') !!} <i class="fa-solid fa-circle-info"></i></label>
                                <input type="number" name="cancellation_fee" id="cancellation_fee-0" min="0.00"
                                       step="0.01" class="form-control" disabled/>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-whitesmoke br">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('admin.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.create') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    function updateRenewal(id) {
        if (document.getElementById('enable-renewal-price-' + id).checked == false) {
            document.getElementById('renewal_price-' + id).value = document.getElementById('price-' + id).value;
        }
    }

    function checkbox(id) {
        if (document.getElementById('enable-renewal-price-' + id).checked == false) {
            document.getElementById('renewal_price-' + id).setAttribute('disabled', '');
        } else {
            document.getElementById('renewal_price-' + id).removeAttribute('disabled');
        }

        if (document.getElementById('enable-cancellation-fee-' + id).checked == false) {
            document.getElementById('cancellation_fee-' + id).setAttribute('disabled', '');
        } else {
            document.getElementById('cancellation_fee-' + id).removeAttribute('disabled');
        }
    }

    function setPriceType(id) {
        var type = document.getElementById('type-' + id).value;

        if(type == 'single') {
            document.getElementById('recurring-options-' + id).classList.add('d-none');
            document.getElementById('price-options-'+ id).classList.add('d-none');
        } else {
            document.getElementById('recurring-options-'+ id).classList.remove('d-none');
            document.getElementById('price-options-'+ id).classList.remove('d-none');
        }
    }
</script>
@endsection