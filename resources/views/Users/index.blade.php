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

            <div class="">
                <a href="#" class="btn btn-danger py-0 d-flex align-items-center table-delete">
                    <i class="bi bi-trash fs-5 me-1"></i>
                    <span>Delete</span>
                </a>
            </div>

            <!-- Button container -->
            <div class="ms-auto">
                <a href="/users/create" class="btn btn-dark py-0 d-flex align-items-center">
                    <i class="bi bi-plus fs-5"></i>
                    <span>Add New User</span>
                </a>
            </div>
        </div>

        <table class="table w-100" id="userTable">
            <thead>
                <tr>
                    <th><div class="form-check"><input type="checkbox" class="form-check-input" id="checkAll"></div></th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Last Login</th>
                    <th>Role</th>
                    <th class="text-center">Status</th>
                    @if($authUser->can('user.edit') || $authUser->can('client.assign') || $authUser->can('user.delete'))
                    <th class="action-box">Actions 
                        <img src="{{url('images/icons/table-delete.png')}}" alt="table-delete" class="cursor-pointer table-delete select-all">
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
     @if($authUser->can('client.assign'))
        <!-- Modal -->
        <form id="assignClientsForm" action="{{ route('clients.clientAssign') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="">
            <div class="modal fade" id="assignClientsModal" tabindex="-1" aria-labelledby="assignClientsModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="assignClientsLabel">Assign Clients to [user]</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                  </div>
                </div>
              </div>
            </div>
        </form>
    @endif
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
<x-emptyModal />
<x-confirm-modal />

@endsection
@push('scripts')
<script>
    var userTable = '';
    var clientOptions = {};
    var level_id = '';
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
                        data: null,  // Instead of using 'formatted_id', use a null value here
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart; 
                        },
                        orderable: false
                    },{
                        "data": "name"
                    },{
                        "data": "email"
                    },{
                        "data": "last_login"
                    },{
                        "data": "roles_list"
                    },{
                        "data": "status",
                        "render": function(data, type, row) {
                            return `<span class="${data == 1 ? 'activeBadge' : 'inactiveBadge'}">
                                        ${data == 1 ? 'Active' : 'Inactive'}
                                    </span>`;
                        }
                    },
                    @if($authUser->can('user.edit') || $authUser->can('client.assign') || $authUser->can('user.delete'))
                    {
                        "data": null, 
                        "render": function(data, type, row) {
                            clientOptions[data.id] = data.clients_html;
                            var return_val = `
                            <div class="dropdown text-center actions" data-id="${data.id}" title="${data.id}">
                                <i class="btn btn-light fa fa-ellipsis-v text-primary cursor-pointer fs-5" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                <ul class="dropdown-menu p-0 overflow-hidden">
                                    <li>
                                        <a class="dropdown-item allocation_popup cursor-pointer" data-id="${data.id}">
                                            <i class="bi bi-info-circle text-warning fs-5 me-2"></i> View Details
                                        </a>
                                    </li>`;

                                    @if($authUser->can('user.edit'))
                                        return_val += `<li class="border-top">
                                            <a href="/users/${data.id}/edit" class="dropdown-item cursor-pointer table-edit" data-id="${data.id}">
                                                <img src="{{url('images/icons/table-edit.png')}}" alt="table-edit"> Edit
                                            </a>
                                        </li>`;
                                    @endif

                                    @if($authUser->can('client.assign'))
                                        return_val += `<li class="border-top">
                                            <a href="#" class="dropdown-item cursor-pointer assignClientsModal preventDefault" data-user="${data.name}" data-id="${data.id}" title="Assign Clients">
                                                <i class="bi bi-person-add text-success cursor-pointer fs-5"></i> Assign Clients
                                            </a>
                                        </li>`;
                                    @endif

                                    return_val += `<li class="border-top">
                                            <a href="#" class="dropdown-item cursor-pointer assignUsersModal preventDefault" data-user="${data.name}" data-id="${data.id}" title="Assign Clients">
                                                <i class="bi bi-people text-success cursor-pointer fs-5"></i> Assign Users
                                            </a>
                                        </li>`;

                                    @if($authUser->can('user.delete'))
                                        return_val += `<li class="border-top">
                                            <a href="#" class="dropdown-item cursor-pointer user-delete preventDefault" data-id="${data.id}" title="Delete">
                                                <img src="{{url('images/icons/table-delete.png')}}" alt="User Delete" class="cursor-pointer " data-id="${data.id}"> Delete
                                            </a>
                                        </li>`;
                                    @endif

                                return_val +`</ul></div>`;

                            // var return_val = `
                            //     <span class="d-flex actions" data-id="${data.id}" title="${data.id}">`;
                            //     @if($authUser->can('user.edit'))
                            //         return_val += `<a href="/users/${data.id}/edit" class="table-edit"><img src="{{url('images/icons/table-edit.png')}}" alt="table-edit"></a>`;
                            //     @endif
                            //     @if($authUser->can('client.assign'))
                            //     return_val += `<i class="bi bi-person-add text-success cursor-pointer fs-5 assignClientsModal" data-user="${data.name}" title="Assign Clients"></i>`;
                            //     @endif
                            //     @if($authUser->can('user.delete'))
                            //         return_val += `<img src="{{url('images/icons/table-delete.png')}}" alt="User Delete" class="cursor-pointer user-delete" data-id="${data.id}">`;
                            //     @endif
                            //     return_val += `</span>`;

                            return return_val;
                        }
                    }
                     @endif
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

    $(document).on('click','.assignUsersModal, .back-to-hierarchy', function() {
        if (typeof resetEmptyModal === 'function') {
            resetEmptyModal();
        }
        const classList = $(this).attr('class');
        level_id = classList.includes('assignUsersModal') ? $(this).data('id') : level_id;
        getUserHierchy(level_id);
    });

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

    $(document).on('click', '#user_hierarchy .level-add', function() {
        var id = $(this).closest('.treeview__level_id').data('id');
        if (typeof resetEmptyModal === 'function') {
            resetEmptyModal();
        }
        getAllocatedUser(id);
    });

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

    $(document).on('click', '.preventDefault', function(e){
        e.preventDefault();
    });
    
    @if($authUser->can('client.assign'))
        $(document).on('click', '.assignClientsModal', function() {
            var userName = $(this).data('user');
            var user_id = $(this).closest('.actions').data('id');
            const decodedHtml = atob(clientOptions[user_id]);
            $('#assignClientsModal .modal-body').html(decodedHtml);
            $('#assignClientsLabel').html('Assign Clients to '+userName);
            $('#assignClientsModal').modal('show');

            $('#assignClientsForm input[name="user_id"]').val(user_id);
            
            // Destroy previous instance if it exists
            if ($(".clientDropdown").data('select2')) {
                $(".clientDropdown").select2('destroy');
            }
            // Reinitialize select2
            $(".clientDropdown").select2({
                closeOnSelect: false,
                placeholder: "Select client",
                allowClear: true,
                tags: true
            });
        });
    @endif

    $(document).on('click', '.user-delete', function() {
        var user_id = $(this).attr('data-id');
        $('#confirmationModal .modal-body').html('Are you sure you want to delete this records?');
        $('#confirmationModal').modal('show');

        $('#confirmDelete').off('click').on('click', function() {
           $.ajax({
                url: '{{ url("users") }}/' + user_id, // Add the user ID to the URL
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}' 
                },
                success: function(response) {
                    userTable.ajax.reload(); 
                    $('#confirmationModal').modal('hide');
                    toastr.success(response.message, 'Success'); // Show success message from response
                },
                error: function(xhr) {
                    toastr.error('An error occurred while deleting the user.', 'Error');
                }
            });

        });

    });
    $(document).on('click','.table-delete', function(e) {
        e.preventDefault();
        const selectedIds = $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            toastr.warning('No users selected for deletion.', 'Warning');
            return;
        }

        $('#confirmationModal .modal-body').html('Are you sure you want to delete the selected records?');
        $('#confirmationModal').modal('show');

        $('#confirmDelete').off('click').on('click', function() {
           $.ajax({
                url: '{{ route("users.bulkDelete") }}',
                type: 'DELETE',
                data: {
                    ids: selectedIds,  // Make sure selectedIds contains valid user IDs
                    _token: '{{ csrf_token() }}' 
                },
                success: function(response) {
                    userTable.ajax.reload(); 
                    $('#confirmationModal').modal('hide');
                    toastr.success(response.message, 'Success');
                },
                error: function(xhr) {
                    toastr.error('An error occurred while deleting users.', 'Error');
                }
            });

        });
    });

</script>
@endpush