@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Configurable Options', 'tab' => 'config_options'])

@section('content')
<div class="p-0">
    <button type="button" class="btn btn-primary mb-2" data-toggle="modal" data-target=".bd-create-option-modal-lg">Create Option</button>
    <div class="table-responsive">
      <table class="table table-striped table-md">
        <tbody><tr>
          <th>#</th>
          <th>Key</th>
          <th>Type</th>
          <th>Is Onetime</th>
          <th>Price per 30 days</th>
          <th>Action</th>
        </tr>
        @foreach($package->configOptions()->orderBy('order', 'desc')->get() as $option)
        <tr>
          <td>{{ $option->id }}</td>
          <td>{{ $option->key }}</td>
          <td>{{ $option->type }}</td>
          <td>{{ $option->is_onetime ? 'True' : 'False' }}</td>
          <td>{{ number_format($option->price_per_30_days, 2) }}</td>
          <td>
            <a href="{{ route('packages.config-options.move-option', ['package' => $package->id, 'option' => $option->id, 'direction' => 'up']) }}"
              class="btn btn-primary"><i class="fas fa-solid fa-caret-up"></i></a>
           <a href="{{ route('packages.config-options.move-option', ['package' => $package->id, 'option' => $option->id, 'direction' => 'down']) }}"
              class="btn btn-primary"><i
                   class="fas fa-solid fa-caret-down"></i></a>
            <a href="{{ route('packages.config-options.edit-option', ['package' => $package->id, 'option' => $option->id]) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('packages.config-options.delete-option', ['package' => $package->id, 'option' => $option->id]) }}"
              class="btn btn-danger"><i class="fas fa-solid fa-trash"></i></a>
          </td>
        </tr>
        <div class="modal fade bd-update-option-modal-lg-{{ $option->id }}" tabindex="-1" role="dialog" aria-labelledby="UpdateOptionModalLabel{{ $option->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <form action="#" method="POST">
                    @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Update Configurable Option</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if($option->type == 'select')
                        <p>Add the options below that the user can select at checkout for {{ $option->key }}</p>
                        <div class="row">
                            <div class="form-group col-md-12">
                              <label for="data[label]">Input Label</label>
                              <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required=""/>
                              <small class="form-text text-muted">Label of the input form</small>
                            </div>
                            <div class="form-group col-md-12">
                              <label for="data[description]">Input Description</label>
                              <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required=""/>
                              <small class="form-text text-muted">Write a description for the input form</small>
                            </div>
                            <hr>
                        </div>
                          <div id="select_options_{{ $option->id }}">
                            @if(isset($option->data['options']) AND count($option->data['options']) > 0)
                              @foreach($option->data['options'] as $key => $optionData)
                              <div class="row" id="options-row">
                                <div class="form-group col-md-4">
                                    <label for="data[options][{{ $key }}][value]">value</label>
                                    <input type="text" name="data[options][{{ $key }}][value]" placeholder="Value" value="{{ $optionData['value'] }}" class="form-control" required=""/>
                                    <small class="form-text text-muted"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="data[options][{{ $key }}][name]">display text</label>
                                    <input type="text" name="data[options][{{ $key }}][name]" placeholder="Display Text" value="{{ $optionData['name'] }}" class="form-control" required=""/>
                                    <small class="form-text text-muted"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="data[options][{{ $key }}][name]">Price per 30 days</label>
                                    <input type="number" min="0" name="data[options][{{ $key }}][monthly_price]" value="{{ $optionData['monthly_price'] }}" placeholder="10.00" class="form-control" required=""/>
                                    <small class="form-text text-muted"></small>
                                </div>
                              </div>
                              @endforeach
                            @endif
                            @if(!isset($option->data['options']) OR count($option->data['options']) == 0)
                            <div class="row">
                              <div class="form-group col-md-4">
                                  <label>value</label>
                                  <input type="text" name="data[options][0][value]" placeholder="Value" class="form-control" required=""/>
                                  <small class="form-text text-muted"></small>
                              </div>
                              <div class="form-group col-md-4">
                                  <label>display text</label>
                                  <input type="text" name="data[options][0][name]" placeholder="Display Text" class="form-control" required=""/>
                                  <small class="form-text text-muted"></small>
                              </div>
                              <div class="form-group col-md-4">
                                  <label>Price per 30 days</label>
                                  <input type="number" min="0" name="data[options][0][monthly_price]" placeholder="10.00" class="form-control" required=""/>
                                  <small class="form-text text-muted"></small>
                              </div>
                            </div>
                            @endif
                          </div>
                          <a onclick="duplicateAndIncrementOptions('select_options_{{ $option->id }}')" class="text-success mr-2" style="cursor: pointer">Add Option</a>
                          <a onclick="deleteLastChildOfDiv('select_options_{{ $option->id }}')" class="text-danger" style="cursor: pointer">Remove Option</a>

                    @elseif($option->type == 'radio')

                    @elseif($option->type == 'checkbox')

                    @elseif($option->type == 'range')
                    <div class="row">
                      <div class="form-group col-md-12">
                        <label for="data[label]">Input Label</label>
                        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Label of the input form</small>
                      </div>
                      <div class="form-group col-md-12">
                        <label for="data[description]">Input Description</label>
                        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Write a description for the input form</small>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="data[default_value]">Default Value</label>
                        <input type="number" name="data[default_value]" value="{{ $option->data['default_value'] ?? '10' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="data[min]">Minimum</label>
                        <input type="number" name="data[min]" value="{{ $option->data['min'] ?? '0' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="data[max]">Max</label>
                        <input type="number" name="data[max]" value="{{ $option->data['max'] ?? '10' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-3">
                        <label for="data[step]">Step</label>
                        <input type="number" name="data[step]" value="{{ $option->data['step'] ?? '1' }}" min="0.1" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-6">
                        <div class="control-label">Is one time?</div>
                        <label class="custom-switch mt-2">
                          <input type="checkbox" name="data[is_onetime]" class="custom-switch-input">
                          <span class="custom-switch-indicator"></span>
                          <span class="custom-switch-description">Enable to make it a one time fee</span>
                        </label>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="data[monthly_price_unit]">Unit Price / 30 days</label>
                        <input type="number" name="data[monthly_price_unit]" value="{{ $option->data['monthly_price_unit'] ?? '0' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Price of 1 ({{ $option->key }}) unit per 30 days</small>
                      </div>
                  </div>
                    @elseif($option->type == 'number')
                    <div class="row">
                      <div class="form-group col-md-12">
                        <label for="data[label]">Input Label</label>
                        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Label of the input form</small>
                      </div>
                      <div class="form-group col-md-12">
                        <label for="data[description]">Input Description</label>
                        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Write a description for the input form</small>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="data[default_value]">Default Value</label>
                        <input type="number" name="data[default_value]" value="{{ $option->data['default_value'] ?? '10' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="data[min]">Minimum</label>
                        <input type="number" name="data[min]" value="{{ $option->data['min'] ?? '0' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-4">
                        <label for="data[max]">Max</label>
                        <input type="number" name="data[max]" value="{{ $option->data['max'] ?? '10' }}" class="form-control" required=""/>
                      </div>
                      <div class="form-group col-md-6">
                        <div class="control-label">Is one time?</div>
                        <label class="custom-switch mt-2">
                          <input type="checkbox" name="data[is_onetime]" class="custom-switch-input">
                          <span class="custom-switch-indicator"></span>
                          <span class="custom-switch-description">Enable to make it a one time fee</span>
                        </label>
                      </div>
                      <div class="form-group col-md-6">
                        <label for="data[monthly_price_unit]">Unit Price / 30 days</label>
                        <input type="number" name="data[monthly_price_unit]" value="{{ $option->data['monthly_price_unit'] ?? '0' }}" class="form-control" required=""/>
                        <small class="form-text text-muted">Price of 1 ({{ $option->key }}) unit per 30 days</small>
                      </div>
                  </div>
                  @endif
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
                </form>
              </div>
            </div>
        </div>
        @endforeach
      </tbody>
    </table>
    </div>
</div>

<div class="modal fade bd-create-option-modal-lg" tabindex="-1" role="dialog" aria-labelledby="CreateOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('packages.config-options.add', $package->id) }}" method="POST">
            @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLongTitle">Create Configurable Option</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="key">{{ __('admin.key') }}</label>
                <select class="form-control select2 select2-hidden-accessible hide" name="key" tabindex="-1" aria-hidden="true">
                    @foreach($package->service()->getPackageConfig($package)->all() as $config)
                        @if(!isset($config['is_configurable']) OR !$config['is_configurable'])
                            @continue
                        @endif
                        <option value="{{ $config['key'] }}">{{ $config['name'] }} ({{ $config['type'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="type">{{ __('admin.type') }}</label>
                <select class="form-control select2 select2-hidden-accessible hide" name="type" tabindex="-1" aria-hidden="true">
                    <option value="range">Range slider</option>
                    <option value="number">Quantity / Number</option>
                    {{-- <option value="radio">Radio</option> --}}
                    <option value="select">Select Dropdown</option>
                    <option value="text">Text</option>
                    {{-- <option value="checkbox">Checkbox</option> --}}
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Add</button>
        </div>
        </form>
      </div>
    </div>
</div>

<script>
  function duplicateFirstChildOfDiv(originalDivId) {
      // Select the original div
      var originalDiv = document.getElementById(originalDivId);
      if (!originalDiv) {
          console.error('The original div was not found');
          return;
      }

      // Select the first child of the original div
      var firstChild = originalDiv.firstElementChild;
      if (!firstChild) {
          console.error('No child elements found inside the div');
          return;
      }

      // Duplicate the first child
      var duplicateChild = firstChild.cloneNode(true);

      // Append the duplicated child to the same parent div
      originalDiv.appendChild(duplicateChild);
  }

  function deleteLastChildOfDiv(divId) {
      // Select the div
      var div = document.getElementById(divId);
      if (!div) {
          console.error('The div was not found');
          return;
      }

      // Check if the div has any children
      if (div.lastElementChild) {
          // Delete the last child of the div
          div.removeChild(div.lastElementChild);
      } else {
          console.error('No child elements to remove');
      }
  }

  function duplicateAndIncrementOptions(div) {
      // Find the parent container
      var container = document.getElementById(div);
      if (!container) return; // Exit if container not found

      // Find the last row within the container
      var lastRow = container.querySelector('.row:last-child');
      if (!lastRow) return; // Exit if no row found

      // Clone the last row
      var clonedRow = lastRow.cloneNode(true);

      // Update the name attributes of inputs in the cloned row
      var inputs = clonedRow.querySelectorAll('input');
      inputs.forEach(function(input) {
          var match = input.name.match(/\[options\]\[(\d+)\]/);
          if (match && match[1]) {
              var index = parseInt(match[1], 10);
              var newIndex = index + 1;
              input.name = input.name.replace(`[options][${index}]`, `[options][${newIndex}]`);
          }
      });

      // Append the cloned row to the container
      container.appendChild(clonedRow);
  }

</script>

@endsection
