@props(['name', 'value' => ''])

<div class="input-group">
    <input type="password" class="form-control" name="{{ $name }}" value="{{ $value }}" data-secret-input>
    <button type="button" class="btn btn-outline-secondary" data-toggle-secret>
        <i class="bi bi-eye"></i>
    </button>
</div>
