@extends('layouts.app')
@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="row manage_users">
    <div class="col-md-12">
       <!--  <div class="d-block mb-3 text-end">
            <button class="export_reports btn btn-success me-2" type="button"><span><i class="fa-solid fa-file-excel"></i> Export Excel</span></button>
            <i class="bi bi-funnel btn btn-outlined filter-btn"></i>
        </div> -->
        <table class="table responsive" id="reportTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>User</th>
                    <th>Department</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Total Time
                        <br>
                    <div class="table_time_format">(HH:MM:SS)</div></th>
                    <th class="action-box">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $value)
                @php
                    $duration = Carbon::parse($value->start_time)->diff(Carbon::parse($value->end_time))->format('%H:%I:%S');
                @endphp

                     <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $value->created_at->format('d-M-Y') }}</td>
                        <td>{{ $value->user->name ?? 'N/A' }}</td>
                        <td>{{ $value->department->field_name ?? 'N/A' }}</td>
                        <td>{{ $value->task->option_value ?? 'N/A' }}</td>
                        <td>{{ $value->end_time == null ? 'Pending' : 'Completed' }}</td>
                        <td>{{ date('h:i:s A', strtotime($value->start_time)) }}</td>
                        <td>{{ $value->end_time != null ? date('h:i:s A', strtotime($value->end_time)) : '-' }}</td>
                        <td>{{ $duration}}</td>
                        <td>
                            <i class="bi bi-info-circle text-warning cursor-pointer fs-5 report_info" title="View Details" data-id="{{$value->id}}"></i>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->

<div class="modal fade" id="ReportModal" tabindex="-1" aria-labelledby="ReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-5" id="ReportModalLabel">Report Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="reportTable_processing" class="dataTables_processing card" role="status"><div><div></div><div></div><div></div><div></div></div></div>
                <div class="report-details-container"></div>
            </div>
            <div class="modal-footer d-block border-0">
                <button type="button" class="btn btn-outlined float-end" data-bs-dismiss="modal">Discard</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    var reportTable = '';
    var clientOptions = {};
    $(document).ready(function() {
        reportTable = $('#reportTable').DataTable({
            "lengthChange": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function(settings) {
                $('.page-numbers').remove();
                $('#reportTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });
    });

    $(document).on('click', '.report_info', function() {
        $('#ReportModal').addClass('loading');
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("reports.getReportsDetails") }}',
            type: 'GET',
            data: { id: id, _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    $('#ReportModal').removeClass('loading');
                    $('.report-details-container').html(response.html);
                } else {
                    toastr.error(response.message, 'Error');
                }
            },
            error: function(xhr) {
                toastr.error('Something went wrong. Please try again later.', 'Error');
            }
        });

        $('#ReportModal').modal('show');
    });
</script>
@endpush