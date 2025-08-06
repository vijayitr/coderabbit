<div class="filter-select" id="{{ $id }}">
    <div class="selected-option">
        {{ $label }}: <strong>{{ $selectedOption }}</strong> <i class="bi bi-chevron-down chevron"></i>
    </div>
    <div class="options">
        @foreach ($options as $option)
            <div class="option" data-value="{{ $option }}">{{ $option }}</div>
        @endforeach
    </div>
</div>
