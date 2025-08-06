@if (isset($global_qc_field) && !$global_qc_field->isEmpty())
<div class="globalQC">
	<div class="field-group">
		<!-- <h5 class="ms-2"></h5> -->
		<div class="d-flex align-items-center gap-2">
			<h5 class="ms-2 mb-0" for="qcToggle">Global QC Fields</h5>
		@if(isset($qc_status))
		  <div class="form-check form-switch m-0">
		    <input class="form-check-input" type="checkbox" {{!$qc_status ? "" : "checked"}} id="qcToggle" data-processId="{{$process_id}}">
		  </div>
		 @endif
		</div>

	    <div class="row global_qc_box {{isset($qc_status) && !$qc_status ? 'd-none' : ''}}">
	        <div class="col-md-5">
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
	<div class="field_box global_qc_box {{isset($qc_status) && !$qc_status ? 'd-none' : ''}}">
		@if ($global_qc_field->isEmpty())
		    <p class="text-muted">No Global QC Fields available.</p>
		@else
		    @foreach ($global_qc_field as $key => $field)
		<div class="field-group mb-3 draggable">
		    <div class="row active">
		        <div class="col-md-5">
		            <div class="form-check mb-2">
		                <input type="text" name="" class="form-control field_name w-95 ms-5p" placeholder="Field Name" required="" readonly disabled value="{{$field->field_name}}">
		            </div>
		        </div>
		        <div class="col-md-2">
		            <select name="" class="form-control field_type" required="" readonly disabled >
		                <option {{$field->field_type == 'text' ? 'selected' : ''}} value="text">Text</option>
		                <option value="dropdown" {{$field->field_type == 'dropdown' ? 'selected' : ''}}>Dropdown</option>
		                <option value="date" {{$field->field_type == 'date' ? 'selected' : ''}}>Date</option>
		                <option value="checkbox" {{$field->field_type == 'checkbox' ? 'selected' : ''}}>Checkbox</option>
		            </select>
		        </div>
		        <div class="col-md-5">
		            <div class="OptionDropdownBox {{ $field->field_type != 'dropdown' ? 'd-none' : '' }}">
		                <div class="position-relative">
		                    <div class="accordion option_container overflow-auto position-absolute top-0 start-0 end-0">
		                        <div class="accordion-item mb-2">
		                            <h2 class="accordion-header"><button class="form-control collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_0" aria-expanded="false" aria-controls="collapseOne_0">Options</button></h2>
		                            <div id="collapseOne_0" class="accordion-collapse collapse" aria-labelledby="headingOne" style="">
		                                <div class="accordion-body bg-transparent">
		                                	@php
											    $options = $field->options->isEmpty()
											        ? collect([(object)['id' => null, 'option_text' => '']])
											        : $field->options;
											@endphp
	                                		@foreach($options as $Okey => $option) 
			                                    <div class="row m-0 draggables">
			                                        <div class="col-md-12 mb-2 d-flex align-items-center">
			                                            <input type="text" name="" class="form-control OptionDropdown" placeholder="Option Name" value="{{$option->option_text}}" readonly disabled>	
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
		    </div>
		</div>
			@endforeach
		@endif
	</div>
</div>
@endif

<div class="field-group">
	<h5 class="ms-2">{{isset($title) ? $title : 'QC Fields'}}</h5>
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
<form id="WorkflowQcForm" action="{{ $submit_url }}" method="POST">
    @csrf
    @method('PUT')
	<div class="field_box">
		@if ($fields->isEmpty())
		    <p class="text-muted">No Global QC Fields available.</p>
		@else
		    @foreach ($fields as $key => $field)
		<div class="field-group mb-3 draggable">
		    <div class="row field_qc_row active">
		        <div class="col-md-4">
		            <div class="form-check mb-2">
		            	<span class="sn_no"></span>
		                <i class="bi bi-grip-vertical workflow_field_drag_icon drag-handle"></i>
		                <input type="text" name="fields[{{$field->id}}][field_name]" class="form-control field_name w-95 ms-5p" placeholder="Field Name" required="" value="{{$field->field_name}}">
		            </div>
		        </div>
		        <div class="col-md-2">
		            <select name="fields[{{$field->id}}][field_type]" class="form-control field_type" required="">
		                <option {{$field->field_type == 'text' ? 'selected' : ''}} value="text">Text</option>
		                <option value="dropdown" {{$field->field_type == 'dropdown' ? 'selected' : ''}}>Dropdown</option>
		                <option value="date" {{$field->field_type == 'date' ? 'selected' : ''}}>Date</option>
		                <option value="checkbox" {{$field->field_type == 'checkbox' ? 'selected' : ''}}>Checkbox</option>
		            </select>
		        </div>
		        <div class="col-md-5">
		            <div class="OptionDropdownBox {{ $field->field_type != 'dropdown' ? 'd-none' : '' }}">
		                <div class="position-relative">
		                    <div class="accordion option_container overflow-auto position-absolute top-0 start-0 end-0">
		                        <div class="accordion-item mb-2">
		                            <h2 class="accordion-header"><button class="form-control collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_0" aria-expanded="false" aria-controls="collapseOne_0">Options</button></h2>
		                            <div id="collapseOne_0" class="accordion-collapse collapse {{$key == 0 && $field->field_type == 'dropdown' ? 'show' : ''}}" aria-labelledby="headingOne" style="">
		                                <div class="accordion-body bg-transparent">
		                                	@php
											    $options = $field->options->isEmpty()
											        ? collect([(object)['id' => null, 'option_text' => '']])
											        : $field->options;
											@endphp
	                                		@foreach($options as $Okey => $option) 
			                                    <div class="row m-0 draggables">
			                                        <div class="col-md-10 mb-2 d-flex align-items-center"><i class="bi bi-grip-vertical workflow_field_drag_icon drag-handles"></i>
			                                            <input type="text" name="fields[{{$field->id}}][options][{{$option->id}}]" class="form-control OptionDropdown" placeholder="Option Name" value="{{$option->option_text}}">
			                                        </div>
			                                        <div class="col-md-1">
                                                         <button type="button" class="btn {{count($options)-1 == $Okey ? 'btn-success add_option' : 'btn-danger remove_option'}}" aria-label="Remove">
                                                            <i class="fa {{count($options)-1 == $Okey ? 'fa-plus' : 'fa-minus'}}"></i>
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
                     <button type="button" class="btn {{count($fields)-1 == $key ? 'btn-success add_qc_field' : 'btn-danger remove_qc_field'}}" aria-label="Remove">
                        <i class="fa {{count($fields)-1 == $key ? 'fa-plus' : 'fa-minus'}}"></i>
                    </button>
                </div>
		    </div>
		</div>
			@endforeach
		@endif
	</div>
	<!-- <button type="submit" class="btn btn-primary ms-4">Save</button> -->
</form>
<!-- Confirmation Modal -->
@php
$buttons = [
    ['label' => 'Confirm', 'class' => 'btn-primary', 'id' => 'confirmBtn'],
    ['label' => 'Cancel', 'class' => 'btn-secondary'],
];
@endphp
<x-modal 
    id="confirmationModal" 
    title="Confirm Deletion" 
    body="Are you sure you want to delete the selected records?" 
    :buttons="$buttons"
/>

<script type="text/javascript">
	$(document).ready(function() {
		// function Sno() {
		// 	$('.sn_no').each(function(index) {
		// 	    $(this).text(index + 1);
		// 	});
		// }
		// Sno();

		// Add field 
		$(document).off('click', '.add_qc_field').on('click', '.add_qc_field', function () {
            let newField = $('.field_qc_row:first').closest('.field-group').clone();
            let uniqueId = `new_${Date.now()}`;

            // Update input and select fields
            newField.find('input, select').each(function () {
                $(this).val(''); // Clear the value
                let name = $(this).attr('name');
                if (name) {
                    let updatedName = name.replace(/fields\[[^\]]+\]/, `fields[${uniqueId}]`);
        			$(this).attr('name', updatedName);

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

            $(this).closest('.field_box').find('.add_qc_field')
                .removeClass('btn-success add_qc_field')
                .addClass('btn-danger remove_qc_field')
                .attr('aria-label', 'Remove')
                .find('i').removeClass('fa-plus').addClass('fa-minus');
            // Update the Add button to Remove button
            newField.find('.remove_qc_field')
                .removeClass('btn-danger remove_qc_field')
                .addClass('btn-success add_qc_field')
                .attr('aria-label', 'Remove')
                .find('i').addClass('fa-plus').removeClass('fa-minus');

            // Append the new field to the container
            newField.appendTo('.field_box:last');
        });

         $('#qcToggle').on('change', function () {
		    const isChecked = $(this).is(':checked');
		    var id = $(this).attr('data-processId');
		    var globalStatusUrl = "{{ route('workflows.processQcStatus', ['id' => '_id']) }}";
		    globalStatusUrl = globalStatusUrl.replace('_id', id);

	    	$('.global_qc_box').toggleClass('d-none');

            $.ajax({
                url: globalStatusUrl,
                type: 'POST',
                success: function(response) {
                    toastr.success(response.message, 'Success');
                    $('#confirmationModal').modal('hide');
                    // workflowTable.ajax.reload();
                },
                error: function(xhr) {
                    toastr.error('Something went wrong. Please try again later.', 'Error');
                }
            });

		});


		$(document).on('focus', '.field_type, .field_name, .process_name', function () {
            var fieldRow = $(this).closest('.field_qc_row');

            // Collapse all accordions, except the one related to the current fieldRow
            $('.accordion-collapse.show').not(fieldRow.find('.accordion-collapse')).collapse('hide');

            if (fieldRow.length) {
                // Expand the current accordion and mark it as active
                $(document).find('.field_qc_row').removeClass('active');
                fieldRow.find('.accordion-collapse').collapse('show');
                fieldRow.addClass('active');
            }
        });

        // Remove field
        $(document).on('click', '.remove_qc_field', function () {
            $(this).closest('.field-group').remove();
        });


        $(document).off('click', '.add_option').on('click', '.add_option', function () {
        	console.log('inside code');
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

		$(document).on('change', '.field_qc_row .field_type', function() {
            const fieldRow = $(this).closest('.field_qc_row');
            const isSimpleField = ['text', 'date', 'checkbox'].includes($(this).val());
            
            fieldRow.find('.OptionDropdownBox').toggleClass('d-none', isSimpleField);
            fieldRow.find('.OptionDropdown').attr('required', !isSimpleField);
        });
		
		$(document).on('click', '.qc_submit_btn, .global_qc_submit_btn', function (e) {
		    e.preventDefault();
		    $('#WorkflowQcForm').trigger('submit');
		});
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
</script>