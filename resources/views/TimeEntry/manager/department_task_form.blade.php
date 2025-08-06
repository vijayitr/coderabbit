@php
$fieldsHtml = [
    'clients' => [
        'value' => '',
        'type' => 'select',
        'multiple' => true,
        'name' => 'clients',
        'label' => 'Clients',
        'class' => 'client_list',
        'wrapper_class' => 'client_col',
    ],
    'users' => [
        'value' => '',
        'name' => 'user',
        'label' => 'Users',
        'type' => 'select',
        'multiple' => false,
        'class' => 'user_list',
        'wrapper_class' => 'user_col',
    ],
    'fte' => [
        'value' => '',
        'type' => 'number',
        'wrapper_class' => 'fte_col',
        'label' => 'Full Time Employee',
        'class' => 'full_time_employee',
        'name' => 'full_time_employee',
        'placeholder' => 'Enter Full Time Employee',
    ],
    'todays_team' => [
        'value' => '',
        'type' => 'number',
        'name' => 'todays_team',
        'label' => 'Todays Team',
        'class' => 'todays_team',
        'wrapper_class' => 'today_team_col',
        'placeholder' => 'Enter Todays Team',
    ],
];
@endphp
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex align-items-center justify-content-between mb-3 filter-bar">
            <h4>Managers - Tasks</h4>
            <button type="button" class="btn btn-dark d-flex align-items-center manager-task-button py-0">
                <i class="bi bi-plus fs-5"></i>
                <span>Create Task</span>
            </button>
        </div>
    </div>

    <div class="col-md-12">
        <div class="row">
            <div class="form-group mb-4">
                <div class="field_box">
                    <div class="field-group mb-3" id="field_container">
                        @foreach($ManagerEntryTime as $key => $entry)
                        @php
                            $json = json_decode($entry->extra_data);
                            $fieldsHtml['clients']['value'] = isset($json->clients) && is_array($json->clients) ? json_encode($json->clients) : '';
                            $fieldsHtml['users']['value'] = isset($json->user) ? $json->user : '';
                            $fieldsHtml['fte']['value'] = isset($json->full_time_employee) ? $json->full_time_employee : '';
                            $fieldsHtml['todays_team']['value'] = isset($json->todays_team) ? $json->todays_team : '';
                        @endphp
                        <div class="accordion mb-3">
                            <div class="accordion-item mb-2">
                                <h2 class="accordion-header" id="heading_{{$key+1}}">
                                    <button class="accordion-button d-flex align-items-center justify-content-between p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$key+1}}" aria-expanded="true" aria-controls="collapse_{{$key+1}}">
                                        <div>
                                            <span class="department_name badge text-bg-secondary p-3 me-2">Department : <span>{{$entry->department->field_name}}</span></span>
                                            <span class="task_name badge text-bg-secondary p-3 me-2">Task : <span>{{$entry->task->option_value}}</span></span>
                                        </div>
                                        <span class="completed_task ms-auto fw-bold badge text-bg-success p-3 me-2 {{$entry->end_time == null ? 'd-none' : ''}}">Completed</span>
                                    </button>
                                </h2>
                                <div id="collapse_{{$key+1}}" class="accordion-collapse collapse show" aria-labelledby="heading_{{$key+1}}">
                                    <div class="accordion-body bg-transparent p-3">
                                        <input type="hidden" name="task_id" value="{{$entry->id}}">
                                        <div class="row field_row d-flex align-items-center mb-3">

                                            <div class="col-md-5 mb-2">
                                                <div class="form-group mb-2">
                                                    <label class="mb-1">Department:</label>
                                                    <select name="department" value="{{$entry->department->id}}" class="form-control department_type select2" required>
                                                        <option value="">Select Department</option>
                                                        @foreach($departments as $department)
                                                            <option value="{{$department->id}}" {{$entry->department->id == $department->id ? 'selected' : '' }}>{{$department->field_name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="mb-1">Task:</label>
                                                    <select name="task" value="{{$entry->task->id}}" class="form-control task_type select2" required>
                                                    <option value="">Select Task</option>
                                                    @foreach($departments as $department)
                                                        @foreach($department->tasks ?? [] as $task)
                                                            <option value="{{$task->id}}" data-id="{{$task->field_id}}" >{{$task->option_value}}</option {{$entry->task->id == $department->id ? 'selected' : '' }}>
                                                        @endforeach
                                                    @endforeach</select>
                                                </div>
                                            </div>

                                            <div class="col-md-7 mb-2">
                                                <div class="form-group">
                                                    <label class="mb-1">Details:</label>
                                                    <textarea name="details" rows="4" class="form-control details" placeholder="Enter details">{{$entry->details}}</textarea>
                                                </div>

                                            </div>

                                            @foreach($fieldsHtml as $key => $field)
                                                <div class="col-md-6 mb-2 {{ $field['wrapper_class'] }} d-none">
                                                    <label class="mb-1">{{ $field['label'] }}:</label>
                                                    @if($field['type'] === 'select')
                                                        <select name="{{ $field['name'] }}" value="{{$field['value']}}"
                                                                class="form-control bg-white select2 {{ $field['class'] }}"
                                                                {{ !empty($field['multiple']) ? 'multiple' : '' }}>
                                                        </select>
                                                    @elseif($field['type'] === 'number')
                                                        <input type="number" 
                                                               name="{{ $field['name'] }}" value="{{$field['value']}}"
                                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                                               class="form-control bg-white {{ $field['class'] }} no-arrows" />
                                                    @endif
                                                </div>
                                            @endforeach


                                             <div class="d-flex align-items-center mt-4 filter-bar-buttons">
                                                <button type="submit" class="btn btn-primary btn-filled complete_task">Complete Task</button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@php
$buttons = [
    ['label' => 'Confirm', 'class' => 'btn-primary', 'id' => 'managerConfirmDelete'],
    ['label' => 'Cancel', 'class' => 'btn-secondary'],
];
@endphp
    <x-modal 
        id="ManagerConfirmationModal" 
        title="Confirm Deletion" 
        body="confirm" 
        :buttons="$buttons" 
    />

@push('scripts')
<script>
    var ClientsAndUsers = {};
    var departmentHtml = '<option value="">Select Department</option>';
    var taskHtml = '<option value="">Select Task</option>';
    var extraFields = {};
    @foreach($departments as $department)
        departmentHtml += '<option value="{{$department->id}}">{{$department->field_name}}</option>';
        @foreach($department->tasks ?? [] as $task)
            taskHtml += '<option value="{{$task->id}}" data-id="{{$task->field_id}}">{{$task->option_value}}</option>';
            extraFields[{{$task->id}}] = JSON.parse(@json($task->extra_fields));
        @endforeach
    @endforeach

    $(document).ready(function () {
        function applySelect() {
            $(document).find(".select2").each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    $(this).select2('destroy');
                }

                const isMultiple = $(this).prop("multiple");

                $(this).select2({
                    closeOnSelect: !isMultiple,
                    placeholder: $(this).data('placeholder') || "Select an option",
                    allowClear: !isMultiple,
                    tags: true
                });
            });
        }

        $('input[type="number"]').on('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        $(document).on('shown.bs.collapse', '.accordion-collapse', function () {
           applySelect();
        });
        $('.accordion-collapse.show').collapse('hide');

         
        $(document).on('click', '.manager-task-button', function() {
            appendRow();
        });

        function appendRow() {
            key = $('#field_container .row.field_row').length + 1;
            var Btn = '<button type="button" class="btn btn-danger remove_field mt-4" aria-label="Remove"><i class="fa fa-minus"></i></button>';
            if (key == 1) {
                Btn = ' <button type="button" class="btn btn-success add_field mt-4" aria-label="Add"><i class="fa fa-plus"></i></button>';
            }
            var extraFieldsHtml = '';
            @foreach($fieldsHtml as $key => $field)
                extraFieldsHtml += `<div class="col-md-6 mb-2 {{ $field['wrapper_class'] }} d-none">
                    <label class="mb-1">{{ $field['label'] }}:</label>
                    @if($field['type'] === 'select')
                        <select name="{{ $field['name'] }}" value=""
                                class="form-control bg-white select2 {{ $field['class'] }}"
                                {{ !empty($field['multiple']) ? 'multiple' : '' }}>
                        </select>
                    @elseif($field['type'] === 'number')
                        <input type="number" 
                               name="{{ $field['name'] }}" value=""
                               placeholder="{{ $field['placeholder'] ?? '' }}"
                               class="form-control bg-white {{ $field['class'] }} no-arrows" />
                    @endif
                </div>`;
            @endforeach

            let newRow = `<div class="accordion mb-3">
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading_${key}">
                                <button class="accordion-button d-flex align-items-center justify-content-between p-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_${key}" aria-expanded="true" aria-controls="collapse_${key}">
                                    <div>
                                        <span class="department_name badge text-bg-secondary p-3 me-2">Department : <span>-</span></span>
                                        <span class="task_name badge text-bg-secondary p-3 me-2">Task : <span>-</span></span>
                                    </div>
                                    <span class="time ms-auto fw-bold d-none">00.00</span>
                                    <span class="completed_task ms-auto fw-bold badge text-bg-success p-3 me-2 d-none">Completed</span>
                                     <span class="remove_field ms-auto fw-bold badge text-bg-danger px-2 me-2">
                                        <i class="fa fa-trash"></i>
                                    </span>
                                </button>
                            </h2>
                            <div id="collapse_${key}" class="accordion-collapse collapse show" aria-labelledby="heading_${key}">
                                <div class="accordion-body bg-transparent p-3">
                                    <input type="hidden" name="task_id" value="" />
                                    <div class="row field_row d-flex align-items-center mb-3">
                                        <div class="col-md-5 mb-2">
                                                <div class="form-group mb-2">
                                                    <label class="mb-1">Department:</label>
                                                    <select name="department" class="form-control department_type select2 " required>
                                                        ${departmentHtml}
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="mb-1">Task:</label>
                                                    <select name="task" class="form-control task_type select2 " required>
                                                        ${taskHtml}
                                                    </select>
                                                </div>
                                                </div>
                                                <div class="col-md-7 mb-2">
                                                <div class="form-group">
                                                    <label class="mb-1">Details:</label>
                                                    <textarea name="details" rows="4" class="form-control details" placeholder="Enter details"></textarea>
                                                </div>
                                            </div>`+extraFieldsHtml+`
                                        
                                            <div class="d-flex align-items-center mt-4 filter-bar-buttons ">
                                                <button type="submit" class="btn btn-primary btn-filled start_task">Start Task</button>
                                                <button type="submit" class="btn btn-primary btn-filled complete_task d-none">Complete Task</button>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;

            $("#field_container").prepend(newRow);
            SetClientsAndUsers();
            applySelect();
        }

        $(document).on("click", ".add_field", function () {
            appendRow();
        });

        $(document).on('change', '.department_type', function() {
            let selectedValue = $(this).val();
            var selectedOptionHtml = $(this).find('option:selected').html();
            $(this).closest('.accordion-item').find('.accordion-header .department_name span').html(selectedOptionHtml);
            var task_type =  $(this).closest('.row.field_row').find(".task_type");
            task_type.html(taskHtml);
            if (selectedValue != '') {
               task_type.find("option").each(function () {
                    if ($(this).data('id') != selectedValue && $(this).val() != '') {
                        $(this).remove();
                    }
                });
            }
        });

        $(document).on('change', '.task_type', function() {
            let selectedValue = $(this).find('option:selected').data('id');
            var selectedOptionHtml = $(this).find('option:selected').html();
            $(this).closest('.accordion-item').find('.accordion-header .task_name span').html(selectedOptionHtml);
            let departmentSelect = $(this).closest('.row.field_row').find('.department_type');
            if (selectedValue != undefined) {
                departmentSelect.val(selectedValue).trigger('change.select2');
                let departmentHtml = $(this).closest('.row.field_row').find('.department_type option:selected').html();
                $(this).closest('.accordion-item').find('.accordion-header .department_name span').html(departmentHtml);
            }

            Manage_extra_fields();
        });
        Manage_extra_fields();
        function Manage_extra_fields() {
            $(document).find('.task_type').each(function() {
                var manager_task_id = $(this).val();
                var box = $(this).closest('.accordion-item');

                var fields = extraFields[manager_task_id];
                const fieldMap = {
                    clients: '.client_col',
                    users: '.user_col',
                    full_time_employee: '.fte_col',
                    todays_team: '.today_team_col',
                };

                for (const [key, selector] of Object.entries(fieldMap)) {
                    const action = Array.isArray(fields) && fields.includes(key) ? 'removeClass' : 'addClass';
                    box.find(selector)[action]('d-none');
                }
            });
            applySelect();
        }

        // Remove field row
        $(document).on("click", ".remove_field", function () {
            $(this).closest(".accordion").remove();
        });

        $(document).on('click', '.start_task', function() {
             var body = $(this).closest('.accordion-body');

            // Collect form values
            var formData = {
                _token: '{{ csrf_token() }}',
                userType: 'manager',
                id: body.find('input[name="task_id"]').val(),
                department_id: body.find('select[name="department"]').val(),
                task_id: body.find('select[name="task"]').val(),
                details: body.find('textarea[name="details"]').val().trim()
            };

            var fields = extraFields[formData.task_id];
            const fieldMap = {
                clients: '.client_list',
                users: '.user_list',
                full_time_employee: 'input.full_time_employee',
                todays_team: 'input.todays_team',
            };
            if (fields != undefined) {
                for (const [key, selector] of Object.entries(fields)) {
                    formData[selector] = body.find(fieldMap[selector]).val();
                }
            }

            // Validation
            var errors = [];
            if (!formData.task_id) errors.push("Please select a task.");
            if (!formData.department_id) errors.push("Please select a department.");

            if (errors.length) {
                errors.forEach(error => toastr.error(error, 'Validation Error'));
                return;
            }

            // AJAX request only if validation passes
            $.ajax({
                url: '{{ route("timeEntry.startTask") }}',
                type: 'post',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        body.find('input[name="task_id"]').val(response.data.id);
                        body.find('button.start_task').remove();
                        body.find('button.complete_task').removeClass('d-none');

                        // var startTime = new Date().getTime();
                        // var spanTime = body.closest('.accordion-item').find('.time');
                        // spanTime.attr('data-startTime', startTime);
                        // startTimer(spanTime);

                        toastr.success(response.message, 'Success');
                    }
                },
                error: function(xhr) {
                    toastr.error("Something went wrong. Please try again.", 'Error');
                }
            });
        });

        // Single delete
        $(document).on('click', '.complete_task ', function() {
            var body = $(this).closest('.accordion-body');
            // Collect form values
            var formData = {
                _token: '{{ csrf_token() }}',
                userType: 'manager',
                id: body.find('input[name="task_id"]').val(),
                status:'completed'
            };
            $('#ManagerConfirmationModal .modal-body').html('Are you sure you want to complete this task?');
            $('#ManagerConfirmationModal').modal('show');

            $('#managerConfirmDelete').off('click').on('click', function() {

                 $.ajax({
                    url: '{{ route("timeEntry.completeTask") }}',
                    type: 'post',
                    data: formData,
                    success: function(response) {
                        $('#ManagerConfirmationModal').modal('hide');
                        toastr.success(response.message, 'Success');
                        body.closest('.accordion').remove();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });

        getClientsAndUsers();
        function getClientsAndUsers() {
            $.ajax({
                url: '{{ route("timeEntry.getClientsAndUsers") }}',
                type: 'get',
                success: function(response) {
                    if (response.status) {
                        ClientsAndUsers = response.data;
                        SetClientsAndUsers();
                    }
                },
                error: function(xhr) {
                    toastr.error("Something went wrong. Please try again.", 'Error');
                }
            });
        }

        function SetClientsAndUsers(){
            const clients = ClientsAndUsers.clients;
            const users = ClientsAndUsers.users;

            // Populate Clients
            let clientOptions = '<option value="">Select Client</option>';
            clients.forEach(client => {
                clientOptions += `<option value="${client.id}">${client.client_name}</option>`;
            });

            // Populate Users
            let userOptions = '<option value="">Select User</option>';
            users.forEach(user => {
                userOptions += `<option value="${user.id}">${user.name} (${user.email})</option>`;
            });
            $(document).find('.client_list').each(function() {
                var optionLength = $(this).find('option').length;
                if (optionLength === 0) {
                    var box = $(this).closest('.accordion-item');
                    if (box.find('.client_list option').length === 0) {
                        box.find('.client_list').html(clientOptions);
                    }
                    if (box.find('.user_list option').length === 0) {
                        box.find('.user_list').html(userOptions);
                    }
                    // prepopulate values
                    var client_list_value = box.find('.client_list').attr('value');
                    var user_list_value = box.find('.user_list').attr('value');
                    if (client_list_value != '') {
                        box.find('.client_list').val(JSON.parse(client_list_value)).trigger('change');
                    }
                    box.find('.user_list').val(user_list_value).trigger('change');
                }
            });

            applySelect();
        }

        // Function to Start a Timer for a Specific Task
        // function startTimer(element) {
        //     function updateTime() {
        //         var startTime = element.data('startTime');
        //         consoe
        //         if (!startTime) return;

        //         var currentTime = new Date().getTime();
        //         var elapsedTime = currentTime - startTime; // Difference in milliseconds

        //         var seconds = Math.floor((elapsedTime / 1000) % 60);
        //         var minutes = Math.floor((elapsedTime / 1000 / 60) % 60);
        //         var hours = Math.floor((elapsedTime / 1000 / 3600));

        //         element.html(
        //             (hours < 10 ? "0" : "") + hours + ":" +
        //             (minutes < 10 ? "0" : "") + minutes + ":" +
        //             (seconds < 10 ? "0" : "") + seconds
        //         );
        //     }

        //     // Set interval for each timer separately
        //     var interval = setInterval(updateTime, 1000);
        //     element.data('interval', interval); // Store interval reference
        // }


        // $(document).on('submit', 'form.manager_form', function(e) {
        //     if ($('.row.field_row select[required]').filter(function() { 
        //         return !this.value; 
        //     }).length) {
        //         toastr.warning('Fill all Required fields.', 'Warning');
        //         event.preventDefault();
        //         return;
        //     }

        // });
    });
</script>
@endpush
