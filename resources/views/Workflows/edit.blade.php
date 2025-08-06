@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">{{isset($workflow->id) ? 'Edit' : 'Add'}} Workflow</h3>
    
    <form id="WorkflowForm" action="{{ isset($workflow->id) ? route('workflows.update', $workflow->id) : route('workflows.store') }}" method="POST">
        @csrf
        @method('PUT') <!-- This tells Laravel to perform an update -->

        <div class="row mb-4">
            <!-- Workflow Name -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="workflow_name">Workflow Name</label>
                    <input type="text" name="workflow_name" class="form-control" value="{{ old('workflow_name', @$workflow->workflow_name) }}" required>
                </div>
            </div>

            <!-- Client Name -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="status">Client Name</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($clients as $client)
                            <option value="{{$client->id}}" {{ $client->id == @$workflow->client_id ? 'selected' : '' }}>{{$client->client_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Status -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" class="form-control" required>
                        <option value="1" {{ isset($workflow->status) && $workflow->status == 1 ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ isset($workflow->status) && $workflow->status == 0 ? 'selected' : '' }}>Disabled</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group mb-4">
            <div class="field-group">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <label for="fields" class="ms-5p">Field Name</label>
                        </div>
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
                    <div class="field-group mb-3 draggable">
                        <div class="row field_row {{$key == 0 ? 'active' : ''}}">
                            <div class="col-md-4" >
                                 <div class="form-check mb-2">
                                    <input type="radio" name="primary_field" class="form-check-input primary_field" value="{{$field->id}}" {{ isset($workflow->id) && $field->is_primary || !isset($workflow->id) && $key == 0 ? 'checked' : '' }}>
                                    <i class="bi bi-grip-vertical workflow_field_drag_icon drag-handle"></i>
                                    <input type="text" name="fields[{{ $field->id }}][field_name]" value="{{ old('fields.' . $field->id . '.field_name', $field->field_name) }}" class="form-control field_name w-95 ms-5p" placeholder="Field Name" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select name="fields[{{ $field->id }}][field_type]" class="form-control field_type" required>
                                    <option value="text" {{ $field->field_type == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="dropdown" {{ $field->field_type == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                                    <option value="date" {{ $field->field_type == 'date' ? 'selected' : '' }}>Date</option>
                                    <option value="checkbox" {{ $field->field_type == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="OptionDropdownBox {{ $field->field_type != 'dropdown' ? 'd-none' : '' }}">
                                    <div class="position-relative">
                                        <div class="accordion option_container overflow-auto position-absolute top-0 start-0 end-0">
                                            <div class="accordion-item mb-2">
                                                <h2 class="accordion-header">
                                                    <button class="form-control" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$key}}" aria-expanded="true" aria-controls="collapseOne_{{$key}}">
                                                        <select class="form-select" id="process-select">
                                                            <option value="all" selected>All</option>
                                                        </select>
                                                    </button>
                                                </h2>
                                                <div id="collapseOne_{{$key}}" class="accordion-collapse collapse {{$key == 0 && $field->field_type == 'dropdown' ? 'show' : ''}}" aria-labelledby="headingOne">
                                                    <div class="accordion-body bg-transparent">
                                                        @foreach($field->options as $Okey => $option)
                                                            <div class="row m-0 draggables"  data-process-id="all">
                                                                <div class="col-md-10 mb-2 drag-handles d-flex align-items-center">
                                                                   <i class="bi bi-grip-vertical workflow_field_drag_icon drag-handle"></i>
                                                                   <input type="text" name="fields[{{ $field->id }}][options][{{$option->id}}]" value="{{ old('fields.' . $field->id . '.options.' . $loop->index, $option->option_value) }}" class="form-control OptionDropdown" placeholder="Option Name" {{ $field->field_type == 'dropdown' ? 'required' : '' }}>
                                                                </div>
                                                                 <div class="col-md-1 drag-handle">
                                                                     <button type="button" class="btn {{count($field->options)-1 == $Okey ? 'btn-success add_option' : 'btn-danger remove_option'}}" aria-label="Remove">
                                                                        <i class="fa {{count($field->options)-1 == $Okey ? 'fa-plus' : 'fa-minus'}}"></i>
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
                            </div>
                            <div class="col-md-1">
                                 <button type="button" class="btn {{count($workflow->fields)-1 == $key ? 'btn-success add_field' : 'btn-danger remove_field'}}" aria-label="Remove">
                                    <i class="fa {{count($workflow->fields)-1 == $key ? 'fa-plus' : 'fa-minus'}}"></i>
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
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary qa_fields me-2" data-id="{{$process->id}}" title="Add QA Dynamic Fields">
                        <i class="bi bi-plus-circle"></i>
                    </button>

                    <button type="button" class="btn btn-primary checklist me-2" data-id="{{$process->id}}" title="Checklist">
                        <i class="fa fa-list-alt"></i>
                    </button>

                    <button type="button" class="btn btn-dark activity_script me-2" data-id="{{$process->id}}" title="Script">
                        <i class="bi bi-terminal"></i>
                    </button>

                    <button type="button" class="btn {{count($workflow->processNames) - 1 == $key ? 'btn-success add_process_name' : 'btn-danger remove_process_name'}}" aria-label="Remove" title="{{count($workflow->processNames) - 1 == $key ? 'Add Activity' : 'Remove Activity'}}">
                        <i class="fa {{count($workflow->processNames) - 1 == $key ? 'fa-plus' : 'fa-minus'}}"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">{{isset($workflow->id) ? 'Update' : 'Save'}} Workflow</button>
    </form>
</div>

<x-emptyModal />

@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <script>
        const CKEditorInstances = {};
        $(document).ready(function() {
            // $('.OptionDropdown').select2({
            //     width: 'resolve',
            //     tags: true,
            //     tokenSeparators: [',', '  '],
            //     placeholder: "Select a state",
            //     allowClear: true
            // });

            document.getElementById('process-select').addEventListener('change', function () {
                const selectedId = this.value;
                document.querySelectorAll('.option-panel').forEach(panel => {
                    if (panel.getAttribute('data-process-id') === selectedId) {
                        panel.classList.remove('d-none');
                    } else {
                        panel.classList.add('d-none');
                    }
                });
            });

            $(document).on('change', '.field_row .field_type', function() {
                const fieldRow = $(this).closest('.field_row');
                const isSimpleField = ['text', 'date', 'checkbox'].includes($(this).val());
                
                fieldRow.find('.OptionDropdownBox').toggleClass('d-none', isSimpleField);
                fieldRow.find('.OptionDropdown').attr('required', !isSimpleField);
            });


            // Add field 
            $(document).on('click', '.add_field', function () {
                let newField = $('.field_row:first').closest('.field-group').clone();
                let uniqueId = `new_${Date.now()}`;

                // Update input and select fields
                newField.find('input, select').each(function () {
                    $(this).val(''); // Clear the value
                    let name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/g, `[${uniqueId}]`));
                    }
                });

                newField.find('.accordion-collapse input').attr('required', false);

                // Update accordion IDs and attributes
                newField.find('.accordion-collapse').each(function () {
                    let newId = `collapseOne_${uniqueId}`;
                    $(this).attr('id', newId);
                    $(this).closest('.accordion-item').find('.accordion-header button')
                        .attr('data-bs-target', `#${newId}`)
                        .attr('aria-controls', newId);
                });

                // Reset specific UI elements
                newField.find('.accordion-body .row').not(':first').remove();
                newField.find('.select2-container').remove(); // Remove Select2 wrapper
                newField.find('.OptionDropdown').empty().removeAttr('aria-hidden data-select2-id');
                newField.find('.OptionDropdownBox').addClass('d-none');
                newField.find('select.field_type').val('text');
                newField.find('.primary_field').prop('checked', false).val(uniqueId);

                $(this).closest('.field_box').find('.add_field')
                .removeClass('btn-success add_option')
                .addClass('btn-danger remove_option')
                .attr('aria-label', 'Remove')
                .find('i').removeClass('fa-plus').addClass('fa-minus');

                // Update the Add button to Remove button
                newField.find('.remove_field')
                    .addClass('btn-success add_field')
                    .removeClass('btn-danger remove_field')
                    .attr('aria-label', 'Remove')
                    .find('i').addClass('fa-plus').removeClass('fa-minus');

                // Append the new field to the container
                newField.appendTo('.field_box:last');
            });



            // Remove field
            $(document).on('click', '.remove_field', function () {
                $(this).closest('.field-group').remove();
            });

            $(document).on('click', '.add_option', function () {
                const optionContainer = $(this).closest('.accordion-body');
                const newOption = optionContainer.find('.row:first').clone().find('input').val('').end();
                const uniqueOptionId = `new_option_${Date.now()}`;

                newOption.find('input').attr('name', function (i, name) {
                    return name.replace(/\[options\]\[.*?\]/, `[options][${uniqueOptionId}]`);
                });

                 $(this).closest('.accordion-body').find('.add_option')
                    .removeClass('btn-success add_option')
                    .addClass('btn-danger remove_option')
                    .attr('aria-label', 'Remove')
                    .find('i').removeClass('fa-plus').addClass('fa-minus');

                newOption.find('.remove_option')
                    .removeClass('btn-danger remove_option')
                    .addClass('btn-success add_option')
                    .attr('aria-label', 'Remove')
                    .find('i').addClass('fa-plus').removeClass('fa-minus');

                optionContainer.append(newOption);
            });


            // Remove process name row
            $(document).on('click', '.remove_option', function () {
                $(this).closest('.row').remove();
            });


            $(document).on('click', '.add_process_name', function () {
                const uniqueOptionId = `new_option_${Date.now()}`;
                const newRow = $('.process_box .row:first').clone().find('input').val('').end();
                $(this).closest('.process_box').find('.add_process_name')
                    .removeClass('btn-success add_process_name')
                    .addClass('btn-danger remove_process_name')
                    .attr('aria-label', 'Remove')
                    .find('i').removeClass('fa-plus').addClass('fa-minus');
                    
                newRow.find('.remove_process_name')
                    .addClass('btn-success add_process_name')
                    .removeClass('btn-danger remove_process_name')
                    .attr('aria-label', 'Remove')
                    .find('i').addClass('fa-plus').removeClass('fa-minus');

                newRow.find('input').attr('name', function (i, name) {
                    return name.replace(/process_names\[.*?\]/, `process_names[${uniqueOptionId}]`);
                });

                $('.process_box').append(newRow);
            });

            $(document).on('click', '.add_process_item', function () {
                const uniqueOptionId = `new`;

                // Clone the first table row and clear its values
                const newRow = $('.process_item_box table tbody tr:first').clone().find('input').val('').end();

                // Update the button to remove row instead of adding
                newRow.find('.add_process_item')
                    .removeClass('btn-success add_process_item')
                    .addClass('btn-danger remove_process_item')
                    .attr('aria-label', 'Remove')
                    .find('i').removeClass('fa-plus').addClass('fa-minus');

                // Update the input name to use a unique ID
                newRow.find('input').attr('name', function (i, name) {
                    return 'process_item[new][]';
                });

                // Update the first column's index (for # column)
                newRow.find('td:first').text($('.process_item_box table tbody tr').length + 1);

                // Append the new row to the table body
                $('.process_item_box table tbody').append(newRow);
            });

            $(document).on('click', '.remove_process_item', function () {
                // Remove the corresponding row
                $(this).closest('tr').remove();

                // Re-index the remaining rows in the table
                $('.process_item_box table tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            });




            // Remove process name row
            $(document).on('click', '.remove_process_name', function () {
                $(this).closest('.row').remove();
            });

            $(document).on('click', '.accordion-header button', function() {

            });

            $(document).on('focus', '.field_type, .field_name, .process_name', function () {
                var fieldRow = $(this).closest('.field_row');

                // Collapse all accordions, except the one related to the current fieldRow
                $('.accordion-collapse.show').not(fieldRow.find('.accordion-collapse')).collapse('hide');

                if (fieldRow.length) {
                    // Expand the current accordion and mark it as active
                    $(document).find('.field_row').removeClass('active');
                    fieldRow.find('.accordion-collapse').collapse('show');
                    fieldRow.addClass('active');
                }
            });

            $(document).on('click', '.checklist', function() {
                var activity_name = $(this).closest('.row').find('.process_name').val();
                var id = $(this).attr('data-id');
                $('#emptyModalLabel').html(activity_name);
                $('#emptyModal .modal-body').addClass('border border-secondary-subtle');

                $('#emptyModal .modal-footer').append('<button type="button" class="btn btn-primary checkList_submit_btn float-end">Submit</button>');
                $('#emptyModal').modal('show');
                $.ajax({
                        url: '{{ route("workflows.getChecklistItems") }}',
                        type: 'GET',
                        data: { id:id , _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.status) {
                                $('#emptyModal .modal-body').html(response.html);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON.message, 'Error');
                        }
                });
            });

            $(document).on('click', '.activity_script', function() {
                var activity_name = $(this).closest('.row').find('.process_name').val();
                var id = $(this).attr('data-id');
                $('#emptyModalLabel').html(activity_name + ' Script');
                $('#emptyModal .modal-body').addClass('border border-secondary-subtle');

                $('#emptyModal .modal-footer').append('<button type="button" class="btn btn-primary script_submit_btn float-end" data-id="'+id+'">Submit</button>');
                $('#emptyModal').modal('show');
                $.ajax({
                        url: '{{ route("workflows.getScript") }}',
                        type: 'GET',
                        data: { id:id , _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.status) {
                                $('#emptyModal .modal-body').html('<textarea id="script_ckeditor" name="script_ckeditor" class="ckeditor" rows="10" placeholder="Write your script here...">'+response.html+'</textarea>');
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON.message, 'Error');
                        }
                });
            });


            $('.accordion').on('shown.bs.collapse', function (event) {
                var fieldRow = $(event.target).closest('.field_row');

                // Collapse other open sections and manage active class
                $('.field_row').not(fieldRow).find('.accordion-collapse').collapse('hide');
                $('.field_row').removeClass('active');
                fieldRow.addClass('active');
            });

            $(document).on('submit','#ProcessItemForm', function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message, 'Success');
                            $('#emptyModal').modal('hide');
                           
                        }
                    },
                    error: function (xhr, status, error) {
                        toastr.error(xhr.responseJSON.message, 'Error');
                    }
                });
            });

            $(document).on('click', '.checkList_submit_btn', function() {
                $('#ProcessItemForm').trigger('submit');
            });

            $(document).on('click', '.script_submit_btn', function() {
                var id = $(this).attr('data-id');
                const editorContent = CKEditorInstances['script_ckeditor'].getData();
                const formData = new FormData();
                formData.set('script', editorContent);
                var url = '{{ route("workflows.storeScript",'') }}';
                $.ajax({
                    url: url+'/'+id,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status) {
                            toastr.success(response.message, 'Success');
                            $('#emptyModal').modal('hide');
                        }
                    },
                    error: function (xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Something went wrong', 'Error');
                    }
                });
            });

            $(document).on('click', '.checkList_submit_btn', function() {
                $('#ProcessItemForm').trigger('submit');
            });

        });
    
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('#emptyModal .modal-footer').find('.script_submit_btn, .checkList_submit_btn').remove();
    });

    function initCkeditors(scope = document) {
        scope.querySelectorAll('.ckeditor').forEach(textarea => {
            const name = textarea.getAttribute('name');
            if (!textarea.classList.contains('ckeditor-initialized')) {
                ClassicEditor
                    .create(textarea)
                    .then(editor => {
                        editor.editing.view.change(writer => {
                            writer.setStyle('min-height', '300px', editor.editing.view.document.getRoot());
                        });
                        textarea.classList.add('ckeditor-initialized');
                        CKEditorInstances[name] = editor;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        });
    }

    $(document).ajaxComplete(function(event, jqXHR, ajaxOptions) {
        initCkeditors();
    });

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

    $(function () {
        let $draggeds = null;

        $(".drag-handles").on("mousedown", function (e) {
            e.preventDefault();
            $draggeds = $(this).closest(".draggables");
            $draggeds.addClass("draggings");

            $(document).on("mousemove.drag", function () {
                $(".draggables").each(function () {
                    const $target = $(this);
                    if ($target[0] !== $draggeds[0] && $target.is(":hover")) {
                        if ($draggeds.index() < $target.index()) {
                            $target.after($draggeds);
                        } else {
                            $target.before($draggeds);
                        }
                    }
                });
            });

            $(document).on("mouseup.drag", function () {
                $(document).off(".drag");
                if ($draggeds) $draggeds.removeClass("draggings");
                 addDependencyList();
                $draggeds = null;
            });
        });
    });

    $(document).on('click','.qa_fields', function() {
        if (typeof resetEmptyModal === 'function') {
            resetEmptyModal();
        }
        var id = $(this).data('id');
        var activity_name = $(this).closest('.row').find('.process_name').val();
        // const classList = $(this).attr('class');
        // level_id = classList.includes('allocation_popup') ? $(this).data('id') : level_id;
        getQCFields(id, activity_name);
    });


    function getQCFields(id, activity_name) {
        $('#emptyModalLabel').html(activity_name+' QC Dynamic Fields');
        $('#emptyModal .modal-body').addClass('border border-secondary-subtle');
        $('#emptyModal .modal-footer').append('<button type="button" class="btn btn-primary qc_submit_btn float-end">Submit</button>');
        $('#emptyModal').modal('show');
        var url = '{{ route("workflows.getProcessQCFields", '') }}';
        $.ajax({
                url: url+'/'+id,
                type: 'GET',
                success: function(response) {
                    if (response.status) {
                        $('#emptyModal .modal-body').html(response.html);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
        }); 
    }
</script>
@endpush