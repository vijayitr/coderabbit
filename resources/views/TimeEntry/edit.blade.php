@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Edit Workflow</h3>
    
    <form action="{{ route('workflows.update', $workflow->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- This tells Laravel to perform an update -->

        <div class="row mb-4">
            <!-- Workflow Name -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="workflow_name">Workflow Name</label>
                    <input type="text" name="workflow_name" class="form-control" value="{{ old('workflow_name', $workflow->workflow_name) }}" required>
                </div>
            </div>

            <!-- Client Name -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="status">Client Name</label>
                    <select name="client_id" class="form-control" required>
                        @foreach($clients as $client)
                            <option value="{{$client->id}}" {{ $client->id == $workflow->client_id ? 'selected' : '' }}>{{$client->client_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="1" {{ $workflow->status == 1 ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ $workflow->status == 0 ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group mb-4">
            <div class="field-group">
                <div class="row">
                    <div class="col-md-4">
                        <label for="fields">Field Name</label>

                    </div>
                    <div class="col-md-2">
                       <label for="fields">Field Type</label>
                    </div>
                    <div class="col-md-5">
                       <label for="fields">Field Options</label>
                    </div>
                </div>
            </div>
            <div class="field_box">
                @foreach($workflow->fields as $key => $field)
                    <div class="field-group mb-3">
                        <div class="row field_row {{$key == 0 ? 'active' : ''}}">
                            <div class="col-md-4">
                                <input type="text" name="fields[{{ $field->id }}][field_name]" value="{{ old('fields.' . $field->id . '.field_name', $field->field_name) }}" class="form-control field_name" placeholder="Field Name" required>
                            </div>
                            <div class="col-md-2">
                                <select name="fields[{{ $field->id }}][field_type]" class="form-control field_type" required>
                                    <option value="text" {{ $field->field_type == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="dropdown" {{ $field->field_type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                </select>
                            </div>
                            <div class="col-md-5 OptionDropdownBox {{ $field->field_type == 'text' ? 'opacity-0' : '' }}">
                                <div class="position-relative">
                                    <div class="accordion option_container overflow-auto position-absolute top-0 start-0 end-0">
                                        <div class="accordion-item mb-2">
                                            <h2 class="accordion-header" id="headingOne">
                                                <button class="form-control" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$key}}" aria-expanded="true" aria-controls="collapseOne_{{$key}}">
                                                    Options
                                                </button>
                                            </h2>
                                            <div id="collapseOne_{{$key}}" class="accordion-collapse collapse {{$key == 0 ? 'show' : ''}}" aria-labelledby="headingOne">
                                                <div class="accordion-body bg-transparent">
                                                    @foreach($field->options as $Okey => $option)
                                                        <div class="row m-0">
                                                            <div class="col-md-10 mb-2">
                                                                <input type="text" name="fields[{{ $field->id }}][options][{{$option->id}}]" value="{{ old('fields.' . $field->id . '.options.' . $loop->index, $option->option_value) }}" }}" class="form-control" placeholder="Option Name" 1required>
                                                            </div>
                                                             <div class="col-md-1">
                                                                 <button type="button" class="btn {{$Okey == 0 ? 'btn-success add_option' : 'btn-danger remove_option'}}" aria-label="Remove">
                                                                    <i class="fa {{$Okey == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>                                
                                </div>
                            </div>
                            <div class="col-md-1">
                                 <button type="button" class="btn {{$key == 0 ? 'btn-success add_field' : 'btn-danger remove_field'}}" aria-label="Remove">
                                    <i class="fa {{$key == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        
        <div class="form-group mb-4 process_box">
            <label for="process_names">Process Names</label>
            @foreach($workflow->processNames as $key => $process)
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <input type="text" name="process_names[{{$process->id}}]" value="{{ old('process_names.' . $loop->index, $process->process_name) }}" class="form-control process_name" placeholder="Process Name" required>
                    </div>
                </div>
                <div class="col-md-1">
                     <button type="button" class="btn {{$key == 0 ? 'btn-success add_process_name' : 'btn-danger remove_process_name'}}" aria-label="Remove">
                        <i class="fa {{$key == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Update Workflow</button>
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