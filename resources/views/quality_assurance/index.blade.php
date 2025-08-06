@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">
        @if($authUser->can('call_allocation.assign_calls'))
            <div class="d-flex gap-2 align-items-center mb-3 filter-bar">
                <div class="ms-auto">
                    <a href="{{ route('qc-parameters.index') }}" class="btn btn-dark py-0 me-2 align-items-center">
                        <i class="bi bi-gear fs-5 me-1"></i>
                        <span>Manage QC Paramaters</span>
                    </a>
                    <a href="{{ route('qualityAssurance.CallAllocations') }}" class="btn btn-dark py-0  align-items-center">
                        <i class="bi bi-box-arrow-in-down fs-5 me-1"></i>
                        <span>Assign Calls</span>
                    </a>
                </div>

            </div>
        @endif
        <table class="table w-100" id="CallsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Client Name</th>
                    <th>Process Name</th>
                    <th class="">Status</th>
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

<x-emptyModal />

@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        let callsTable;
        callsTable = $('#CallsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('qualityAssurance.getAssignedCalls') }}",
                "type": "GET",
                "data": function(d) {
                    d.start_date = $('input.start_date').val();
                    d.end_date = $('input.end_date').val();
                    d.user = $('#user-selection').val();
                    d.is_assigned = $('#is_assigned').val();
                    // d.date_filter = dateFilter; 
                    // d.sort_by = sortBy; 
                    // d.workflow_name = $('#workflow_filter').val(); 
                    // d.client_id = $('#client_filter').val(); 
                },
                dataSrc: function(json) {
                    totalRecords = json.recordsTotal;
                    return json.data;
                }
            },
            "columns": [
                {
                    data: null,  // Instead of using 'formatted_id', use a null value here
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart; 
                    },
                    orderable: false
                },
                { data: 'created_at' },
                { data: "client_name" },
                { data: 'activity_name' },
                {
                    data: 'status',
                    render: function(data) {
                        const statusMap = {
                            pending: { label: 'Pending', class: 'text-warning' },
                            in_progress: { label: 'In Progress', class: 'text-primary' },
                            completed: { label: 'Completed', class: 'text-success' }
                        };
                        const { label, class: cls } = statusMap[data] || { label: 'Unknown', class: 'text-muted' };
                        return `<span class="fw-bold ${cls}">${label}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        // totalRecords = 
                        var url = "{{ route('qualityAssurance.scoringForm','') }}";
                        var return_val = `
                            <a class="dropdown-item cursor-pointer play_call d-inline-block w-auto" title="Play Recording" data-id="${data.id}" data-recording-id="${data.recording_id}">
                                <i class="bi bi-play-circle text-success fs-5 me-2"></i>
                            </a>`;
                            @if($authUser->can('call_allocation.score_call'))

                            return_val += `<a href="${url+'/'+data.id}" class="dropdown-item cursor-pointer d-inline-block w-auto" title="Scoring" data-id="${data.id}">
                                <i class="bi bi-trophy-fill text-primary fs-5 me-2"></i> 
                            </a>`;

                            if (data.status == 'completed') {
                                return_val += `<a class="dropdown-item cursor-pointer release_btn d-inline-block w-auto" title="Release" data-id="${data.id}">
                                    <i class="fa fa-unlock text-success fs-5 me-2"></i>
                                </a>`;
                            }

                        @endif
                        return return_val;
                        var return_val = `
                            <div class="dropdown">
                                <i class="btn btn-light fa fa-ellipsis-v text-primary cursor-pointer fs-5" data-bs-toggle="dropdown" aria-expanded="false"></i>
                                <ul class="dropdown-menu p-0 overflow-hidden">`;

                        return_val += `
                                <li class="border-top">
                                    <a class="dropdown-item cursor-pointer play_call" data-id="${data.id}" data-recording-id="${data.recording_id}">
                                        <i class="bi bi-play-circle text-success fs-5 me-2"></i> Play
                                    </a>
                                </li>`;
                        @if($authUser->can('call_allocation.score_call'))
                            return_val += `
                                    <li class="border-top">
                                        <a href="${url+'/'+data.id}" class="dropdown-item cursor-pointer" data-id="${data.id}">
                                            <i class="bi bi-trophy-fill text-primary fs-5 me-2"></i> Scoring
                                        </a>
                                    </li>`;
                            if (data.status == 'completed') {
                                return_val += `
                                    <li class="border-top">
                                        <a class="dropdown-item cursor-pointer release_btn" data-id="${data.id}">
                                            <i class="fa fa-unlock text-success fs-5 me-2"></i> Release
                                        </a>
                                    </li>`;
                            }
                        @endif

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

        $(document).on('click', '#searchTask', function(){
            callsTable.ajax.reload();
        });
        @if($authUser->can('call_allocation.score_call'))
            $(document).on('click', '.release_btn', function(e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                $.ajax({
                    url: '{{ route("qualityAssurance.release") }}',
                    type: 'GET',
                    data: { id:id , _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success');
                            callsTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON.message, 'Error');
                    }
                });
            });
        @endif
    });
</script>
@endpush