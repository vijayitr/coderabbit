@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
        <div class="d-flex gap-2 align-items-center mb-3 filter-bar justify-content-end">
            @if($authUser->can('ticket.generate'))
                <div class="AddMenu">
                    <a href="#" onclick="event.preventDefault();" class="btn btn-dark py-0 d-flex align-items-center">
                        <i class="bi bi-plus fs-5 me-2"></i>
                        <span>Generate Ticket</span>
                    </a>
                    <ul class="bg-dark">
                        @foreach($forms as $form)
                        <li><a href="{{ route('tickets.create',$form->slug) }}" class="px-3"> {{$form->name}}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($authUser->can('ticket.manage_ticket_form'))
                <div class="">
                    <a href="{{ route('tickets.ticketForms') }}" class="btn btn-dark py-0 d-flex align-items-center">
                        <i class="bi bi-gear fs-5 me-2"></i>
                        <span>Manage Ticket Form</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Table -->
        <table class="table" id="ticketsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>User Name</th>
                    <th>Title</th>
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
    body="Are you sure you want to mark this ticket as complete?" 
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
                url: "{{ route('tickets.getTickets') }}",
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
                { data: 'created_at', name: 'created_at' },
                { data: 'user', name: 'user' },
                { data: 'title', name: 'title' },
                { data: 'status', name: 'status' },
                {
                    data: null,
                    render: function(data, type, row) {
                        let return_val = `<span class="d-flex actions float-start">`;
                        return_val += `<a href="#" data-id="${data.id}" class="btn btn-info btn-sm ticket_chat bi bi-chat-dots text-white"></a>`;
                        return_val += `<a href="#" data-id="${data.id}" class="btn btn-warning btn-sm view_ticket bi bi-eye text-white"></a>`;
                        @if($authUser->can('ticket.status'))
                            if ("{{ $authUser->id }}" == data.user_id && data.status != 'Closed') {

                                return_val += `<button class="btn btn-success btn-sm complete-ticket" data-id="${data.id}">Complete</button>`;
                            }
                        @endif
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
        @if($authUser->can('ticket.status'))
            $(document).on('click', '.complete-ticket', function() {
                let ticketId = $(this).data('id');
                var url = "{{ route('tickets.complete', ':id') }}".replace(':id', ticketId);
                $('#confirmationModal').modal('show');

                $('#confirmDelete').off('click').on('click', function() {
                    $.ajax({
                        url: url,
                        type: 'PATCH',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function(response) {
                            toastr.success(response.message, 'Success');
                            ticketsTable.ajax.reload();
                            $('.complete-ticket').remove();
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong. Please try again later.', 'Error');
                        }
                    });

                });
            });
        @endif
        $(document).on('click', '.ticket_chat', function(e) {
            e.preventDefault();
            ticketId = $(this).attr('data-id');
            viewChat(ticketId);
        });

        $('.chat_notification_status').on('change', function() {
            var value = $('.chat_notification_status').val();
            var task_id = null;
            if (value.includes('__')) {
                var parts = value.split('__');
                var task_id = parts[1];
            }

            if (task_id == ticketId) {
                viewChat(ticketId);
            }
        });

        function viewChat(ticketId) {
            var url = "{{ route('tickets.chat', ':id') }}".replace(':id', ticketId);
            $('#emptyModalLabel').html('Ticket Details');
            $('#emptyModal .modal-header, #emptyModal .modal-footer').addClass('d-none');
            $('#emptyModal .modal-body').addClass('p-0');
            $('#emptyModal').modal('show');
            $.ajax({
                url: url,
                type: 'GET',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status) {
                        $('#emptyModal .modal-body').html(response.html);
                        setTimeout(function() {
                            var chatContainer = $('#emptyModal .modal-body .msger-chat');
                            chatContainer.scrollTop(chatContainer.prop("scrollHeight"));
                        }, 200);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        }

        $(document).on('click', '.view_ticket', function(e) {
            e.preventDefault();
            var ticketId = $(this).attr('data-id');
            viewTicket(ticketId);
        });

        function viewTicket(ticketId) {
            var url = "{{ route('tickets.show', ':id') }}".replace(':id', ticketId);
            var button = '<a href="#" data-id="'+ticketId+'" class="ms-2 btn btn-info btn-sm ticket_chat bi bi-chat-dots text-white"></a>';
            $('#emptyModalLabel').html('Ticket Details '+button);
            $('#emptyModal .modal-header, #emptyModal .modal-footer ').removeClass('d-none');
            $('#emptyModal .modal-body').addClass('p-0');
            $('#emptyModal').modal('show');
            $.ajax({
                url: url,
                type: 'GET',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status) {
                        $('#emptyModal .modal-body').html(response.html);
                        setTimeout(function() {
                            var chatContainer = $('#emptyModal .modal-body .msger-chat');
                            chatContainer.scrollTop(chatContainer.prop("scrollHeight"));
                        }, 200);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        }

        $(document).on('click', '.close_ticket_view', function() {
            $('#emptyModal').modal('hide');
        });

        @if($authUser->can('ticket.reply'))
            $(document).on('submit', '.msger-inputarea', function(e) {
                e.preventDefault();
                var ticketId = $(this).attr('data-id');
                var url = $(this).attr('action');
                var message = $('.msger-input').val();
                var status = $('.msger-status').val();
                $.ajax({
                        url: url,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data:{message:message, status:status},
                        success: function(response) {
                            toastr.success(response.message, 'Success');
                            viewChat(ticketId);
                            ticketsTable.ajax.reload();
                        },
                        error: function(xhr) {
                            toastr.error('Something went wrong. Please try again later.', 'Error');
                        }
                    });
            });
        @endif
    });
</script>

@endpush