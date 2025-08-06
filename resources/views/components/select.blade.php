
<div class="filter-item">
    <div class="filter-icon">
        <img src="{{ $iconImg }}" alt="{{ $label }}">
    </div>
    <div class="w-100">
        <div class="select-menu">
            <input type="hidden" name="{{ $name }}" value="">
            <label class="dropdown-label">{{ $label }}</label>
            <div class="select-btn p-0 bg-transparent">
                <span class="sBtn-text">Select</span>
                <i class="bi bi-caret-down-fill"></i>
            </div>

            <ul class="option_list">
                <li class="list_option_search">
                    <input type="text" class="form-control" placeholder="Search">
                </li>
                @foreach($options as $key => $value)
                    <li class="list_option" value="{{ $value->id }}">
                        <span class="option-text">{{ $value->value }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>