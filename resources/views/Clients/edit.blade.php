@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">{{isset($client) ? 'Edit' : 'Add'}} Client</h3>
    
    <form action="{{ isset($client) ? route('clients.update', $client->id) : route('clients.store') }}" method="POST">
        @csrf
        @if(isset($client))
            @method('PUT') <!-- Use PUT method for updating -->
        @endif
        <div class="row mb-4">
            <div class="col-md-8">
                <!-- Workflow Name -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="workflow_name">Client Name</label>
                        <input type="text" name="client_name" class="form-control" value="{{ old('client_name', @$client->client_name) }}" required>
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ isset($client) && $client->status == 1 ? 'selected' : '' }}>Enabled</option>
                            <option value="0" {{ isset($client) && $client->status == 0 ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{isset($client) ? 'Update' : 'Save'}} Client</button>
    </form>
</div>

@endsection

@push('scripts')
    <script>

        $(document).ready(function() {
            $('.OptionDropdown').select2({
                width: 'resolve',
                tags: true,
                tokenSeparators: [',', '  '],
                placeholder: "Select a state",
                allowClear: true
            });

            $(document).on('change', '.field_row .field_type', function() {
                var field_type = $(this).val();
                if (field_type == 'text') {
                    $(this).closest('.field_row').find('.OptionDropdownBox').addClass('opacity-0');
                    $(this).closest('.field_row').find('.OptionDropdown ').removeAttr('required');
                } else {
                    $(this).closest('.field_row').find('.OptionDropdownBox').removeClass('opacity-0');
                    $(this).closest('.field_row').find('.OptionDropdown').addAttr('required', true);
                }
            });

            // Add field 
            $(document).on('click', '.add_field', function () {
                let newField = $('.field_row:first').closest('.field-group').clone();
                let uniqueId = `new_${Date.now()}`;
                newField.find('input, select').each(function () {
                    if ($(this).is('input')) {
                        $(this).val('');
                        let name = $(this).attr('name');
                        if (name) {
                            $(this).attr('name', name.replace(/\[\d+\]/, '['+uniqueId+']'));
                        }
                    } else if ($(this).is('select')) {
                        $(this).val([]);
                        let name = $(this).attr('name');
                        if (name) {
                            $(this).attr('name', name.replace(/\[\d+\]/,  '['+uniqueId+']'));
                        }
                    }
                });

                newField.find('[id^="collapseOne_"]').each(function() {
                    let currentId = $(this).attr('id');
                    let newId = currentId.replace(/collapseOne_\d+/, `collapseOne_${uniqueId}`);
                    $(this).attr('id', newId);  // Update the ID attribute
                });

                newField.find('.accordion-header button').each(function () {
                    $(this).attr({
                        'data-bs-target': $(this).attr('data-bs-target').replace(/collapseOne_\d+/, `collapseOne_${uniqueId}`),
                        'aria-controls': $(this).attr('aria-controls').replace(/collapseOne_\d+/, `collapseOne_${uniqueId}`)
                    });
                });

                newField.find('.accordion-body .row').not(':first').remove();
              
                newField.find('.select2-container').remove();
                newField.find('.OptionDropdown').removeAttr('aria-hidden data-select2-id');
                newField.find('.OptionDropdown').empty();
                newField.find('.add_field')
                .removeClass('btn-success add_field')
                .addClass('btn-danger remove_field')
                .attr('aria-label', 'Remove')
                .find('i')
                .removeClass('fa-plus')
                .addClass('fa-minus');

                newField.find('select.field_type').val('text');
                newField.find('.OptionDropdownBox').addClass('opacity-0')

              
                newField.find('.OptionDropdown').each(function () {
                    $(this).select2({
                        width: '100%',
                        tags: true,
                        tokenSeparators: [',', '  '],
                        placeholder: "Select options",
                        allowClear: true,
                    });
                });

               
                newField.appendTo('.field_box:last');
            });


            // Remove field
            $(document).on('click', '.remove_field', function () {
                $(this).closest('.field-group').remove();
            });

            $(document).on('click', '.add_option', function () {
                var optionContainer = $(this).closest('.accordion-body');
                var lastOption = optionContainer.find('.row:first');
                var newOption = lastOption.clone();
                let uniqueOptionId = `new_option_${Date.now()}`;
                // Reset the input field
                newOption.find('input').val('');
                
                // Generate a unique ID for the new option
                newOption.find('input').each(function () {
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[options\]\[.*?\]/, `[options][${uniqueOptionId}]`));
                    }
                });

                // Update the button to 'remove_option' for the cloned row
                newOption.find('.add_option')
                    .removeClass('btn-success add_option')
                    .addClass('btn-danger remove_option')
                    .attr('aria-label', 'Remove')
                    .find('i')
                    .removeClass('fa-plus')
                    .addClass('fa-minus');

                 newOption.find('.accordion-body').find('.row:not(:first)').remove();


                // Append the new option row
                optionContainer.append(newOption);
            });

            // Remove process name row
            $(document).on('click', '.remove_option', function () {
                $(this).closest('.row').remove();
            });


            $(document).on('click', '.add_process_name', function () {
                let uniqueOptionId = `new_option_${Date.now()}`;
                let newRow = $('.process_box .row:first').clone();
                newRow.find('input').val('');

                newRow.find('.add_process_name')
                    .removeClass('btn-success add_process_name')
                    .addClass('btn-danger remove_process_name')
                    .attr('aria-label', 'Remove')
                    .find('i')
                    .removeClass('fa-plus')
                    .addClass('fa-minus');

                newRow.find('input').each(function () {
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/process_names\[.*?\]/, `process_names[${uniqueOptionId}]`));
                    }
                });

                $('.process_box').append(newRow);
            });

            // Remove process name row
            $(document).on('click', '.remove_process_name', function () {
                $(this).closest('.row').remove();
            });

            $(document).on('click', '.accordion-header button', function() {

            });

            $(document).on('focus', '.field_type, .field_name, .process_name', function() {
                var field_row = $(this).closest('.field_row');
                if (!field_row.find('.accordion-collapse').hasClass('show')) {
                    field_row.find('.accordion-header button').click();
                    $('.field_row').each(function() {
                        if ($(this).find('.accordion-collapse').hasClass('show')) {
                            $(this).find('.accordion-header button').click();
                        }
                    });
                    $('.field_row').removeClass('active');
                    field_row.addClass('active');
                }
            });

            $('.accordion').on('shown.bs.collapse', function (event) {
                var field_row = $(event.target).closest('.field_row'); // Get the current field_row of the opened accordion
                
                // Close all other open accordion sections
                $('.field_row').each(function () {
                    var current_field_row = $(this);
                    if (current_field_row[0] !== field_row[0]) { // Exclude the current field_row from closing
                        current_field_row.find('.accordion-collapse').collapse('hide');
                    }
                });
                
                // Manage active class
                $('.field_row').removeClass('active'); // Remove 'active' class from all field rows
                field_row.addClass('active'); // Add 'active' class to the current field row
            });


        });


    </script>
@endpush