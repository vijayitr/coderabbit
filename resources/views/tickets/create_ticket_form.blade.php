@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Form: {{ $form->name }}</h2>
    <h5 class="fw-normal mb-3">{{ $form->form_details }}</h5>
    <form method="POST" action="{{ route('tickets.form-fields.submit', $form->slug) }}">
        @csrf

        <div id="field-group-wrapper">
            @if($form->fields->isNotEmpty())
                @foreach($form->fields as $key => $field)
                    <x-admin-ticket-form-fields :data="$field" :index="$key" />
                @endforeach
            @else
                <x-admin-ticket-form-fields :data="[]"  :index="0"/>
            @endif
        </div>

        <button type="button" class="btn btn-secondary" onclick="addFieldGroup()">+ Add More Field</button>
        <button type="submit" class="btn btn-primary ms-2">Save Fields</button>
    </form>

</div>
@endsection

@push('scripts')
<script>
let fieldIndex = 1;

$(function () {
    let $dragged = null;

    $(".drag-handle").on("mousedown", function (e) {
        e.preventDefault();
        $dragged = $(this).closest(".draggable");
        $dragged.addClass("dragging");

        $(document).on("mousemove.drag", function () {
            $(".draggable").each(function () {
                const $target = $(this);
                if ($target[0] !== $dragged[0] && $target.is(":hover")) {
                    if ($dragged.index() < $target.index()) {
                        $target.after($dragged);
                    } else {
                        $target.before($dragged);
                    }
                }
            });
        });

        $(document).on("mouseup.drag", function () {
            $(document).off(".drag");
            if ($dragged) $dragged.removeClass("dragging");
             addDependencyList();
            $dragged = null;
        });
    });
});

function addFieldGroup() {
    fieldIndex = Date.now();
    // $(`input[name="field_id[${fieldIndex}]"]`).length
    $(document).find(".dependency_select2").each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }
    });
    const wrapper = document.getElementById('field-group-wrapper');
    const original = wrapper.querySelector('.field-group');
    const clone = original.cloneNode(true);
    const $clone = $(clone);
    $clone.find('.remove_checklist').each(function() {
        $(this).closest('.checklist-item').remove();
    });

    $clone.find('.remove_radio').each(function() {
        $(this).closest('.radio-item').remove();
    });

    $clone.find('.remove_dropdown').each(function() {
        $(this).closest('.ticket-dropdown-item').remove();
    });

    // Replace all collapse IDs and related attributes dynamically
    $clone.find('[id^="collapseOne_"]').each(function () {
        const oldId = $(this).attr('id');
        const type = oldId.includes('dropdown') ? 'dropdown' : oldId.includes('checklist') ? 'checklist' : 'collapse';
        const newId = (type === 'dropdown') ? 'dropdown_col_' + fieldIndex :
                       (type === 'checklist') ? 'checklist_col_' + fieldIndex :
                       (type === 'radio') ? 'radio_col_' + fieldIndex :
                       'collapse_col_' + fieldIndex;
        $(this).attr('id', newId);

        // Update aria-controls
        $clone.find('[aria-controls="' + oldId + '"]').attr('aria-controls', newId);

        // Update data-bs-target
        $clone.find('[data-bs-target="#' + oldId + '"]').attr('data-bs-target', '#' + newId);
    });

    $clone.find('[id^="collapseField_"]').each(function () {
        const oldId = $(this).attr('id');
        const type = oldId.includes('dropdown') ? 'dropdown' : oldId.includes('checklist') ? 'checklist' : 'collapse';
        const newId = 'collapseField_' + fieldIndex;
        $(this).attr('id', newId);

        // Update aria-controls
        $clone.find('[aria-controls="' + oldId + '"]').attr('aria-controls', newId);

        // Update data-bs-target
        $clone.find('[data-bs-target="#' + oldId + '"]').attr('data-bs-target', '#' + newId);
    });



    $clone.find('input, select').each(function () {
        const el = this;

        if (el.name.startsWith('dependency')) {
            let newId = Date.now();
            el.name = `dependency[${fieldIndex}][${newId}]`;
            el.value = '';
        } else if (el.name.startsWith('is_required')) {
            el.name = `is_required[${fieldIndex}]`;
            el.checked = false;
        } else if (el.name.startsWith('checklist')) {
            el.name = `checklist[${fieldIndex}][]`;
            el.value = '';
        } else if (el.name.startsWith('dropdown')) {
            el.name = `dropdown[${fieldIndex}][]`;
            el.value = '';
        } else if (el.name.startsWith('radio')) {
            el.name = `radio[${fieldIndex}][]`;
            el.value = '';
        } else if (el.name.startsWith('field_id')) {
            el.name = `field_id[${fieldIndex}]`;
            el.value = '';
        } else {
            const base = el.name.split('[')[0];
            el.name = `${base}[${fieldIndex}]`;
            el.value = '';
        }
    });

    $(wrapper).append($clone);
    // fieldIndex++;

    $(document).find('.ticket_field_close').each(function(index){
        if (index != 0) {
            $(this).removeClass('d-none');
        }
    });
    isChecklist()
    isDropdown();
    isRadio();
    isText();
    addDependencyList();
}

function isChecklist() {
    $(document).find('.field-type-select').each(function(index){
        const $select = $(this);
        const value = $select.val();
        const $wrapper = $select.closest('.field-group-box').find('.checklist-options');
        if (value === 'checklist') {
            $wrapper.removeClass('d-none');
            $wrapper.find('.checklist-option').attr('required',true);
        } else {
            $wrapper.find('.checklist-option').removeAttr('required');
            $wrapper.addClass('d-none');
        }
    });
}

function isRadio() {
    $(document).find('.field-type-select').each(function(index){
        const $select = $(this);
        const value = $select.val();
        const $wrapper = $select.closest('.field-group-box').find('.radio-options');
        if (value === 'radio') {
            $wrapper.removeClass('d-none');
            $wrapper.find('.radio-option').attr('required',true);
        } else {
            $wrapper.find('.radio-option').removeAttr('required');
            $wrapper.addClass('d-none');
        }
    });
}

function isText() {
    $(document).find('.field-type-select').each(function(index){
        const $select = $(this);
        const value = $select.val();
        const $wrapper = $select.closest('.field-group-box').find('.text-options');
        if (value === 'text') {
            $wrapper.removeClass('d-none');
        } else {
            $wrapper.find('.radio-option').prop('checked', false);
            $wrapper.addClass('d-none');
        }
    });
}

function isDropdown() {
    $(document).find('.field-type-select').each(function(index){
        const $select = $(this);
        const value = $select.val();
        const $wrapper = $select.closest('.field-group-box').find('.dropdown-options');
        if (value === 'dropdown') {
            $wrapper.removeClass('d-none');
            $wrapper.find('.dropdown-option').attr('required',true);
        } else {
            $wrapper.find('.dropdown-option').removeAttr('required');
            $wrapper.addClass('d-none');
        }
    });
}
// New input row
function getOptionTemplate(btn_type) {
    var btn_status = btn_type == 'add';
    var target_class = btn_status ? 'add-option-btn' : 'remove-option-btn'; 
    var btn_class = btn_status ? 'btn-success' : 'btn-danger'; 
    var btn_icon = btn_status ? 'fa-plus' : 'fa-minus'; 
    var btn_hidden = btn_status ? 'd-none' : ''; 
    var title = '';
    if (btn_status) {
        title = `<div class="col-12 mt-2 field_option d-none">
            <label for="process_names">Dropdown Options</label>
        </div>`;
    }
    return `<div class="col-12 options-wrapper ${btn_hidden}"><div class="row">`+title + `
        <div class="col-6 mt-2 field_option">
            <input type="text" name="options[${fieldIndex}][]" class="form-control mt-0" placeholder="Option">
        </div>
        <div class="col-3 mt-2 field_option">
            <button type="button" class="btn ${btn_class} ${target_class}" aria-label="Add">
                <i class="fa ${btn_icon}"></i>
            </button>
        </div></div></div>`;
}

// Checklist Operations
$(document).on('click', '.add_checklist', function () {
    let wrapper = $(this).closest('.accordion-body');
    let item = $(this).closest('.checklist-item');
    let clone = item.clone();

    // Clear input value
    clone.find('input').val('');

    // Update name attribute with new ID
    clone.find('input').each(function () {
        let name = $(this).attr('name');
        let newId = Date.now();

        // Replace the last numeric key inside square brackets
        let updatedName = name.replace(/\[\d+\]$/, '[' + newId + ']');
        $(this).attr('name', updatedName);
    });

    // Convert add button to remove
    clone.find('.add_checklist')
        .removeClass('add_checklist btn-success')
        .addClass('remove_checklist btn-danger')
        .find('.fa-plus')
        .removeClass('fa-plus')
        .addClass('fa-minus');

    // Append the cloned item
    wrapper.append(clone);
});

$(document).on('click', '.remove_checklist', function () {
    $(this).closest('.checklist-item').remove();
});

// Radio Operations
$(document).on('click', '.add_radio', function () {
    let wrapper = $(this).closest('.accordion-body');
    let item = $(this).closest('.radio-item');
    let clone = item.clone();

    // Clear input value
    clone.find('input').val('');

    // Update name attribute with new ID
    clone.find('input').each(function () {
        let name = $(this).attr('name');
        let newId = Date.now();

        // Replace the last numeric key inside square brackets
        let updatedName = name.replace(/\[\d+\]$/, '[' + newId + ']');
        $(this).attr('name', updatedName);
    });

    // Convert add button to remove
    clone.find('.add_radio')
        .removeClass('add_radio btn-success')
        .addClass('remove_radio btn-danger')
        .find('.fa-plus')
        .removeClass('fa-plus')
        .addClass('fa-minus');

    // Append the cloned item
    wrapper.append(clone);
});

$(document).on('click', '.remove_radio', function () {
    $(this).closest('.radio-item').remove();
});

// Dropdown Operations
$(document).on('click', '.add_dropdown', function () {
    let wrapper = $(this).closest('.accordion-body');
    let item = $(this).closest('.ticket-dropdown-item');
    let clone = item.clone();

    // Clear input value
    clone.find('input').val('');

    // Update name attribute to have a new index (replace last key)
    clone.find('input').each(function () {
        let name = $(this).attr('name');
        let newId = Date.now();

        // Replace the last number between brackets
        let updatedName = name.replace(/\[\d+\]$/, '[' + newId + ']');
        $(this).attr('name', updatedName);
    });

    // Change add button to remove
    clone.find('.add_dropdown')
        .removeClass('add_dropdown btn-success')
        .addClass('remove_dropdown btn-danger')
        .find('.fa-plus')
        .removeClass('fa-plus')
        .addClass('fa-minus');

    // Append the cloned row
    wrapper.append(clone);
});


$(document).on('click', '.remove_dropdown', function () {
    $(this).closest('.ticket-dropdown-item').remove();
});

$(document).on('change','.field-name, .field-type-select, .dropdown-option, .checklist-option, .radio-option', function(){
    addDependencyList();
});

// Append to the options wrapper
// $('.options-wrapper').append(getOptionTemplate('add'));

// $(document).on('click', '.add-option-btn', function() {
//     $(this).closest('.field-group').find('.field-group-box').append(getOptionTemplate('remove'));
// });

$(document).on('click', '.remove-option-btn', function() {
    $(this).closest('.options-wrapper').remove();
});

$(document).on('click', '.ticket_field_close', function() {
    $(this).closest('.field-group').remove();
});

$(document).on('change', '.field-type-select', function() {
    isChecklist();
    isDropdown();
    isRadio();
    isText();
});

function addDependencyList() {
    $(document).find(".dependency_select2").each(function (index, element) {
        const $select = $(this);

        // Add Title on Header
        const currentFieldGroup = $select.closest('.field-group');
        const headerFieldName = currentFieldGroup.find('.field-name').val() || "-";
        currentFieldGroup.find('.head_s_no').html((index+1)+'.');
        currentFieldGroup.find('.head_field_name').html(headerFieldName);

        // 
        var currentValues = $select.val();
        var depValue = $(this).attr('value');
        if (!currentValues || currentValues.length === 0) {
            try {
                const parsed = JSON.parse(depValue);
                if (Array.isArray(parsed)) {
                    currentValues = parsed;  // Set the value in Select2
                }
            } catch (e) {
            }
        }
        // Destroy existing Select2 instance (if any)
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2('destroy');
        }

        let dependencyOptionsHtml = '';
        const currentGroup = $select.closest('.field-group');

        // Traverse previous field groups from top to current
        currentGroup.prevAll('.field-group').get().reverse().forEach(group => {
            const $group = $(group);
            const fieldType = $group.find('.field-type-select').val();
            const fieldName = $group.find('.field-name').val() || "Unnamed";
            let groupOptionsHtml = '';

            if (fieldType === 'dropdown') {
                const $options = $group.find('.accordion-item .dropdown-option');
                if ($options.length) {
                    groupOptionsHtml += `<optgroup label="${fieldName}">`;
                    $options.each(function () {
                        const id = $(this).data('option_id');
                        const value = $(this).val();
                        groupOptionsHtml += `<option class="dropdown_dependency" value="${id}">${value}</option>`;
                    });
                    groupOptionsHtml += `</optgroup>`;
                }
            } else if (fieldType === 'checklist') {
                const $options = $group.find('.accordion-item .checklist-option');
                if ($options.length) {
                    groupOptionsHtml += `<optgroup label="${fieldName}">`;
                    $options.each(function () {
                        const id = $(this).data('option_id');
                        const value = $(this).val();
                        groupOptionsHtml += `<option class="checklist_dependency" value="${id}">${value}</option>`;
                    });
                    groupOptionsHtml += `</optgroup>`;
                }
            } else if (fieldType === 'radio') {
                const $options = $group.find('.accordion-item .radio-option');
                if ($options.length) {
                    groupOptionsHtml += `<optgroup label="${fieldName}">`;
                    $options.each(function () {
                        const id = $(this).data('option_id');
                        const value = $(this).val();
                        groupOptionsHtml += `<option class="radio_dependency" value="${id}">${value}</option>`;
                    });
                    groupOptionsHtml += `</optgroup>`;
                }
            } else {
                // const id = $group.find('.field_id').val() || fieldName;
                // groupOptionsHtml += `<option value="${id}">${fieldName}</option>`;
                // groupOptionsHtml += ``;
            }

            dependencyOptionsHtml += groupOptionsHtml;
        });

        // Inject new options into the <select>
        $select.html(dependencyOptionsHtml);
        if (currentValues && currentValues.length > 0) {
            $select.val(currentValues);
        }
        // Re-initialize Select2
        const isMultiple = $select.prop("multiple");
        $select.select2({
            closeOnSelect: !isMultiple,
            placeholder: $select.data('placeholder') || "Select an option",
            allowClear: true
        });
    });
}

// $('.accordion-collapse').on('show.bs.collapse', function () {
//      $(document).find(".dependency_select2").each(function (index, element) {
//         const $select = $(this);
//         const isMultiple = $select.prop("multiple");
//        // Destroy existing Select2 instance (if any)
//         if ($select.hasClass("select2-hidden-accessible")) {
//             $select.select2('destroy');
//         }
//         $select.select2({
//             closeOnSelect: !isMultiple,
//             placeholder: $select.data('placeholder') || "Select an option",
//             allowClear: true
//         });
//     });
// });

$('.accordion-collapse').on('show.bs.collapse', function () {
    const $currentAccordion = $(this); // This is the currently opening accordion panel

    $currentAccordion.find(".dependency_select2").each(function () {
        const $select = $(this);
        const isMultiple = $select.prop("multiple");

        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2('destroy');
        }

        $select.select2({
            closeOnSelect: !isMultiple,
            placeholder: $select.data('placeholder') || "Select an option",
            allowClear: true
        });
    });
});


$(document).ready(function() {
    addDependencyList();
});
</script>

@endpush
