@extends('layouts.app')

@section('content')
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar">
            <x-filter-select
                label="Date"
                :options="['Last Week', 'Last Month', 'Last Year', 'Show All']"
                selectedOption="Last Week"
                id="dateDropdown"/>

            <x-filter-select
                label="Sort By"
                :options="['Z to A', 'Date Added']"
                selectedOption="Date Added"
                id="sortByDropdown"/>
        </div>

        <table class="table" id="userTable">
            <thead>
                <tr>
                    <th><div class="form-check"><input type="checkbox" class="form-check-input" id="checkAll"></div></th>
                    <th>Manager Id</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Last Login</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="action-box">Actions 
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

<x-modal 
    id="confirmationModal" 
    title="Confirm Deletion" 
    body="Are you sure you want to delete the selected records?" 
    :buttons="$buttons" 
/>

@endsection
@push('scripts')
<script>
    var userTable = '';
    $(document).ready(function() {
        let dateFilter = ''; 
        let sortBy = ''; 
        
        $('#dateDropdown .option').on('click', function() {
            dateFilter = $(this).data('value');
            $('#dateDropdown .selected-option strong').text(dateFilter);
            userTable.ajax.reload(); 
        });
        
        $('#sortByDropdown .option').on('click', function() {
            sortBy = $(this).data('value');
            $('#sortByDropdown .selected-option strong').text(sortBy);
            userTable.ajax.reload(); 
        });

        userTable = $('#userTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('getUsers') }}",
                "type": "GET",
                "data": function(d) {
                    d.date_filter = dateFilter; 
                    d.sort_by = sortBy; 
                }
            },
            "columns": [{
                        "data": null, 
                        "render": function(data, type, row) {
                            return `<div class="form-check"><input type="checkbox" class="form-check-input user-checkbox" value="${row.id}"></div>`;
                        }
                    },{
                        "data": null, 
                        "render": function(data, type, row) {
                            return `
                                <img src="${row.avatar_url}" alt="User Avatar" class="shadow-sm user-avatar-table">
                                ${row.formatted_id}
                            `;
                        }
                    },{
                        "data": "name"
                    },{
                        "data": "email"
                    },{
                        "data": "last_login"
                    },{
                        "data": "role"
                    },{
                        "data": "status",
                        "render": function(data, type, row) {
                            return `<span class="${data == 1 ? 'activeBadge' : 'inactiveBadge'}">
                                        ${data == 1 ? 'Active' : 'Inactive'}
                                    </span>`;
                        }
                    },{
                        "data": null, 
                        "render": function(data, type, row) {
                            return `
                                <span class="d-flex actions">
                                    <a href="/edit/${data.id}" class="table-edit"><img src="{{url('images/icons/table-edit.png')}}" alt="table-edit"></a>
                                    <img src="{{url('images/icons/table-delete.png')}}" alt="table-delete" class="cursor-pointer table-delete">
                                </span>
                            `;
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
                $('#userTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });

        // Check-delete
        $('#checkAll').on('change', function() {
            const checked = this.checked;
            $('.user-checkbox').prop('checked', checked);

            if (checked) {
                toastr.info('You have selected all current page records.', 'Info', {
                    closeButton: true,
                    progressBar: true,
                });
                $(".select-all").show();
            }else{
                $(".select-all").hide();
            }
        });

    });
    $(document).on('click','.table-delete', function() {
        const selectedIds = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            toastr.warning('No users selected for deletion.', 'Warning');
            return;
        }

        $('#confirmationModal').modal('show');

        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: '{{route("users.bulkDelete")}}', 
                type: 'DELETE',
                data: {
                    ids: selectedIds,
                    _token: '{{ csrf_token() }}' 
                },
                success: function(response) {
                    console.log(response); // Log the response to check if it's correct
                    userTable.ajax.reload(); 
                    $('#confirmationModal').modal('hide');
                    toastr.success(response.message, 'Success'); // Show success message from response
                },
                error: function(xhr) {
                    toastr.error('An error occurred while deleting users.', 'Error');
                }
            });
        });
    });

</script>
@endpush