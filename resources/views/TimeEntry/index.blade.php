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

            <!-- Add Workflow Button -->
            <div class="ms-auto">
                <a href="{{ route('workflows.create') }}" class="btn btn-dark py-0 d-flex align-items-center">
                    <i class="bi bi-plus fs-5"></i>
                    <span>Add Workflow</span>
                </a>
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
@endsection

@push('scripts')
<script>
    let workflowTable;

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
                { data: 'workflow_name' },
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
                    var return_val = `<span class="d-flex actions">`;

                    // Edit action (if permitted)
                    @if($authUser->can('workflow.edit'))
                        return_val += `<a href="/workflows/${data.id}/edit" class="table-edit">
                                            <img src="{{url('images/icons/table-edit.png')}}" alt="table-edit">
                                        </a>`;
                    @endif

                    // Delete action (if permitted)
                    @if($authUser->can('workflow.delete'))
                        return_val += `<img src="{{url('images/icons/table-delete.png')}}" alt="Workflow Delete" class="cursor-pointer workflow-delete" data-id="${data.id}">`;
                    @endif

                    return_val += `</span>`;
                    return return_val;
                },
                orderable: false
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
                $('#userTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });


        // Select/Deselect all checkboxes
        $('#checkAll').on('change', function() {
            const checked = this.checked;
            $('.workflow-checkbox').prop('checked', checked);
            if (checked) {
                toastr.info('You have selected all current page records.', 'Info');
            }
        });

        // Single delete
        $('#workflowTable').on('click', '.workflow-delete', function(e) {
            e.preventDefault();
            var workflowId = $(this).data('id');
            var deleteUrl = '/workflows/' + workflowId;
            $('#confirmationModal .modal-body').html('Are you sure you want to delete this records?');
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
