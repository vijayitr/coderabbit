@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
           
        <div class="row">
            <div class="col-2">
                <div class="filter">
                    <input type="input" class="rangedatepicker form-control date-filter">
                    <input type="hidden" class="start_date" name="">
                    <input type="hidden" class="end_date" name="">
                </div>
            </div>
            <div class="col-2">
                <select class="form-control bg-white" id="task-priority">
                    <option value="">-- Select Priority --</option>
                    @foreach (getPriorityNames() as $key => $value)
                        <option value="{{ $key }}" {{ old('priority') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <div class="input-group mb-3">
                    <input type="text" class="form-control bg-white text-center" placeholder="0" id="taskStart">
                    <span class="input-group-text py-0 px-1 bg-white">to</span>
                    <input type="text" class="form-control bg-white text-center" placeholder="0" id="taskEnd">
                </div>
            </div>

            <div class="col-2">
                <select class="form-control select2" id="user-selection"><option value="">Select User</option></select>
            </div>

            <div class="col-2">
                <select class="form-control" id="is_assigned">
                    <option value="">All</option>
                    <option value="assigned">Assigned</option>
                    <option value="unassigned">Un-assigned</option>
                </select>
            </div>

            <div class="col-2 text-end">
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
                        <table class="table" id="AssignTaskTable">
                            <thead>
                            </thead>
                            <tbody>
                              
                            </tbody>
                        </table>

                        <!-- Pagination Links -->
                        <div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="dataTables_wrapper my-2">
            <div class="row">
                <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" role="status" aria-live="polite"></div>
                </div>
                <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers pt-0">
                    </div>
                </div>
            </div>
        </div>

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
        let Count = 0;
        // taskSelectionRange(Count);
        getUserFilterList();
        let ids = [];
        var page = 1;
        let table;
        var id = "{{$id}}";
        let tasks_user = {};
        let assigned_tasks = {};
        let tasksIds = {};
        var checklist = [];
        // CheckAll();
        // if (localStorage.getItem("checkAll") == 'true') {
        //     $('#checkAll, tbody input[type="checkbox"]').prop('checked', true);
        //     $(".assignAllBtn").removeClass("d-none");
        // }
        function addDataTable() {
            if ($.fn.DataTable.isDataTable("#AssignTaskTable")) {
                table.destroy();
            }
            table = $('#AssignTaskTable').DataTable({
                "processing": true,
                "serverSide": false,
                "searching": true,
                "ordering": false,
                "paging": false,
                "info":false
            });

            checkIsUserTask();
        }
        getTaskList(page);
        // addDataTable();

        function getFilters() {
            return {
                start_date: $('.start_date').val(),
                end_date: $('.end_date').val(),
                priority: $('#task-priority').val(),
                task_start: $('#taskStart').val(),
                task_end: $('#taskEnd').val(),
                user: $('#user-selection').val(),
                is_assigned: $('#is_assigned').val(),
                page: page
            };
        }

        function getTaskList(page) {
            let data = getFilters();
            $.ajax({
                url: "{{ route('userAllocation.getTaskList') }}",
                type: 'get',
                data:data,   
                success: function(response) {
                    if (response.status) {
                        if ($.fn.DataTable.isDataTable("#AssignTaskTable")) {
                            table.destroy();
                        }
                        let columns = Object.values(response.data.columns);
                        let records = response.data.records;
                        let dataTables_info = response.data.pagination_info;
                        let dataTables_paginate = response.data.pagination;
                        assigned_tasks = response.data.assigned_tasks;
                        tasksIds = response.data.ids;
                        tasks_user = response.data.tasks_user;
                        let filter_records = response.data.filter_records;
                        $('.dataTables_info').html(dataTables_info);
                        $('.dataTables_paginate').html(dataTables_paginate);
                        Count = response.data.total;
                        // taskSelectionRange(response.data.total);
                        

                        // Append column headers
                        let tableHead = $("#AssignTaskTable thead");
                        tableHead.html('');
                        let tableHeadRow = "<tr>";
                        tableHeadRow += `<th>#</th><th> <div class="form-check"> <input type="checkbox" name="assign_all" class="form-check-input" id="checkAll"></div> </th>`;
                        columns.forEach(col => {
                             tableHeadRow += `<th>${col.replace(/_/g, ' ').toUpperCase()}</th>`;
                        });
                        // tableHeadRow += `<th width="30%">Action</th></tr>`;
                        tableHead.append(tableHeadRow);
                        // Append table rows
                        let tableBody = $("#AssignTaskTable tbody");
                        tableBody.html('');
                        $.each(filter_records, function(index, record) {
                            let isChecked = assigned_tasks[record] != undefined && assigned_tasks[record] > 0;
                            let checkedAttr = isChecked ? "checked" : "";
                            let buttonText = isChecked ? 'Reassign' : 'Assign'
                            let sno = ((page - 1) * 10) + (index +1);
                            let row = "<tr>";
                            row += `<td>${sno}</td><td> <div class="form-check"> <input type="checkbox" class="form-check-input taskCheckbox" value="`+record+`" > <button type="button" class="btn btn-success btn-sm assignBtn text-nowrap ms-2" data-id="`+record+`" data-assigned="`+isChecked+`" title="Assign Task" ><span>`+buttonText+`</span> </button> </div></td>`;
                            columns.forEach(col => {
                                row += `<td>${records[record][col] ? records[record][col] : '-'}</td>`;
                            });

                            row += "</tr>";
                            tableBody.append(row);
                        });

                        addDataTable();

                        // Check Select All
                        const taskStart = Number($('#taskStart').val());
                        const taskEnd = Number($('#taskEnd').val());

                        if (taskStart === 1 && taskEnd === Count) {
                            $('tbody input[type="checkbox"], #checkAll').prop('checked', true);
                        }

                    }
                },
                error: function(xhr, status, error) {
                    // toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        }

        function checkIsUserTask() {
            $(document).find('.assignBtn').each(function() {
                var user = $('#user-selection').val();
                $(this).attr('disabled',false);
                var assigned = $(this).attr('data-assigned');
                var btn_text = assigned == 'true' ? 'Reassign' : 'Assign';
                $(this).find('span').html(btn_text);
                if (user != '') {
                    var task_id = $(this).attr('data-id');
                    if (tasks_user[task_id] != undefined && tasks_user[task_id] == user) {
                        $(this).attr('disabled',assigned);
                        $(this).find('span').html('Assigned');
                    }
                }
            });
        }

        $(document).on('change', '#taskStart, #taskEnd', function () {
            const taskStart = Number($('#taskStart').val());
            const taskEnd = Number($('#taskEnd').val());
            const isAllSelected = (taskStart === 1 && taskEnd === Count);
            ids = [];
            $('tbody input[type="checkbox"], #checkAll').prop('checked', isAllSelected);
        });

        $(document).on('click', 'ul.pagination a', function(e) {
            e.preventDefault();
            page = $(this).attr('data-page');
            getTaskList(page)
        });

        // Select all checkbox functionality
        // $('#checkAll').on('click', function() {
        //     $('tbody input[type="checkbox"]').prop('checked', this.checked);
        // });

        $(document).on('click', '.assignBtn', function(e) {
            e.preventDefault();
            var self = $(this);
            var id = $(this).data('id');
            var priority = $('#task-priority').val();
            var user = $('#user-selection').val();
            if (user == '') {
                toastr.warning('Select User Fist.', 'warning');
                return;
            }
            var url = $('form#AssignTaskForm').attr('action');
            var data = { 
                id:id,
                user:user,
                priority:priority,
                _token: '{{ csrf_token() }}'
                };
            $.ajax({
                url: url,
                type: 'Post',
                data: data,
                success: function(response) {
                    if (response.status) {
                        self.find('span').text('Reassign');
                        // self.closest('tr').find('input').prop({ checked: true, disabled: true });
                        toastr.success(response.message, 'success');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        });

        $(document).on('change', '#user-selection', function() {
            checkIsUserTask();
        });

        function filterData() {

        }

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


        $(document).on('change', '#checkAll', function () {
            const isChecked = $(this).is(':checked');
            const $checkboxes = $('tbody input[type="checkbox"]');

            $checkboxes.prop('checked', isChecked);

            $('#taskStart').val(isChecked ? 1 : '');
            $('#taskEnd').val(isChecked ? Count : '');
        });


        $(document).on('click', '.assignAllBtn', function() {
            localStorage.removeItem("assigned_tasks");
            $('form#AssignTaskForm').trigger('submit');
        });

        $(document).on("change", ".taskCheckbox", function () {
            let value = $(this).val();

            if ($(this).is(":checked")) {
                if (!ids.includes(value)) {
                    ids.push(value);
                }
            } else {
                ids = ids.filter(id => id !== value);
            }

            const count = ids.reduce(
                (acc, id) => {
                    const value = assigned_tasks[id];
                    if (value === 1) acc.assigned += 1;
                    else if (value === 0) acc.unassigned += 1;
                    return acc;
                },
                { assigned: 0, unassigned: 0 }
            );

            let label = 'Assign';
            if (count.assigned && !count.unassigned) {
                label = 'Reassign';
            } else if (count.assigned && count.unassigned) {
                label = 'Assign/Reassign';
            }

            $('#assign-btn span').html(label);
        });

        // function CheckAll() {

        //     if (Count == ids.length || assigned == Count) {
        //         $('tbody input[type="checkbox"]').not(':disabled').prop('checked', 'checked');
        //         $('#checkAll').prop('checked', 'checked');
        //     }
        //     $(".assignAllBtn").toggleClass("d-none", !ids.length);
        //     $('input[name="id"]').val(JSON.stringify(ids));
        //     $('tbody input[type="checkbox"]').each(function() {
        //         if (ids.includes($(this).val())) {
        //             $(this).prop('checked', 'checked');
        //         }
        //     });
        // }

        $(document).on('click', '.filter-btn', function() {
            getTaskList(1);
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
                    $('input.start_date').val(startDate);
                    $('input.end_date').val(endDate);
                    // getTaskList(1);
                }
            }
        });

        function taskSelectionRange(totalCount) {
            // let select = document.getElementById("task-selection-range");
            // select.innerHTML = '<option value="">Select Task</option>';
            // let step = Math.ceil(totalCount / 5); // Divide into 5 logical ranges
            // let ranges = [];
            
            // for (let i = 1; i <= totalCount; i += step) {
            //     let end = Math.min(i + step - 1, totalCount);
            //     ranges.push({ min: i, max: end });
            // }

            // // Generate range options dynamically
            // ranges.forEach(range => {
            //     select.innerHTML += `<option value="${range.min}-${range.max}">${range.min} to ${range.max}</option>`;
            // });

            // // Add "All" option if totalCount is large
            // if (totalCount > step) {
            //     select.innerHTML += `<option value="all">All (${totalCount})</option>`;
            // }
        }

        function getSelectedTask() {
            let { task_start, task_end } = getFilters();
            let start = parseInt(task_start) || 0;
            let end = parseInt(task_end) || 0;
            if (start < end) {
                ids =  Object.entries(tasksIds)
                       .slice(start - 1, end)
                        .map(([_, value]) => value);
            }
            return ids;
        }

        $(document).on('click', '#assign-btn', function() {
            let data = getFilters();
            data['selected_tasks'] = JSON.stringify(getSelectedTask());
            data['_token'] = '{{ csrf_token() }}'; 
            let url = "{{ route('userAllocation.BulkAssignTask') }}";
            $.ajax({
                url: url,
                type: 'Post',
                data: data,
                success: function(response) {
                    if (response.status) {
                        getTaskList(page);
                        ids = [];
                        toastr.success(response.message, 'success');
                    } else {
                        toastr.warning(response.message, 'warning');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        });
    });
</script>
@endpush
