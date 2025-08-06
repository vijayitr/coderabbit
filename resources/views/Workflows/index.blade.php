@extends('layouts.app')

@section('content')
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar">

            <!-- Delete Button -->
            <div class="">
                <a href="#" class="btn btn-danger py-0 d-flex align-items-center workflows-delete">
                    <i class="bi bi-trash fs-5 me-1"></i>
                    <span>Delete</span>
                </a>
            </div>

            <div class="col-2 ms-2">
                <select class="form-control bg-white select2" id="client_filter">
                    <option value="">-- Select Client --</option>
                    @foreach($clients as $client)
                        <option value="{{$client->id}}">{{$client->client_name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2 ms-2">
                <select class="form-control bg-white select2" id="workflow_filter">
                    <option value="">-- Select Workflow --</option>
                    
                </select>
            </div>
            <div class="d-flex ms-auto gap-2">
                <!-- Globle QC Module Button -->
                <a href="#" onclick="event.preventDefault();" class="btn btn-dark py-0 d-flex align-items-center globle_qc_modal">
                    <i class="bi bi-plus fs-5"></i>
                    <span class="ms-1">Globle QC Module</span>
                </a>

                <!-- Add Workflow Button with Dropdown -->
                <div class="dropdown">
                    <a href="#" onclick="event.preventDefault();" class="btn btn-dark py-0 d-flex align-items-center dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-plus fs-5"></i>
                        <span class="ms-1">Add Workflow</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="{{ route('workflows.create') }}"><i class="bi bi-plus me-1"></i> Add Workflow</a></li>
                        <li><a class="dropdown-item" href="{{ route('workflows.import') }}"><i class="bi bi-box-arrow-in-down me-1"></i> Import Workflow</a></li>
                    </ul>
                </div>
            </div>


        </div>

        <!-- Table -->
        <table class="table" id="workflowTable">
            <!-- Add Status Column in Table Header -->
            <thead>
                <tr>
                    <th>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="checkAll">
                        </div>
                    </th>
                    <th>#</th>
                    <th>Client Name</th>
                    <th>Workflow Name</th>
                    <th>Created By</th>
                    <th>Date Created</th>
                    <th>Status</th> <!-- New Column for Status -->
                    <th class="action-box">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Confirmation Modal -->
@php
$buttons = [
    ['label' => 'Confirm', 'class' => 'btn-primary', 'id' => 'confirmDelete'],
    ['label' => 'Cancel', 'class' => 'btn-secondary'],
];
@endphp
<x-modal 
    id="confirmationModal" 
    title="Confirm Deletion" 
    body="Are you sure you want to delete the selected records?" 
    :buttons="$buttons"
/>
<x-emptyModal />
@endsection

@push('scripts')
<script>
    let workflowTable;

    var workflows = [];
    @foreach($workflows as $workflow)
        workflows.push({
            id: '{{ $workflow->id }}',
            workflow_name: '{{ $workflow->workflow_name }}',
            client_id: '{{ $workflow->client_id }}'
        });
    @endforeach

    $(document).ready(function() {
        let dateFilter = '';
        let sortBy = '';

        // Initialize DataTable

        workflowTable = $('#workflowTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('getWorkflows') }}",
                "type": "GET",
                "data": function(d) {
                    d.date_filter = dateFilter; 
                    d.sort_by = sortBy; 
                    d.workflow_name = $('#workflow_filter').val(); 
                    d.client_id = $('#client_filter').val(); 
                }
            },
            "columns": [
                {
                    data: 'id',
                    render: function(data) {
                        return `<div class="form-check">
                                    <input type="checkbox" class="form-check-input workflow-checkbox" value="${data}">
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
                { data: 'client_name' },
                {
                    data: 'workflow_name',
                    render: function(data, type, row) {
                        return `
                            <a href="/workflows/${row.id}/edit" class="text-decoration-none text-dark">
                               ${data}
                            </a>
                        `;
                    }
                },
                { "data": "created_by" },
                { data: 'created_at' }, // Formatted creation date
                { 
                    data: 'status',  // Add Status column
                    render: function(data) {
                        const badgeClass = data === 'Enabled' ? 'text-success' : 'text-danger';
                        return `<span class="fw-bold ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        var return_val = `
                            <div class="dropdown text-center">
                                <i class="btn btn-light fa fa-ellipsis-v text-primary cursor-pointer fs-5" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                <ul class="dropdown-menu p-0 overflow-hidden">`;

                        @if($authUser->can('workflow.edit'))
                            return_val += `
                                <li>
                                    <a class="dropdown-item" href="/workflows/${data.id}/edit">
                                        <i class="bi bi-pencil-square text-primary fs-5 me-2"></i> Edit
                                    </a>
                                </li>`;
                        @endif

                        return_val += `
                                <li class="border-top">
                                    <a class="dropdown-item duplicate_workflow" href="/workflows/${data.id}/duplicate">
                                        <i class="bi bi-files text-primary fs-5 me-2"></i> Duplicate
                                    </a>
                                </li>`;

                        return_val += `
                            <li class="border-top">
                                <a class="dropdown-item change_global_qc_status" data-status="${data.qc_enabled}" href="/workflows/${data.id}/global-qc-status">
                                    <i class="fas fa-keyboard ${data.qc_enabled ? 'text-success' : 'text-danger'} fs-5 me-2"></i>
                                    ${data.qc_enabled ? 'Global QC Enabled' : 'Global QC Disabled'}
                                </a>
                            </li>`;


                        @if($authUser->can('workflow.delete'))
                            return_val += `
                                <li class="border-top">
                                    <a class="dropdown-item cursor-pointer workflow-delete" data-id="${data.id}">
                                        <i class="bi bi-trash text-danger fs-5 me-2"></i> Delete
                                    </a>
                                </li>`;
                        @endif

                        return_val += `
                                <li class="border-top">
                                    <a class="dropdown-item cursor-pointer workflow-export" data-id="${data.id}">
                                        <i class="bi bi-box-arrow-up text-danger fs-5 me-2"></i> Export
                                    </a>
                                </li>`;

                        return_val += `
                                </ul>
                            </div>`;
                        
                        return return_val;
                    },
                    orderable: false
                }

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

        $(document).on('click','.globle_qc_modal', function() {
            if (typeof resetEmptyModal === 'function') {
                resetEmptyModal();
            }
            // const classList = $(this).attr('class');
            // level_id = classList.includes('allocation_popup') ? $(this).data('id') : level_id;
            getGlobleQCFields();
        });


        function getGlobleQCFields() {
            $('#emptyModalLabel').html('Globle QC Dynamic Fields');
            $('#emptyModal .modal-body').addClass('border border-secondary-subtle');
            $('#emptyModal .modal-footer').append('<button type="button" class="btn btn-primary global_qc_submit_btn  float-end">Submit</button>');
            $('#emptyModal').modal('show');
            $.ajax({
                    url: '{{ route("workflows.getGlobleQCFields") }}',
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

    $(document).on('click', '.workflow-export', function() {
        var id = $(this).attr('data-id');
        var url = '{{ route("workflows.taskSampleExport", '') }}';
        // console.log(url+'/'+id);
        // if (client != '') {
            window.open(url+'/'+id, "_blank");
        // }
    })


        // Select/Deselect all checkboxes
        $('#checkAll').on('change', function() {
            const checked = this.checked;
            $('.workflow-checkbox').prop('checked', checked);
            if (checked) {
                toastr.info('You have selected all current page records.', 'Info');
            }
        });

        // duplicate_workflow
        $('#workflowTable').on('click', '.duplicate_workflow', function(e) {
            e.preventDefault();
            var duplicateUrl = $(this).attr('href');
            $('#confirmationModal .modal-body').html('Are you sure you want to Duplicate this records?');
            $('#confirmationModalLabel').html('Confirm Duplication');
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {

                 $.ajax({
                    url: duplicateUrl,
                    type: 'POST',
                    success: function(response) {
                        toastr.success(response.message, 'Success');
                        $('#confirmationModal').modal('hide');
                        workflowTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });

        // duplicate_workflow
        $('#workflowTable').on('click', '.change_global_qc_status', function(e) {
            e.preventDefault();
            var status = $(this).attr('data-status');
            var message = status == 'false' ? 'enable' : 'disable';
            var qcStatusUrl = $(this).attr('href');
            $('#confirmationModal .modal-body').html('Are you sure you want to '+message+' global qc fields for that workflow');
            $('#confirmationModalLabel').html('Global QC Status');
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {
                 $.ajax({
                    url: qcStatusUrl,
                    type: 'POST',
                    success: function(response) {
                        toastr.success(response.message, 'Success');
                        $('#confirmationModal').modal('hide');
                        workflowTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });

        // Single delete
        $('#workflowTable').on('click', '.workflow-delete', function(e) {
            e.preventDefault();
            var workflowId = $(this).data('id');
            var deleteUrl = '/workflows/' + workflowId;
            $('#confirmationModal .modal-body').html('Are you sure you want to delete this records?');
            $('#confirmationModalLabel').html('Confirm Deletion');
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {

                 $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    success: function(response) {
                        toastr.success(response.message, 'Success');
                        $('#confirmationModal').modal('hide');
                        workflowTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });

        $(document).on('change', '#client_filter', function() {
            var client_id = $(this).val();

            var filteredWorkflows = workflows.filter(function(workflow) {
                return workflow.client_id == client_id;
            });

            // If you want to populate a dropdown with these workflows:
            var $workflowFilter = $('#workflow_filter');
            $workflowFilter.empty().append('<option value="">Select Workflow</option>');

            filteredWorkflows.forEach(function(workflow) {
                $workflowFilter.append('<option value="' + workflow.id + '">' + workflow.workflow_name + '</option>');
            });
            workflowTable.ajax.reload();
        });


        $(document).on('change', '#workflow_filter', function() {

            workflowTable.ajax.reload();
        });


        $(document).on('click', '.workflow-export', function() {
            var workflowId = $(this).data('id');
        })

        // Bulk delete
        $('.workflows-delete').on('click', function() {
            const selectedIds = $('.workflow-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (selectedIds.length === 0) {
                toastr.warning('No workflows selected for deletion.', 'Warning');
                return;
            }

            $('#confirmationModal .modal-body').html('Are you sure you want to delete the selected records?');
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {
                $.ajax({
                    url: '{{ route("workflows.bulkDelete") }}',
                    type: 'DELETE',
                    data: { ids: selectedIds, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        workflowTable.ajax.reload();
                        $('#confirmationModal').modal('hide');
                        toastr.success(response.message, 'Success');
                    },
                    error: function() {
                        toastr.error('Failed to delete selected workflows.', 'Error');
                    }
                });
            });
        });
    });
</script>
@endpush
