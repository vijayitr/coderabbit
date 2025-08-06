@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
        <h1>{{$form->name}}</h1>
        <h5 class="fw-normal mb-3">{{ $form->form_details }}</h5>
        <form action="{{ route('tickets.store') }}" method="POST" id="ticketsForm">
            @csrf

            @if(isset($form->fields))
                @foreach($form->fields as $field_key => $field)
                    @php
                        $selectedDependencies = isset($field->dependencies) ? $field->dependencies->pluck('depends_on_option_id')->toArray() : [];
                        $prepopulatedData = [
                            'id' => str_pad(Auth::id(), 5, '0', STR_PAD_LEFT),
                            'name' => Auth::user()->name ?? '',
                            'email' => Auth::user()->email ?? '',
                        ];
                        $prepopulate_value = isset($field->prepopulate_by) && !empty($field->prepopulate_by) ? $prepopulatedData[$field->prepopulate_by] : '';

                        
                        $prepopulate_value = '';
                        if (!empty($field->prepopulate_by) && array_key_exists($field->prepopulate_by, $prepopulatedData)) {
                            $prepopulate_value = $prepopulatedData[$field->prepopulate_by];
                        }
                    @endphp
                    <div class="form-group mb-3 {{$field_key = 0 || empty($selectedDependencies) ? '' : 'd-none ticketElement'}}" data-id="{{$field->id}}" data-target="{{ json_encode($selectedDependencies) }}">
                        <label class="form-label text-dark"><span class="sno"></span> {{$field->name}}@if($field->is_required)<span class="text-danger">*</span>@endif :- </label>
                        @if($field->type == 'text' || $field->type == 'date')
                            <input type="{{$field->type == 'text' ? 'text' : 'date'}}" name="{{$field->id}}" class="form-control {{$field->type == 'text' ? '' : 'datepicker'}}" placeholder="Enter your answer" value="{{$prepopulate_value}}"></input>
                        @endif

                        @if($field->type == 'dropdown')
                            <select class="user_hierarchy form-select" name="{{$field->id}}">
                                <option value="">Select your answer</option>
                                @foreach($field->selectOptions as $option)
                                <option value="{{$option->id}}" > {{$option->option}}</option>
                                @endforeach
                            </select>
                        @endif

                        @if($field->type == 'checklist')
                            @foreach($field->selectOptions as $option)
                                <div class="align-items-center mb-2">
                                    <label class="form-check-label mb-0 text-capitalize">
                                    <input type="checkbox" name="{{$field->id}}[]" value="{{$option->id}}" class="form-check-input me-2 mt-0">
                                        {{$option->option}}</label>
                                </div>
                            @endforeach

                        @endif

                        @if($field->type == 'radio')
                            @foreach($field->selectOptions as $option)
                                <div class="align-items-center mb-2">
                                    <label class="form-check-label mb-0 text-capitalize text-dark">
                                    <input type="radio" name="{{$field->id}}" value="{{$option->id}}" class="form-check-input me-2 mt-0">
                                        {{$option->option}}</label>
                                </div>
                            @endforeach

                        @endif
                    </div>
                @endforeach
            @endif

            <!-- ----------------------------------------------------------------------- -->

           <!--  -->
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        AddSerialNumbers();

        $(document).on('change', '#ticketsForm .form-group select, #ticketsForm .form-group input[type="checkbox"], #ticketsForm .form-group input[type="radio"]', function () {
            // Hide all next form groups and remove ticketElement class
            $(this).closest('.form-group').nextAll('.form-group').addClass('d-none').removeClass('ticketElement');

            var dropdown_ids = [];

            // Collect values from visible selects and checked checkboxes
            $('#ticketsForm .form-group:visible').each(function () {
                $(this).find('select').each(function () {
                    dropdown_ids.push($(this).val());
                });

                $(this).find('input[type="checkbox"]:checked').each(function () {
                    dropdown_ids.push($(this).val());
                });

                // Checked radio buttons
                $(this).find('input[type="radio"]:checked').each(function () {
                    dropdown_ids.push($(this).val());
                });
            });

            // Show the form groups based on matching `data-target` values
            $('#ticketsForm .form-group').each(function () {
                const currentHideGroup = $(this);
                const target = $(this).attr('data-target');

                if (target !== undefined) {
                    try {
                        const obj = JSON.parse(target);
                        if (obj.length > 0) {
                            $.each(obj, function (index, value) {
                                if (dropdown_ids.includes(value.toString())) {
                                    currentHideGroup.removeClass('d-none').addClass('ticketElement');
                                }
                            });
                        }
                    } catch (e) {
                        console.error('Invalid JSON in data-target:', target);
                    }
                }
            });

            AddSerialNumbers();
        });


        $(document).on('submit', '#ticketsForm', function(e) {
            e.preventDefault();
            var formData = [];
            var isValid = true;

            $(document).on('change', 'input, select', function() {
                var value = $(this).val();
                if (value != '') {
                    $(this).removeClass('is-invalid');
                } else {
                    $(this).addClass('is-invalid');
                }
            });

            $('#ticketsForm').find('.form-group:visible input, .form-group:visible select, .form-group:visible textarea').each(function() {
                var $field = $(this);
                var tagType = $field.prop("tagName").toLowerCase();
                var elementType = $field.attr('type');
                var name = $field.attr('name');
                var value = $field.val().trim();

                if (!name) {
                    return;
                }

                if ($field.is(':checkbox')) {
                    if (!$field.is(':checked')) {
                        return;
                    }
                }

                // Skip unchecked radio buttons
                if ($field.is(':radio') && !$field.is(':checked')) {
                    return;
                }

                if (value === '') {
                    isValid = false;
                    $field.addClass('is-invalid');
                    return;
                } else {
                    $field.removeClass('is-invalid');
                }

                if (name.endsWith('[]')) {
                    name = name.slice(0, -2); // clean name
                }

                formData.push({name: name, value: value, tagType:tagType, elementType:elementType});
            });

            if (!isValid) {
                toastr.warning('Please fill all required fields.', 'Warning');
            } else {
                // ===== Now GROUP duplicate name fields =====
                var combinedData = [];
                var groupedFields = {};

                formData.forEach(function(item) {
                    var name = item.name;
                    var value = item.value;
                    var tagType = item.tagType;
                    var elementType = item.elementType;

                    if (groupedFields[name]) {
                        // Already exists, push into array
                        if (!Array.isArray(groupedFields[name].value)) {
                            groupedFields[name].value = [groupedFields[name].value];
                        }
                        groupedFields[name].value.push(value);
                    } else {
                        groupedFields[name] = { name: name, value: value, tagType:tagType, elementType:elementType };
                    }
                });

                // Convert groupedFields back to ordered array
                for (var key in groupedFields) {
                    combinedData.push(groupedFields[key]);
                }

                if (combinedData.length) {
                    var $form = $('#ticketsForm');
                    var actionUrl = $form.attr('action');
                    var methodType = $form.attr('method');
                    combinedData._token = '{{ csrf_token() }}';
                    $.ajax({
                        url: actionUrl,
                        type: methodType,
                        data: {form_id: {{$form->id}},form_data: combinedData },
                        success: function(response) {
                             if (response.status) {
                                toastr.success(response.message, 'Success');
                                window.location.href = '/tickets';
                            } else {
                                toastr.warning(response.message, 'Warning');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong. Please try again later.', 'Error');
                        }
                    });
                }
            }

        });

        function AddSerialNumbers() {
            $('.sno:visible').each(function(index) {
                $(this).text((index + 1)+'.');
            });
        }
        
    });
</script>
@endpush