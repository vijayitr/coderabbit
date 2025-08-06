@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">

        <div class="d-flex gap-2 align-items-center mb-3 filter-bar justify-content-end">
            <div class="">
                <a href="{{ route('tickets.addTicketForms') }}" class="btn btn-dark py-0 d-flex align-items-center">
                    <i class="bi bi-plus fs-5 me-2"></i>
                    <span>Add Form</span>
                </a>
            </div>
        </div>
        <!-- Table -->
        <table class="table" id="ticketsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Form Title</th>
                    <th>Status</th>
                    <th width="10%">Actions</th>
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
    title="Confirm" 
    body="Are you sure you want to delete this form?" 
    :buttons="$buttons" 
/>
<x-emptyModal />
@endsection
@push('scripts')
<script>
    let ticketsTable;
    var ticketId = '';
    $(document).ready(function() {
        ticketsTable = $('#ticketsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('tickets.getForms') }}",
                type: "GET"
            },
            columns: [
                {
                    data: null,  // Instead of using 'formatted_id', use a null value here
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart; 
                    },
                    orderable: false
                },
                { data: 'name', name: 'name' },
                { data: 'form_details', name: 'form_details' },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (data.status == 1) {
                            return `<span class="activeBadge"> Active </span>`;
                        } else {
                            return `<span class="inactiveBadge"> Inactive </span>`;
                        }
                    }
                },
                {
                        "data": null, 
                        "render": function(data, type, row) {
                            var return_val = `
                                <span class="d-flex actions" data-id="${data.id}" title="${data.id}">`;
                                    return_val += `<a href="/tickets/ticket-form/${data.id}" class="table-edit"><img src="{{url('images/icons/table-edit.png')}}" alt="table-edit"></a>`;
                                    return_val += `<a href="/tickets/create-form-fields/${data.slug}" class="table-edit"><i class="bi bi-gear fs-5 me-2"></i></a>`;
                                    return_val += `<img src="{{url('images/icons/table-delete.png')}}" alt="form Delete" class="cursor-pointer delete-form" data-id="${data.id}">`;
                                return_val += `</span>`;

                            return return_val;
                        }
                    }

            ],
            searching: true,
            paging: true,
            lengthChange: false,
            // order: [[4, 'desc']], // Sort by latest created date
            ordering: false,
            dom: 'Bfrtip',
            buttons: ['csv', 'excel'],
            info: false,
            language: {
                paginate: {
                    previous: '<i class="fa-solid fa-arrow-left"></i> Previous',
                    next: 'Next <i class="fa-solid fa-arrow-right"></i>'
                }
            },
            drawCallback: function(settings) {
                $('.page-numbers').remove();
                $('#ticketsTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });
        $(document).on('click', '.delete-form', function() {
            let formId = $(this).data('id');
            var url = "{{ route('tickets.forms.destroy', ':id') }}".replace(':id', formId);
            $('#confirmationModal').modal('show');

            $('#confirmDelete').off('click').on('click', function() {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        toastr.success(response.message, 'Success');
                        ticketsTable.ajax.reload();
                    },
                    error: function(xhr) {
                        toastr.error('Something went wrong. Please try again later.', 'Error');
                    }
                });

            });
        });
    });
</script>

@endpush