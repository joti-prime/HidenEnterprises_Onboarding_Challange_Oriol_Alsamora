@php
    $monitor = $monitor ?? null;
@endphp

<div class="form-group">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $monitor->name ?? '') }}" required>
    @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
</div>

<div class="form-group">
    <label for="check_type">Check type</label>
    <select name="check_type" id="check_type" class="form-control @error('check_type') is-invalid @enderror" required
            onchange="document.getElementById('http-fields').style.display = this.value === 'http' ? 'block' : 'none'; document.getElementById('tcp-fields').style.display = this.value === 'tcp' ? 'block' : 'none';">
        @php $currentType = old('check_type', $monitor->check_type ?? 'http'); @endphp
        <option value="http" @selected($currentType === 'http')>HTTP (real web request)</option>
        <option value="tcp" @selected($currentType === 'tcp')>TCP connection test (ping-style reachability check)</option>
    </select>
    @error('check_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
</div>

<div class="form-group">
    <label for="target">
        Target
        <small class="text-muted">— full URL for HTTP (e.g. https://example.com), or hostname/IP for TCP (e.g. example.com)</small>
    </label>
    <input type="text" name="target" id="target" class="form-control @error('target') is-invalid @enderror"
           value="{{ old('target', $monitor->target ?? '') }}" placeholder="https://example.com" required>
    @error('target') <span class="invalid-feedback">{{ $message }}</span> @enderror
</div>

<div id="http-fields" style="{{ ($monitor->check_type ?? 'http') === 'tcp' ? 'display:none;' : '' }}">
    <div class="form-group">
        <label for="expected_status_code">Expected HTTP status code</label>
        <input type="number" name="expected_status_code" id="expected_status_code"
               class="form-control @error('expected_status_code') is-invalid @enderror"
               value="{{ old('expected_status_code', $monitor->expected_status_code ?? 200) }}">
        @error('expected_status_code') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>
</div>

<div id="tcp-fields" style="{{ ($monitor->check_type ?? 'http') === 'tcp' ? '' : 'display:none;' }}">
    <div class="form-group">
        <label for="port">Port</label>
        <input type="number" name="port" id="port" class="form-control @error('port') is-invalid @enderror"
               value="{{ old('port', $monitor->port ?? 80) }}">
        @error('port') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>
</div>

<div class="form-group form-check">
    <input type="hidden" name="is_enabled" value="0">
    <input type="checkbox" name="is_enabled" id="is_enabled" class="form-check-input" value="1"
           @checked(old('is_enabled', $monitor->is_enabled ?? true))>
    <label for="is_enabled" class="form-check-label">Enabled</label>
</div>
