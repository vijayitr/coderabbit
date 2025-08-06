@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
           
        <div class="row">
            <div class="col-3">
                <div class="filter">
                    <input type="input" class="rangedatepicker form-control date-filter">
                    <input type="hidden" class="start_date" name="">
                    <input type="hidden" class="end_date" name="">
                </div>
            </div>

            <div class="col-3">
                <select class="form-control select2" id="user-selection"><option value="">Select User</option></select>
            </div>

            <div class="col-3">
                <select class="form-control" id="is_assigned">
                    <option value="">All</option>
                    <option value="assigned">Assigned</option>
                    <option value="unassigned">Un-assigned</option>
                </select>
            </div>

            <div class="col-3 text-end">
                <button class="btn btn-success" type="button" id="assign-btn"> <span>Assign</span> </button>
                <button class="btn btn-light p-0 ms-2" type="button" id="searchTask"> <i class="bi bi-funnel btn btn-outlined filter-btn"></i> </button>
            </div>
        </div>

        <form action="{{ route('userAllocation.assignTasks', $id) }}" method="POST" id="AssignTaskForm" class="position-relative">
            @csrf
            <input type="hidden" name="id" value="">
            <div class="row">
                <div class="col-md-12 d-grid">
                    <div class="table-responsive">
                        <table class="table" id="CallsTable">
                            <!-- Add Status Column in Table Header -->
                            <thead>
                                <tr>
                                    <th>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                        </div>
                                    </th>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>User Name</th>
                                    <th>Client</th>
                                    <th>Process Name</th>
                                    <th>Status</th>
                                    <!-- <th class="action-box">Actions</th> -->
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>

    // document.addEventListener("DOMContentLoaded", function () {
    //     const inputA = document.getElementById("taskStart");
    //     const inputB = document.getElementById("taskEnd");

    //     function validateInput(input) {
    //         // let maxLimit = parseInt(input.dataset.max, 10) || Infinity;
    //         let maxLimit = Count;
    //         let value = input.value.replace(/\D/g, "");

    //         if (value !== "") {
    //             value = Math.min(parseInt(value, 10), maxLimit);
    //         }

    //         input.value = value;
    //     }

    //     function validateRange() {
    //         let valA = parseInt(inputA.value, 10) || 0;
    //         let valB = parseInt(inputB.value, 10) || 0;

    //         if (valB <= valA) {
    //             inputB.value = valA + 1;
    //         }
    //     }

    //     inputA.addEventListener("input", function () {
    //         validateInput(inputA);
    //     });

    //     inputB.addEventListener("input", function () {
    //         validateInput(inputB);
    //     });

    //     inputB.addEventListener("blur", function () {
    //         validateRange();
    //     });
    // });

    $(document).ready(function() {
        let callsTable;
        let totalRecords;
        let checkAllStatus = false;
        callsTable = $('#CallsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('qualityAssurance.getCalls') }}",
                "type": "GET",
                "data": function(d) {
                    d.start_date = $('input.start_date').val();
                    d.end_date = $('input.end_date').val();
                    d.user = $('#user-selection').val();
                    d.is_assigned = $('#is_assigned').val();
                    // d.date_filter = dateFilter; 
                    // d.sort_by = sortBy; 
                    // d.workflow_name = $('#workflow_filter').val(); 
                    // d.client_id = $('#client_filter').val(); 
                },
                dataSrc: function(json) {
                    totalRecords = json.recordsTotal;
                    return json.data;
                }
            },
            "columns": [
                {
                    data: 'id',
                    render: function(data) {
                        return `<div class="form-check">
                                    <input type="checkbox" class="form-check-input call-checkbox" value="${data}">
                                </div>`;
                    },
                    orderable: false
                },
                {
                    data: null,  // Instead of using 'formatted_id', use a null value here
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart; 
                    },
                    orderable: false
                },
                { data: 'created_at' },
                { data: 'user_name' },
                { data: "client_name" },
                { data: 'activity_name' },
                { 
                    data: 'status',  // Add Status column
                    render: function(data) {
                        return 'Pending';
                        // const badgeClass = data === 'Enabled' ? 'text-success' : 'text-danger';
                        // return `<span class="fw-bold ${badgeClass}">${data}</span>`;
                    }
                },
                // {
                //     data: null,
                //     render: function(data, type, row) {
                //         // totalRecords = 
                //         return '';
                //         var return_val = `
                //             <div class="dropdown text-center">
                //                 <i class="btn btn-light fa fa-ellipsis-v text-primary cursor-pointer fs-5" data-bs-toggle="dropdown" aria-expanded="false"></i>
                //                 <ul class="dropdown-menu p-0 overflow-hidden">`;

                //             // return_val += `
                //             //     <li>
                //             //         <a class="dropdown-item" href="/workflows/${data.id}/edit">
                //             //             <i class="bi bi-pencil-square text-primary fs-5 me-2"></i> Edit
                //             //         </a>
                //             //     </li>`;



                //         return_val += `
                //                 </ul>
                //             </div>`;
                        
                //         return return_val;
                //     },
                //     orderable: false
                // }

            ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[3, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function(settings) {
                $('.page-numbers').remove();
                $('#userTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });

        $(document).on('click', '#searchTask', function(){
            callsTable.ajax.reload();
        });

        let selectedValues = [];
        let unselectedValues = [];
        $(document).on('change', '#checkAll', function () {
            const isChecked = $(this).is(':checked');
            $('.call-checkbox').prop('checked', isChecked);
            checkAllStatus = isChecked;
            selectedValues = isChecked ? ['checkAll'] : [];
            unselectedValues = []; 
            console.log('checkAll = ',selectedValues);
        });

        $(document).on('change', '.call-checkbox', function () {
            const value = $(this).val();
            const isChecked = $(this).is(':checked');

          
            unselectedValues = (checkAllStatus && !isChecked)
                                ? [...unselectedValues, value]
                                : unselectedValues.filter(v => v !== value);

            if (isChecked) {
                if (!selectedValues.includes(value)) selectedValues.push(value);
            } else {
                selectedValues = selectedValues.filter(v => v !== value);
                if (!unselectedValues.length) {
                    selectedValues = selectedValues.filter(v => v !== 'checkAll');
                }
            }

            let checkedAllStatus = totalRecords <= selectedValues.length && !selectedValues.includes('checkAll');
            if (totalRecords == unselectedValues.length) {
                checkAllStatus = false;
                unselectedValues = [];
                selectedValues = [];
            }

            $('#checkAll').prop('checked', checkedAllStatus);
            if (!unselectedValues.length) {
                const lengthWithoutCheckAll = selectedValues.filter(v => v !== 'checkAll').length;

                if (totalRecords <= lengthWithoutCheckAll && !unselectedValues.length) {
                    if (!selectedValues.includes('checkAll')) selectedValues.push('checkAll');
                    $('#checkAll').prop('checked', true);
                }
            }


            console.log('call-checkbox = ', selectedValues, 'unselectedValues = ', unselectedValues, 'checkAllStatus = ',checkAllStatus, 'totalRecords = ',totalRecords);
        });

        $(document).on('click', '.filter-btn', function() {
            // getTaskList(1);
        });

        flatpickr(".rangedatepicker", {
            mode: "range",
            dateFormat: "Y-m-d",
            allowInput: false,
            onReady: function(selectedDates, dateStr, instance) {
                instance.input.classList.add('no-custom-arrow');
                instance.input.setAttribute('placeholder', 'Select a date range');
            },
            onOpen: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('no-custom-arrow');
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) { // Ensure both dates are selected
                    var startDate = new Date(selectedDates[0].getTime() + 86400000).toISOString().split('T')[0];
                    var endDate = new Date(selectedDates[1].getTime() + 86400000).toISOString().split('T')[0];
                    $('input.start_date').val(startDate).trigger('change');
                    $('input.end_date').val(endDate).trigger('change');

                    // getTaskList(1);
                }
            }
        });

        $(document).on('click', '#assign-btn', function() {
            let data = {
                _token:'{{ csrf_token() }}',
                start_date : $('input.start_date').val(),
                end_date : $('input.end_date').val(),
                user : $('#user-selection').val(),
                is_assigned : $('#is_assigned').val(),
                selectedValues : selectedValues,
                unselectedValues : unselectedValues
            }

            if (data.user == '') {
                toastr.warning('Select User Fist.', 'warning');
                return;
            }
            console.log('data.user = ',data.user);
            let url = "{{ route('qualityAssurance.AssignCalls') }}";
            console.log(data, url);
            // data['_token'] = '{{ csrf_token() }}'; 
            // let url = "{{ route('userAllocation.BulkAssignTask') }}";
           $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.status) {
                        callsTable.ajax.reload();
                        toastr.success(response.message, 'Success');
                    } else {
                        toastr.warning(response.message || 'Something went wrong.', 'Warning');
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        // Laravel validation error
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function (key, messages) {
                            toastr.error(messages[0], 'Validation Error');
                        });
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'An unexpected error occurred.', 'Error');
                    }
                }
            });

        });
        getUserFilterList();
        function getUserFilterList() {
            $.ajax({
                url: "{{ route('getUserFilterList') }}",
                type: 'get',
                success: function(response) {
                    if (response.status) {
                        var userSelect = $('#user-selection');

                        userSelect.empty();
                        userSelect.append('<option value="">Select User</option>');
                        $.each(response.data, function(index, user) {
                            var userOption = `<option value="${user.id}">${user.name} (${user.roles_list})</option>`;
                            userSelect.append(userOption);
                        });
                        userSelect.trigger('change');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        }
    });
</script>
@endpush
