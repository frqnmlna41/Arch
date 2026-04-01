@foreach ($fields as $field)
    <div class="mb-3">
        <label class="form-label">{{ $field['label'] }}</label>
        <input type="{{ $field['type'] ?? 'text' }}"
            class="form-control {{ $errors->has($field['name']) ? 'is-invalid' : '' }}" name="{{ $field['name'] }}"
            value="{{ old($field['name']) }}" {{ $field['required'] ?? '' }}>
        @error($field['name'])
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endforeach
