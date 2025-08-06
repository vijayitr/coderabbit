<div class="mb-3">
    <label for="name">Name</label>
    <input type="text" name="name" value="{{ old('name', $qc_parameter->name ?? '') }}" class="form-control" required>
</div>

<div class="mb-3">
    <label for="weight">Weight</label>
    <input type="number" step="0.01" name="weight" value="{{ old('weight', $qc_parameter->weight ?? '') }}" class="form-control" required>
</div>

<div class="mb-3">
    <label for="order">Order</label>
    <input type="number" name="order" value="{{ old('order', $qc_parameter->order ?? 0) }}" class="form-control">
</div>

<div class="mb-3">
    <label for="status">Status</label>
    <select name="status" class="form-control">
        <option value="1" {{ old('status', $qc_parameter->status ?? 1) == 1 ? 'selected' : '' }}>Active</option>
        <option value="0" {{ old('status', $qc_parameter->status ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
    </select>
</div>
