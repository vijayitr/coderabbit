@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar">
            @if($authUser->can('case_allocation.import_task'))
                <div class="ms-auto">
                    <a href="{{ route('userAllocation.importAssignTask') }}" class="btn btn-dark py-0 d-flex align-items-center">
                        <i class="bi bi-box-arrow-in-down fs-5 me-1"></i>
                        <span>Import Tasks</span>
                    </a>
                </div>
            @endif
        </div>

        <table class="table w-100" id="userAllocationTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Allocated Users</th>
                    <th>Role</th>
                    <th class="text-center">Status</th>
                    <th class="action-box text-center">Actions 
                        <img src="{{url('images/icons/table-delete.png')}}" alt="table-delete" class="cursor-pointer table-delete select-all">
                    </th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@php
$buttons = [
    ['label' => 'Confirm', 'class' => 'btn-primary', 'id' => 'confirmDelete'],
    ['label' => 'Cancel', 'class' => 'btn-secondary'],
];
@endphp

<x-emptyModal />
<x-confirm-modal />
<x-floating_window />

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        localStorage.removeItem("assigned_tasks");
        var level_id = '';
        var userAllocationTable = $('#userAllocationTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('userAllocation.getAllocations') }}",
                "type": "GET",
                "data": function(d) {}
            },
            "columns": [
                    {
                        data: null,  // Instead of using 'formatted_id', use a null value here
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart; 
                        },
                        orderable: false
                    },{
                        "data": "name"
                    },{
                        "data": "email"
                    },
                    {
                        "data": "allocated_users_count",
                         "render": function(data, type, row) {
                            return `<span class="">${data}</span>`;
                        }
                    },
                    {
                        "data": "roles_list"
                    },{
                        "data": "status",
                        "render": function(data, type, row) {
                            return `<span class="${data == 1 ? 'activeBadge' : 'inactiveBadge'}">
                                        ${data == 1 ? 'Active' : 'Inactive'}
                                    </span>`;
                        }
                    },
                   {
                    "data": null,
                    "render": function(data, type, row) {
                        var return_val = `
                            <div class="dropdown text-center">
                                <i class="btn btn-light fa fa-ellipsis-v text-primary cursor-pointer fs-5" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                <ul class="dropdown-menu p-0 overflow-hidden">
                                    <li>
                                        <a class="dropdown-item allocation_popup cursor-pointer" data-id="${data.id}">
                                            <i class="bi bi-info-circle text-warning fs-5 me-2"></i> View Details
                                        </a>
                                    </li>
                                    <li class="border-top">
                                        <a class="dropdown-item activity_list cursor-pointer" data-id="${data.id}">
                                            <i class="fa fa-chart-line text-primary fs-5 me-2"></i> Activity List
                                        </a>
                                    </li>
                                    <li class="border-top">
                                        <a class="dropdown-item task_list cursor-pointer" data-id="${data.id}">
                                            <i class="fa fa-tasks text-primary fs-5 me-2"></i> Tasks List
                                        </a>
                                    </li>
                                    @if($authUser->can('case_allocation.assign_task'))
                                        <li class="border-top">
                                            <a class="dropdown-item ps-2" href="{{ route('userAllocation.assignTasks', ':id') }}" data-id="${data.id}">
                                                <span class="fa-stack">
                                                    <i class="bi bi-journal-plus fa-stack-1x text-primary"></i>
                                                    <i class="fa fa-plus fa-stack-2x text-danger" style="font-size: 0.5em; margin-left: 5px;"></i>
                                                </span> Assign Task
                                            </a>
                                        </li>
                                    @endif
                                    @if($authUser->can('case_allocation.assign_activities'))
                                        <li class="border-top">
                                            <a class="dropdown-item ps-2" href="{{ route('userAllocation.assignActivities', ':id') }}" data-id="${data.id}">
                                               <span class="fa-stack">
                                                    <i class="fa fa-chart-line fa-stack-1x text-primary"></i>
                                                    <i class="fa fa-plus fa-stack-2x text-danger" style="font-size: 0.5em; margin-left: 5px;"></i>
                                                </span> Assign Activities
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>`;

                        return return_val.replaceAll(':id', data.id);
                    }
                }

                ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
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
                $('#userAllocationTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });

        $(document).on('click','.allocation_popup, .back-to-hierarchy', function() {
            if (typeof resetEmptyModal === 'function') {
                resetEmptyModal();
            }
            const classList = $(this).attr('class');
            level_id = classList.includes('allocation_popup') ? $(this).data('id') : level_id;
            getUserHierchy(level_id);
        });

        $(document).on('click', '#user_hierarchy .level-add', function() {
            var id = $(this).closest('.treeview__level_id').data('id');
            if (typeof resetEmptyModal === 'function') {
                resetEmptyModal();
            }
            getAllocatedUser(id);
        });

        $(document).on('submit', '#allotedUsersForm', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            var parent = $(this).find('input[name="parent"]').val();
            var url = $(this).attr('action');
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.status) {
                        if (typeof resetEmptyModal === 'function') {
                            resetEmptyModal();
                        }
                        getUserHierchy(level_id);
                        userAllocationTable.ajax.reload();
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        });

        $(document).on('click', '#user_hierarchy .level-remove', function () {
            var parent_id = $(this).closest('ul').closest('.treeview__level_id').data('id');
            var id = $(this).closest('.treeview__level_id').data('id');
            $('#confirmModal .modal-body').html('Are you sure you want to remove that user from allocation?');
            $('#confirmModal').modal('show');
            $('#confirmModal').data('removeId', id);
            $('#confirmModal').data('parent_id', parent_id);
        });

        $(document).on('click', '#confirmButton', function () {
            var id = $('#confirmModal').data('removeId');
            var parent_id = $('#confirmModal').data('parent_id');
            if (id) {
                removeUser(id, parent_id);
            }
            $('#confirmModal').modal('hide');
        });

        $(document).on('click', '.activity_list', function() {
            var id  = $(this).data('id');
            getActivityList(id);
        });

        $(document).on('click', '.task_list', function() {
            var id  = $(this).data('id');
            getTaskList(id);
        });

        $(document).on('click', '.task_details', function() {
            var process_id = $(this).data('id');
            var user_id = $(this).data('userid');
            getTaskDetails(process_id, user_id);
        });

        $(document).on('click', '.back-to-task-list', function() {
            var user_id = $(this).data('user');
            getActivityList(user_id);
        });

        function removeUser(id, parent_id) {
            if (typeof resetEmptyModal === 'function') {
                resetEmptyModal();
            }
            $.ajax({
                    url: '{{ route("userAllocation.removeUser") }}',
                    type: 'Post',
                    data: { id:id , parent:parent_id, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message, 'Success');
                        } else {
                           toastr.error(response.message, 'Error'); 
                        }
                        getUserHierchy(level_id);
                        userAllocationTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON.message, 'Error');
                    }
            });
        }

        function getUserHierchy(id) {
            $('#emptyModalLabel').html('User Hierarchy');
            $('#emptyModal').modal('show');
            $.ajax({
                    url: '{{ route("userAllocation.getAllocatedUsers") }}',
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
        }

        function getAllocatedUser(id) {
            $('#emptyModalLabel').html('<i class="bi bi-arrow-left-circle-fill me-2 back-to-hierarchy fs-3" data-id="'+id+'"></i>User Hierarchy');
            $('#emptyModal').modal('show');
            $.ajax({
                    url: '{{ route("userAllocation.getAllocatedUserlist") }}',
                    type: 'GET',
                    data: { id:id , _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.status) {
                            $('#emptyModal .modal-body').html(response.html);
                            if ($(".user_hierarchy").data('select2')) {
                                $(".user_hierarchy").select2("destroy");
                            }
                            setTimeout(function() {
                                $(".user_hierarchy").select2({
                                    closeOnSelect: true,
                                    placeholder: "Select User",
                                    allowClear: true,
                                    tags: true
                                });
                            }, 50);
                        } else {
                            $('#emptyModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        $('#emptyModal').modal('hide');
                        toastr.error(xhr.responseJSON.message, 'Error');
                    }
            });
        }

        function getActivityList(id) {
            $('#emptyModal').modal('show');
            var url = '{{ route("userAllocation.getUserActivityList", "/") }}';
            $.ajax({
                    url: url+'/'+id,
                    type: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $('#emptyModalLabel').html(response.user_name+' Activity Lists ');
                            $('#emptyModal .modal-body').html(response.html);
                        }
                    },
                    error: function(xhr) {
                       toastr.error(xhr.responseJSON.message, 'Error');
                    }
            });
        }

        function getTaskList(id) {
            $('#emptyModal').modal('show');
            var url = '{{ route("userAllocation.getUserTaskList", "/") }}';
            $.ajax({
                    url: url+'/'+id,
                    type: 'GET',
                    success: function(response) {
                        if (response.status) {
                            $('#emptyModalLabel').html(response.user_name+' Task Lists ');
                            $('#emptyModal .modal-body').html(response.html);
                            $('#AssignTaskTable').DataTable({
                                "processing": true,
                                "serverSide": false,
                                "searching": true,
                                "ordering": false,
                                "paging": true,
                                "info": true,
                                "lengthChange": false
                            });
                        }
                    },
                    error: function(xhr) {
                       toastr.error(xhr.responseJSON.message, 'Error');
                    }
            });
        }

        function getTaskDetails(id, user_id) {
            $('#emptyModalLabel').html('Task List');

            var activity = $('.task_details[data-id="'+id+'"]').closest('tr').find('.activity').html();
            $('#emptyModalLabel').html('<i class="bi bi-arrow-left-circle-fill me-2 back-to-task-list cursor-pointer fs-3" data-id="'+id+'" data-user="'+user_id+'"></i> '+activity);
            $('#emptyModal').modal('show');
            var url = '{{ route("userAllocation.getUserTaskDetails", "/") }}';
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

        $(document).on('click', '.view_task', function () {
            let headers = [];
            let values = [];

            // Get all headers (th) from the table, except first and last
            $('#AssignTaskTable thead th').each(function (index, element) {
                if (index !== 0 && index !== $('#AssignTaskTable thead th').length - 1) {
                    headers.push($(element).text().trim());
                }
            });

            // Get first row (td) values, except first and last
            $(this).closest('tr').find('td').each(function (index, element) {
                if (index !== 0 && index !== $('#AssignTaskTable tbody tr:first td').length - 1) {
                    values.push($(element).text().trim());
                }
            });

            // Build the vertical table dynamically
            let verticalTable = '<table class="table table-bordered"><tbody>';
            
            headers.forEach((header, index) => {
                let value = values[index] || '';
                let copyIcon = value !== '-' ? `<img class="ms-2 copy_content cursor-pointer" src="/images/copy.svg" alt="copy">` : '';

                verticalTable += `<tr><td>${index+1}</td><td>${header}</td><td><span class="value">${value}</span> ${copyIcon}</td></tr>`;
            });


            verticalTable += '</tbody></table>';

            // Append the new table to a div
            $('#drag .content').html(verticalTable);
            $('#drag').show();
            var oDrag = document.getElementById("drag");
            oDrag.style.left = (document.documentElement.clientWidth - oDrag.offsetWidth) / 2 + "px";
            oDrag.style.top = (document.documentElement.clientHeight - oDrag.offsetHeight) / 2 + "px";
        });
    });
</script>
@endpush