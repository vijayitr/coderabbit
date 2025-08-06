@extends('layouts.app')
@section('content')
  <div class="container" id="TimeEntry">
  	<div class="d-flex justify-content-end align-items-center mb-3">
        @if ($authUser->can('time_entry.agent_tasks') && $authUser->can('time_entry.manager_tasks')) 
        <!-- Toggle Switch Tabs -->
        <div class="btn-group me-3" role="group">
            <button class="toggle-btn btn btn-outline-secondary active" data-target="agentModule">Agent</button>
            <button class="toggle-btn btn btn-outline-secondary" data-target="managerModule">Manager</button>
        </div>
        @endif
    </div>

     <!-- Tab Content -->
    <div class="tab-content">
       @if ($authUser->can('time_entry.agent_tasks')) 
        <div class="tab-pane fade {{$authUser->can('time_entry.agent_tasks') ? 'show active' : ''}}" id="agentModule">
        	@include('TimeEntry.agent_task')
        </div>
        @endif
        @if ($authUser->can('time_entry.manager_tasks')) 
        <div class="tab-pane fade {{!$authUser->can('time_entry.agent_tasks') && $authUser->can('time_entry.manager_tasks') ? 'show active' : ''}}" id="managerModule">
        	@include('TimeEntry.manager.department_task_form')
        </div>
        @endif
    </div>
    @if($authUser->can('time_entry.assigned_task_list'))

    <div class="row mb-4">
            <div class="col-md-12 d-grid">
                <hr/>
                <h4>Task List</h4>
                <div class="position-relative task-loader">
                    <div class="dataTables_processing card" role="status">
                        <div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table d-none" id="taskTable">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>

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
        <x-floating_window />
    @endif
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {

        $(document).on('click', '.toggle-btn', function () {
            const $this = $(this);
            const target = $this.data('target');

            // Activate the clicked button and deactivate others
            $this.addClass('active').siblings('.toggle-btn').removeClass('active');

            // Show the target tab and hide others
            $('.tab-pane').removeClass('active show');
            $('#'+target).addClass('active show');
        });

        let getPriorityBadge = @json(getPriorityBadge());
        @if($authUser->can('time_entry.assigned_task_list'))
            let table;

            var page = 1;
            getTaskList(page);
            function getTaskList(page) {
                var savedTaskId = localStorage.getItem('selected_task_id');
                savedTaskId = savedTaskId == null ? '' : savedTaskId;
                $('.task-loader').removeClass('d-none');
                $.ajax({
                    url: "{{ route('timeEntry.getTasks') }}",
                    type: 'get',
                    data:{ page: page, savedTaskId : savedTaskId },   
                    success: function(response) {
                        if (response.status) {
                            if ($.fn.DataTable.isDataTable("#taskTable")) {
                                table.destroy();
                            }
                            let columns = Object.values(response.data.columns);
                            let records = response.data.records;
                            let dataTables_info = response.data.pagination_info;
                            let dataTables_paginate = response.data.pagination;
                            let filter_records = response.data.filter_records;
                            $('.dataTables_info').html(dataTables_info);
                            $('.dataTables_paginate').html(dataTables_paginate);

                            // Append column headers
                            let tableHead = $("#taskTable thead");
                            let tableBody = $("#taskTable tbody");
                            tableHead.html('');
                            tableBody.html('');
                            let tableHeadRow = "<tr>";
                            tableHeadRow += `<th>#</th>`;
                            if (columns.length) {
                                tableHeadRow += `<th>Action</th>`;
                            }

                            columns.forEach(col => {
                                 tableHeadRow += `<th data-col="${col}">${col.replace(/_/g, ' ').toUpperCase()}</th>`;
                            });
                            tableHead.append(tableHeadRow);
                            // Append table rows
                            $.each(filter_records, function(index, record) {
                                let isChecked = false;
                                let checkedAttr = false;
                                let buttonText = isChecked ? 'Reassign' : 'Assign'
                                let sno = ((page - 1) * 10) + (index +1);
                                let row = `<tr data-taskid="`+record+`">`;
                                row += `<td>${sno}</td>`;
                                row += '<td class="d-flex">';

                                row += ` <button type="button" class="btn btn-light btn-sm view_task me-2" data-id="`+record+`" title="Assign Task">
                                                    <i class="bi bi-info-circle text-warning fs-5"></i>
                                                </button>`;


                                row += ` <button type="button" class="btn btn-light btn-sm archive_task" data-id="`+record+`" title="Archive Task">
                                                    <i class="fa fa-archive text-info fs-5"></i>
                                                </button>`;
                                row += `</td>`;
                                columns.forEach((col, index) => {
                                    var data_priority = index == 0 ? `data-priority="`+records[record][col]+`"` : '';
                                    var data_content = index == 0 ? `<span class="`+getPriorityBadge[records[record][col]]['class']+` p-3">`+getPriorityBadge[records[record][col]]['label']+`</span>` : (records[record][col] ? records[record][col] : '-');
                                    row += `<td `+data_priority+`>`+data_content+`</td>`;
                                });

                                row += "</tr>";
                                tableBody.append(row);
                            });

                            addDataTable();
                        }
                    },
                    error: function(xhr, status, error) {
                        // toastr.error(xhr.responseJSON.message, 'Error');
                    }
                });
            }

            function addDataTable() {
                $('#taskTable').removeClass('d-none');
                $('.task-loader').addClass('d-none');
                if ($.fn.DataTable.isDataTable("#taskTable")) {
                    table.destroy();
                }
                table = $('#taskTable').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "searching": true,
                    "ordering": false,
                    "paging": false,
                    "info":false
                });

                var savedTaskId = localStorage.getItem('selected_task_id');

                if (savedTaskId) {
                    // Find the row based on data-taskid attribute
                    var row = table.rows().nodes().to$().filter(function () {
                        return $(this).attr('data-taskid') == savedTaskId;
                    });

                    if (row.length > 0) {
                        var rowIndex = table.row(row).index(); // Get row index
                        var page = Math.floor(rowIndex / table.page.len()); // Calculate page number


                        // Navigate to the correct page in the DataTable
                        table.page(page).draw(false);

                        // Wait for table to update and then trigger the click
                        setTimeout(function () {
                            row.find('.view_task').trigger('click');
                            localStorage.removeItem('selected_task_id');
                        }, 500);
                    } else {
                        console.log("Row not found for task ID:", savedTaskId);
                    }
                }
            }

            $(document).on('click', 'ul.pagination a', function(e) {
                e.preventDefault();
                page = $(this).attr('data-page');
                getTaskList(page)
            });

           $(document).on('click', '.view_task', function () {
                let headers = [];
                let values = [];

                // Get all headers (th) from the table, except first and second
                $('#taskTable thead th').each(function (index, element) {
                    if (index !== 0 && index !== 1) {
                        headers.push(formatColumnName($(element).text().trim()));
                    }
                });

                // Get first row (td) values, except first and second
                $(this).closest('tr').find('td').each(function (index, element) {
                    if (index !== 0 && index !== 1) {
                        values.push($(element).text().trim());
                    }
                });

                // Build the vertical table dynamically
                let verticalTable = '<table class="table table-bordered"><tbody>';
                
                headers.forEach((header, index) => {
                    let value = (values[index] || '');
                    let copyIcon = value !== '-' && index != 0 ? `<img class="ms-2 copy_content cursor-pointer" src="/images/copy.svg" alt="copy" title="Copy">` : '';


                    verticalTable += `<tr><td>${index+1}</td><td>${header}</td><td><span class="value">${value}</span> ${copyIcon}</td></tr>`;
                });


                verticalTable += '</tbody></table>';

                // Append the new table to a div
                let copyAll = '<img class="me-1 copy_all_content cursor-pointer float-start" src="/images/copy.svg" alt="copy all" title="Copy All">';

                $('#drag .title h2').text('Task Details');
                if ($('#drag .title div .copy_all_content').length === 0) {
                    $('#drag .title div').prepend(copyAll);
                }
                $('#drag .content').html(verticalTable);
                $('#drag').show();
                var oDrag = document.getElementById("drag");
                oDrag.style.left = (document.documentElement.clientWidth - oDrag.offsetWidth) / 2 + "px";
                oDrag.style.top = (document.documentElement.clientHeight - oDrag.offsetHeight) / 2 + "px";
            });

           function formatColumnName(column) {
            let formatted = column.replace(/_/g, ' ');
            return formatted.charAt(0).toUpperCase() + formatted.slice(1);
        }

           $(document).on('click', '.copy_all_content', function() {
                const createTaskBox = document.querySelector('.create_task_box');

                if (createTaskBox && createTaskBox.offsetParent !== null) {
                    const labels = createTaskBox.querySelectorAll('.Workflow_field_box label');
                    const dragTableRows = document.querySelectorAll('#drag table tr');

                    labels.forEach(label => {
                        console.log('data-snake =',label.getAttribute('data-snake'));
                        const labelText = formatColumnName(label.getAttribute('data-snake').trim());

                        dragTableRows.forEach(row => {
                            const cells = row.querySelectorAll('td');
                            if (cells.length >= 2) {
                                const firstCol = formatTitleCase(cells[1].textContent.trim());
                                if (firstCol === labelText) {
                                    const secondColValue = cells[2].textContent.trim();
                                    var $input = $(label).parent().find('input, textarea, select').first();
                                    if ($input.length) {
                                        $input.val(secondColValue);
                                    }
                                }
                            }
                        });
                    });
                } else {
                    toastr.warning("Select Activity First", 'Warning');
                }

           });

           function formatTitleCase(text) {
                return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
            }

           $(document).on('click', '.copy_content', function() {
                const valueToCopy = $(this).closest('td').find('span.value').html();
                navigator.clipboard.writeText(valueToCopy).then(() => {
                    toastr.success("Copied to clipboard: " + valueToCopy, 'Success');
                }).catch(err => {
                });
           });

           $(document).on('click', '.archive_task', function() {
                var id = $(this).attr('data-id');
                var $button = $(this);
                $.ajax({
                    url: '{{ route("timeEntry.archiveTask") }}',
                    type: 'get',
                    data: { id: id, _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response.status) {
                            if ($.fn.dataTable.isDataTable('#taskTable')) {
                                $('#taskTable').DataTable().destroy();
                            }
                            $button.closest('tr').remove();
                            getTaskList(page);
                            toastr.success(response.message, 'Success');
                        } else {
                            toastr.warning(response.message, 'Warning');
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                    }
                });
           });
        @endif
    });
</script>
@endpush
