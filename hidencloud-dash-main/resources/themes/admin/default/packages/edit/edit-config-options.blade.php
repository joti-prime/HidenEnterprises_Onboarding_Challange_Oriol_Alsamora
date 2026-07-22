@extends(AdminTheme::path('packages/edit/master'), ['title' => 'Configurable Options', 'tab' => 'config_options'])

@section('content')
<form action="{{ route('packages.config-options.update-option', ['package' => $package->id, 'option' => $option->id]) }}" method="POST">
    @csrf
@if($option->type == 'select')
<p>Add the options below that the user can select at checkout for {{ $option->key }}</p>
<div class="row">
    <div class="form-group col-md-12">
        <label for="data[label]">Input Label</label>
        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Label of the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="data[description]">Input Description</label>
        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Write a description for the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="rules{{ $option->id }}">Validation Rules</label>
        <input type="text" name="rules" id="rules{{ $option->id }}" value="{{ $option->rules }}" class="form-control" required=""/>
        <small class="form-text text-muted">View all <a href="https://laravel.com/docs/11.x/validation#available-validation-rules" target="_blank">validation rules</a></small>
        <div>
            <button type="button" onclick="appendRule('required')" class="btn btn-sm btn-primary">required</button>
            <button type="button" onclick="appendRule('numeric')" class="btn btn-sm btn-primary">numeric</button>
            <button type="button" onclick="appendRule('email')" class="btn btn-sm btn-primary">email</button>
            <button type="button" onclick="appendRule('active_url')" class="btn btn-sm btn-primary">active url</button>
            <button type="button" onclick="appendRule('url')" class="btn btn-sm btn-primary">url</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
            <button type="button" onclick="appendRule('min:3')" class="btn btn-sm btn-primary">min chars</button>
            <button type="button" onclick="appendRule('max:255')" class="btn btn-sm btn-primary">max chars</button>
            <button type="button" onclick="appendRule('in:audi,bmw,mercedes')" class="btn btn-sm btn-primary">in list</button>
            <button type="button" onclick="appendRule('starts_with:test')" class="btn btn-sm btn-primary">stars with</button>
            <button type="button" onclick="appendRule('ends_with:test')" class="btn btn-sm btn-primary">ends with</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
        </div>
        <small class="form-text text-muted"><a href="#" onclick="document.getElementById('rules{{ $option->id }}').value = '{{ $package->service()->getPackageRule($package, $option->key, 'string') }}'">reset to default</a></small>
    </div>
    <hr />
</div>
<div id="select_options_{{ $option->id }}">
    @if(isset($option->data['options']) AND count($option->data['options']) > 0) @foreach($option->data['options'] as $key => $optionData)
    <div class="row" id="options-row">
        <div class="form-group col-md-4">
            <label for="data[options][{{ $key }}][value]">value</label>
            <input type="text" name="data[options][{{ $key }}][value]" onchange="selectOptionsUpdated()" placeholder="Value" value="{{ $optionData['value'] }}" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
        <div class="form-group col-md-4">
            <label for="data[options][{{ $key }}][name]">display text</label>
            <input type="text" name="data[options][{{ $key }}][name]" placeholder="Display Text" value="{{ $optionData['name'] }}" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
        <div class="form-group col-md-4">
            <label for="data[options][{{ $key }}][name]">Price per 30 days</label>
            <input type="text" min="0" name="data[options][{{ $key }}][monthly_price]" value="{{ $optionData['monthly_price'] }}" placeholder="10.00" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
    </div>
    @endforeach @endif @if(!isset($option->data['options']) OR count($option->data['options']) == 0)
    <div class="row">
        <div class="form-group col-md-4">
            <label>value</label>
            <input type="text" name="data[options][0][value]" onchange="selectOptionsUpdated()" placeholder="Value" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
        <div class="form-group col-md-4">
            <label>display text</label>
            <input type="text" name="data[options][0][name]" placeholder="Display Text" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
        <div class="form-group col-md-4">
            <label>Price per 30 days</label>
            <input type="text" min="0" name="data[options][0][monthly_price]" placeholder="10.00" class="form-control" required="" />
            <small class="form-text text-muted"></small>
        </div>
    </div>
    @endif
</div>
<a onclick="duplicateAndIncrementOptions('select_options_{{ $option->id }}')" class="text-success mr-2" style="cursor: pointer;">Add Option</a>
<a onclick="deleteLastChildOfDiv('select_options_{{ $option->id }}')" class="text-danger" style="cursor: pointer;">Remove Option</a>
@elseif($option->type == 'text')
<div class="row">
    <div class="form-group col-md-12">
        <label for="data[label]">Input Label</label>
        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Label of the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="data[description]">Input Description</label>
        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Write a description for the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="rules{{ $option->id }}">Validation Rules</label>
        <input type="text" name="rules" id="rules{{ $option->id }}" value="{{ $option->rules }}" class="form-control" required=""/>
        <small class="form-text text-muted">View all <a href="https://laravel.com/docs/11.x/validation#available-validation-rules" target="_blank">validation rules</a></small>
        <div>
            <button type="button" onclick="appendRule('required')" class="btn btn-sm btn-primary">required</button>
            <button type="button" onclick="appendRule('numeric')" class="btn btn-sm btn-primary">numeric</button>
            <button type="button" onclick="appendRule('email')" class="btn btn-sm btn-primary">email</button>
            <button type="button" onclick="appendRule('active_url')" class="btn btn-sm btn-primary">active url</button>
            <button type="button" onclick="appendRule('url')" class="btn btn-sm btn-primary">url</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
            <button type="button" onclick="appendRule('min:3')" class="btn btn-sm btn-primary">min chars</button>
            <button type="button" onclick="appendRule('max:255')" class="btn btn-sm btn-primary">max chars</button>
            <button type="button" onclick="appendRule('in:audi,bmw,mercedes')" class="btn btn-sm btn-primary">in list</button>
            <button type="button" onclick="appendRule('starts_with:test')" class="btn btn-sm btn-primary">stars with</button>
            <button type="button" onclick="appendRule('ends_with:test')" class="btn btn-sm btn-primary">ends with</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
        </div>
        <small class="form-text text-muted"><a href="#" onclick="document.getElementById('rules{{ $option->id }}').value = '{{ $package->service()->getPackageRule($package, $option->key, 'string') }}'">reset to default</a></small>
    </div>
    <div class="form-group col-md-12">
        <label name="data[type]">Field Type</label>
        <div class="input-group mb-2">
            <select class="form-control select2 select2-hidden-accessible" name="data[type]" tabindex="-1" aria-hidden="true" required>
                <option value="text" @if($option->data['type'] ?? 'text' == 'text') selected @endif>Text</option>
                <option value="email" @if($option->data['type'] ?? 'text' == 'email') selected @endif>Email</option>
                <option value="number" @if($option->data['type'] ?? 'text' == 'number') selected @endif>Number</option>
                <option value="date" @if($option->data['type'] ?? 'text' == 'date') selected @endif>Date</option>
                <option value="url" @if($option->data['type'] ?? 'text' == 'url') selected @endif>Url</option>
                <option value="password" @if($option->data['type'] ?? 'text' == 'password') selected @endif>Password</option>
            </select>
            <small class="form-text text-muted">Select field type</small>
        </div>
    </div>
    <div class="form-group col-md-12">
        <label for="data[default_value]">Default Value</label>
        <input type="number" name="data[default_value]" value="{{ $option->data['default_value'] ?? '' }}" class="form-control" />
    </div>
    <div class="form-group col-md-12">
        <label for="data[placeholder]">Placeholder Text</label>
        <input type="number" name="data[placeholder]" value="{{ $option->data['placeholder'] ?? '' }}" class="form-control" />
    </div>
</div>
@elseif($option->type == 'radio') @elseif($option->type == 'checkbox') @elseif($option->type == 'range')
<div class="row">
    <div class="form-group col-md-12">
        <label for="data[label]">Input Label</label>
        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Label of the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="data[description]">Input Description</label>
        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Write a description for the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="rules{{ $option->id }}">Validation Rules</label>
        <input type="text" name="rules" id="rules{{ $option->id }}" value="{{ $option->rules }}" class="form-control" required=""/>
        <small class="form-text text-muted">View all <a href="https://laravel.com/docs/11.x/validation#available-validation-rules" target="_blank">validation rules</a></small>
        <div>
            <button type="button" onclick="appendRule('required')" class="btn btn-sm btn-primary">required</button>
            <button type="button" onclick="appendRule('numeric')" class="btn btn-sm btn-primary">numeric</button>
            <button type="button" onclick="appendRule('email')" class="btn btn-sm btn-primary">email</button>
            <button type="button" onclick="appendRule('active_url')" class="btn btn-sm btn-primary">active url</button>
            <button type="button" onclick="appendRule('url')" class="btn btn-sm btn-primary">url</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
            <button type="button" onclick="appendRule('min:3')" class="btn btn-sm btn-primary">min chars</button>
            <button type="button" onclick="appendRule('max:255')" class="btn btn-sm btn-primary">max chars</button>
            <button type="button" onclick="appendRule('in:audi,bmw,mercedes')" class="btn btn-sm btn-primary">in list</button>
            <button type="button" onclick="appendRule('starts_with:test')" class="btn btn-sm btn-primary">stars with</button>
            <button type="button" onclick="appendRule('ends_with:test')" class="btn btn-sm btn-primary">ends with</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
        </div>        
        <small class="form-text text-muted"><a href="#" onclick="document.getElementById('rules{{ $option->id }}').value = '{{ $package->service()->getPackageRule($package, $option->key, 'string') }}'">reset to default</a></small>
    </div>
    <div class="form-group col-md-3">
        <label for="data[default_value]">Default Value</label>
        <input type="number" name="data[default_value]" value="{{ $option->data['default_value'] ?? '10' }}" class="form-control" required="" />
    </div>
    <div class="form-group col-md-3">
        <label for="data[min]">Minimum</label>
        <input type="number" name="data[min]" value="{{ $option->data['min'] ?? '0' }}" class="form-control" onchange="minUpdated(this.value)" required="" />
    </div>
    <div class="form-group col-md-3">
        <label for="data[max]">Max</label>
        <input type="number" name="data[max]" value="{{ $option->data['max'] ?? '10' }}" class="form-control" onchange="maxUpdated(this.value)" required="" />
    </div>
    <div class="form-group col-md-3">
        <label for="data[step]">Step</label>
        <input type="number" name="data[step]" value="{{ $option->data['step'] ?? '1' }}" class="form-control" required="" />
    </div>
    <div class="form-group col-md-6">
        <div class="control-label">Is one time?</div>
        <label class="custom-switch mt-2">
            <input type="checkbox" name="data[is_onetime]" class="custom-switch-input" />
            <span class="custom-switch-indicator"></span>
            <span class="custom-switch-description">Enable to make it a one time fee</span>
        </label>
    </div>
    <div class="form-group col-md-6">
        <label for="data[monthly_price_unit]">Unit Price / 30 days</label>
        <input type="text" name="data[monthly_price_unit]" value="{{ $option->data['monthly_price_unit'] ?? '0' }}" class="form-control" required="" />
        <small class="form-text text-muted">Price of 1 ({{ $option->key }}) unit per 30 days</small>
    </div>
</div>
@elseif($option->type == 'number')
<div class="row">
    <div class="form-group col-md-12">
        <label for="data[label]">Input Label</label>
        <input type="text" name="data[label]" placeholder="Select Option" value="{{ $option->data['label'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Label of the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="data[description]">Input Description</label>
        <input type="text" name="data[description]" placeholder="Write a description..." value="{{ $option->data['description'] ?? '' }}" class="form-control" required="" />
        <small class="form-text text-muted">Write a description for the input form</small>
    </div>
    <div class="form-group col-md-12">
        <label for="rules{{ $option->id }}">Validation Rules</label>
        <input type="text" name="rules" id="rules{{ $option->id }}" value="{{ $option->rules }}" class="form-control" required=""/>
        <small class="form-text text-muted">View all <a href="https://laravel.com/docs/11.x/validation#available-validation-rules" target="_blank">validation rules</a></small>
        <div>
            <button type="button" onclick="appendRule('required')" class="btn btn-sm btn-primary">required</button>
            <button type="button" onclick="appendRule('numeric')" class="btn btn-sm btn-primary">numeric</button>
            <button type="button" onclick="appendRule('email')" class="btn btn-sm btn-primary">email</button>
            <button type="button" onclick="appendRule('active_url')" class="btn btn-sm btn-primary">active url</button>
            <button type="button" onclick="appendRule('url')" class="btn btn-sm btn-primary">url</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
            <button type="button" onclick="appendRule('min:3')" class="btn btn-sm btn-primary">min chars</button>
            <button type="button" onclick="appendRule('max:255')" class="btn btn-sm btn-primary">max chars</button>
            <button type="button" onclick="appendRule('in:audi,bmw,mercedes')" class="btn btn-sm btn-primary">in list</button>
            <button type="button" onclick="appendRule('starts_with:test')" class="btn btn-sm btn-primary">stars with</button>
            <button type="button" onclick="appendRule('ends_with:test')" class="btn btn-sm btn-primary">ends with</button>
            <button type="button" onclick="appendRule('date')" class="btn btn-sm btn-primary">date</button>
        </div>
        <small class="form-text text-muted"><a href="#" onclick="document.getElementById('rules{{ $option->id }}').value = '{{ $package->service()->getPackageRule($package, $option->key, 'string') }}'">reset to default</a></small>
    </div>
    <div class="form-group col-md-4">
        <label for="data[default_value]">Default Value</label>
        <input type="number" name="data[default_value]" value="{{ $option->data['default_value'] ?? '10' }}" class="form-control" required="" />
    </div>
    <div class="form-group col-md-4">
        <label for="data[min]">Minimum</label>
        <input type="number" name="data[min]" value="{{ $option->data['min'] ?? '0' }}" class="form-control" onchange="minUpdated(this.value)" required="" />
    </div>
    <div class="form-group col-md-4">
        <label for="data[max]">Max</label>
        <input type="number" name="data[max]" value="{{ $option->data['max'] ?? '10' }}" class="form-control" onchange="maxUpdated(this.value)" required="" />
    </div>
    <div class="form-group col-md-6">
        <div class="control-label">Is one time?</div>
        <label class="custom-switch mt-2">
            <input type="checkbox" name="data[is_onetime]" class="custom-switch-input" />
            <span class="custom-switch-indicator"></span>
            <span class="custom-switch-description">Enable to make it a one time fee</span>
        </label>
    </div>
    <div class="form-group col-md-6">
        <label for="data[monthly_price_unit]">Unit Price / 30 days</label>
        <input type="text" name="data[monthly_price_unit]" value="{{ $option->data['monthly_price_unit'] ?? '0' }}" class="form-control" required="" />
        <small class="form-text text-muted">Price of 1 ({{ $option->key }}) unit per 30 days</small>
    </div>
</div>
@endif
<div class="row">
    <div class="col-12 mt-4 justify-end text-right">
        <button class="btn btn-primary" type="submit">Update</button>
    </div>
</div>
</form>

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

    function appendRule(rule)
    {
        var rules = document.getElementById('rules{{ $option->id }}');

        // if rule exists but is a different value, then replace it
        // example: min:3 exists, but min:5 is being added
        // this should also work for other rules like max: and in:
        if (rules.value.includes('min:') && rule.includes('min:')) {
            rules.value = rules.value.replace(/min:\d+/, rule);
            return;
        }

        if (rules.value.includes('max:') && rule.includes('max:')) {
            rules.value = rules.value.replace(/max:\d+/, rule);
            return;
        }

        if (rules.value.includes('in:') && rule.includes('in:')) {
            rules.value = rules.value.replace(/in:[^|]+/, rule);
            return;
        }

        // check if rule already exists
        if (rules.value.includes(rule)) {
            return;
        }

        if (rules.value.length > 0) {
            // make sure string does not end with | 
            if (rules.value.endsWith('|')) {
                rules.value += rule;
            } else {
                rules.value += '|' + rule;
            }
        } else {
            rules.value = rule;
        }

    }

    function minUpdated(value)
    {
        appendRule('min:' + value);
    }

    function maxUpdated(value)
    {
        appendRule('max:' + value);
    }

    function selectOptionsUpdated()
    {
        var options = document.querySelectorAll('input[name^="data[options]"]');
        var filteredOptions = Array.prototype.filter.call(options, function(option) {
            return /\bdata\[options\]\[[^\]]+\]\[value\]/.test(option.name);
        });
        var optionsArray = Array.from(filteredOptions);
        var optionsValues = optionsArray.map(option => option.value);
        var optionsString = optionsValues.join(',');

        appendRule('in:' + optionsString);
    }

</script>

@endsection