@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $form->name }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('tickets.submitTicketForm', $form->slug) }}" id="dynamic-form">
        @csrf

        @foreach($form->fields->sortBy('order') as $field)
            <div class="form-group" 
                 id="field-{{ $field->id }}" 
                 @if($field->dependencies->count()) style="display:none;" @endif
                 data-depends-on="{{ optional($field->dependencies->first())->depends_on_field_id }}"
                 data-expected-value="{{ optional($field->dependencies->first())->expected_value }}"
            >
                <label>{{ $field->label }} @if($field->is_required)*@endif</label>

                @switch($field->type)
                    @case('text')
                        <input type="text" name="{{ $field->name }}" class="form-control" value="{{ old($field->name) }}">
                        @break

                    @case('select')
                        <select name="{{ $field->name }}" class="form-control dependency-source" data-field-id="{{ $field->id }}">
                            <option value="">-- Select --</option>
                            @foreach($field->options as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @break

                    @case('date')
                        <input type="date" name="{{ $field->name }}" class="form-control">
                        @break
                    // Add textarea, checkbox, radio etc as needed
                @endswitch
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary mt-3">Submit</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.dependency-source').forEach(select => {
        select.addEventListener('change', function() {
            const value = this.value;
            const fieldId = this.dataset.fieldId;

            document.querySelectorAll('[data-depends-on="' + fieldId + '"]').forEach(target => {
                const expected = target.dataset.expectedValue;
                if (value === expected) {
                    target.style.display = 'block';
                } else {
                    target.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
