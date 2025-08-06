@extends('layouts.app')

@section('content')
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar">

            <!-- Delete Button -->
            <div class="">
                <a href="#" class="btn btn-danger py-0 d-flex align-items-center clients-delete">
                    <i class="bi bi-trash fs-5 me-1"></i>
                    <span>Delete</span>
                </a>
            </div>

            <div class="ms-auto AddMenu">
                <a href="#" onclick="event.preventDefault();" class="btn btn-dark py-0 d-flex align-items-center">
                    <i class="bi bi-plus fs-5"></i>
                    <span>Add Clients</span>
                </a>
                <ul class="bg-dark">
                    <li><a href="{{ route('clients.create') }}"> <i class="bi bi-plus"></i> Add Clients</a></li>
                    <li><a href="{{ route('clients.import') }}"> <i class="bi bi-box-arrow-in-down"></i> Import Clients</a></li>
                </ul>
            </div>
        </div>

        <!-- Table -->
        <table class="table" id="DataTable">
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
    let DataTable;

    $(document).ready(function() {
        let dateFilter = '';
        let sortBy = '';

        // Initialize DataTable

        DataTable = $('#DataTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('getClients') }}",
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
                                    <input type="checkbox" class="form-check-input table-item-checkbox" value="${data}">
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
                {
                    data: 'client_name',
                    render: function(data) {
                        return `
                            <span class="workflow-name">${data}</span>
                            <span class="copy-icon ms-2 cursor-pointer copy-client" text="${data}">
                              <img src="/images/copy.svg" alt="copy" />
                            </span>`;
                    }
                },
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
                        return_val += `<a href="/clients/${data.id}/edit" class="table-edit">
                                            <img src="{{url('images/icons/table-edit.png')}}" alt="table-edit">
                                        </a>`;
                    @endif

                    // Delete action (if permitted)
                    @if($authUser->can('workflow.delete'))
                        return_val += `<img src="{{url('images/icons/table-delete.png')}}" alt="Client Delete" class="cursor-pointer item-delete" data-id="${data.id}">`;
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

        $(document).on('click', '.copy-client', function() {
            var textArea = document.createElement('textarea');
            textArea.value = $(this).attr('text');
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            toastr.success('Client name copied to clipboard.', 'success');
        });

        // Select/Deselect all checkboxes
        $('#checkAll').on('change', function() {
            const checked = this.checked;
            $('.table-item-checkbox').prop('checked', checked);
            if (checked) {
                toastr.info('You have selected all current page records.', 'Info');
            }
        });

        // Single delete
        $('#DataTable').on('click', '.item-delete', function(e) {
            e.preventDefault();
            var workflowId = $(this).data('id');
            var deleteUrl = '/clients/' + workflowId;
            $('#confirmationModal .modal-body').html('Are you sure you want to delete this records?');
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {

                 $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    success: function(response) {
                        toastr.success(response.message, 'Success');
                        $('#confirmationModal').modal('hide');
                        DataTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });

        // Bulk delete
        $('.clients-delete').on('click', function() {
            const selectedIds = $('.table-item-checkbox:checked').map(function() {
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
                    url: '{{ route("clients.bulkDelete") }}',
                    type: 'DELETE',
                    data: { ids: selectedIds, _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        DataTable.ajax.reload();
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
