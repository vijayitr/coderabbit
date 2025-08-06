@extends('layouts.app')

@section('content')
@php
    $permissions = [
        $authUser->can('report.agent_reports'),
        $authUser->can('report.manager_reports'),
        $authUser->can('report.qc_reports'),
    ];
    $trueCount = collect($permissions)->filter()->count();
@endphp
<div class="row manage_users">
    <div class="col-md-12">
        <div class="d-flex justify-content-end align-items-center mb-3">
            @if ($trueCount >= 2) 
            <!-- Toggle Switch Tabs -->
            <div class="btn-group me-3" role="group">
                @if($authUser->can('report.agent_reports'))
                    <button class="toggle-btn btn btn-outline-secondary active" data-target="agentModule">Agent</button>
                @endif
                @if($authUser->can('report.qc_reports'))
                    <button class="toggle-btn btn btn-outline-secondary" data-target="qcModule">QC Agent</button>
                @endif
                @if($authUser->can('report.manager_reports'))
                    <button class="toggle-btn btn btn-outline-secondary" data-target="managerModule">Manager</button>
                @endif
            </div>
            @endif
            @if ($authUser->can('report.agent_reports') || $authUser->can('report.manager_reports')) 
            <button class="export_reports btn btn-success me-2" type="button"><span><i class="fa-solid fa-file-excel"></i> Export Excel</span></button>
            <i class="bi bi-funnel btn btn-outlined filter-btn"></i>
            @endif
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
           @if ($authUser->can('report.agent_reports')) 
            <div class="tab-pane fade {{$authUser->can('report.agent_reports') ? 'show active' : ''}}" id="agentModule">
                <x-reports.agent :filterData="$filterData" />
            </div>
            @endif
            @if ($authUser->can('report.qc_reports')) 
            <div class="tab-pane fade {{!$authUser->can('report.agent_reports') && $authUser->can('report.qc_reports') ? 'show active' : ''}}" id="qcModule">
                <x-reports.qc_agent :reports="$reports" :filterManagerData="$filterManagerData" />
            </div>
            @endif

            @if ($authUser->can('report.manager_reports')) 
            <div class="tab-pane fade {{!$authUser->can('report.agent_reports') && !$authUser->can('report.qc_reports') && $authUser->can('report.manager_reports') ? 'show active' : ''}}" id="managerModule">
                <x-reports.manager :reports="$reports" :filterManagerData="$filterManagerData" />
            </div>
            @endif
        </div>
        @if (!$authUser->can('report.agent_reports') && !$authUser->can('report.manager_reports') && !$authUser->can('report.qc_reports')) 
             <div class="alert alert-warning alert-dismissible fade show text-center">
                <i class="fa fa-thumbs-down me-2"></i>No Data found.
            </div>
        @endif
    </div>
</div>
 @include('Reports.partial.report-details-modal')
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script>
    var agentReportTable = '';
    // var qcAgentReportTable = '';
    var clientOptions = {};
        var dateFilter = ''; 
        var sortBy = ''; 
        var search = ''; 
        var start_date = '';
        var end_date = '';
    $(document).ready(function() {

        $(document).on('click', '.toggle-btn', function () {
            const $this = $(this);
            const target = $this.data('target');

            // Activate the clicked button and deactivate others
            $this.addClass('active').siblings('.toggle-btn').removeClass('active');

            // Show the target tab and hide others
            $('.tab-pane').removeClass('active show');
            $('#'+target).addClass('active show');
        });

        // Filter event listeners
        $('#agentReportTable .user-filter, #agentReportTable .client-filter, #agentReportTable .activity-filter, #agentReportTable .status-filter, #agentReportTable .category-filter').on('change', function() {
            agentReportTable.ajax.reload();
        });
        
        let previousRequest = null;
        agentReportTable = $('#agentReportTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('reports.getReports') }}",
                "type": "GET",
                "data": function(d) {
                    d.date_filter = dateFilter; 
                    d.sort_by = sortBy;
                    d.user_filter = $('#agentReportTable .user-filter').val(); 
                    d.client_filter = $('#agentReportTable .client-filter').val(); 
                    d.activity_filter = $('#agentReportTable .activity-filter').val();
                    d.status_filter = $('#agentReportTable .status-filter').val(); 
                    d.category_filter = $('#agentReportTable .category-filter').val();
                    d.start_date = $('input.start_date').val();
                    d.end_date = $('input.end_date').val();

                },
                "beforeSend": function(jqXHR) {
                    if (previousRequest) {
                        previousRequest.abort();
                    }
                    previousRequest = jqXHR;
                }
            },
            "columns": [{
                        data: null,  // Instead of using 'formatted_id', use a null value here
                        render: function(data, type, row, meta) {
                            return meta.row + 1 + meta.settings._iDisplayStart; 
                        },
                        orderable: false
                    },
                { data: 'date', name: 'date' },
                @if($authUser->can('report.view'))
                { data: 'user_name', name: 'user.name' },
                @endif
                { data: 'client_name', name: 'client.client_name' },
                { data: 'activity', name: 'activity' },
                { data: 'category', name: 'category' },
                // { data: 'sub_category', name: 'sub_category' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        return `<span class="rounded-2   text-capitalize"> ${data}</span>`;
                    }
                },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                {
                    data: 'total_task_time',
                    name: 'total_task_time',
                    render: function(data, type, row) {
                        if (row.category === 'Attendance Activities') {
                            return '-';
                        }
                        return data;
                    }
                },
                {
                    "data": null, 
                    "render": function(data, type, row) {
                        // clientOptions[data.id] = data.clients_html;
                        var return_val = `
                            <span class="d-flex actions" >`;
                            return_val += `<i class="bi bi-info-circle text-warning cursor-pointer fs-5 report_info" data-type="`+data.type+`"  title="View Details" data-id="`+data.task_id+`"></i>`;
                           
                            return_val += `</span>`;
                        // if (data.process_id == null) {
                        //     return_val = '';
                        // }
                        return return_val;
                    }
                }
                ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": [],
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function(settings) {
                $('.page-numbers').remove();
                $('#agentReportTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });

        $(document).on('click', '.export_reports', function() {
            exportAllData();
        });

        // Function to fetch all data for export
        function exportAllData() {
            var report_type = 'agent';
            var data = {};
            if ($('#reportTable').is(':visible')) {
                report_type = 'manager';
                data = {
                    export: true,
                    report_type: report_type,
                    user_filter: $('#reportTable .user-filter').val(),
                    department_filter: $('#reportTable .department-filter').val(),
                    task_filter: $('#reportTable .task-filter').val(),
                    status_filter: $('#reportTable .status-filter').val(),
                    start_date: $('input.start_date').val(),
                    end_date: $('input.end_date').val(),
                }
            } else if ($('#qcAgentReportTable').is(':visible')) {
                report_type = 'qc_report';
                data = {
                    export: true,
                    report_type: report_type,
                    agent: $('#qcAgentReportTable .qc-agent-filter').val(),
                    user_filter: $('#qcAgentReportTable .qc-user-filter').val(),
                    client_filter: $('#qcAgentReportTable .qc-client-filter').val(),
                    activity_filter: $('#qcAgentReportTable .qc-activity-filter').val(),
                    status_filter: $('#qcAgentReportTable .qc-status-filter').val(),
                    start_date: $('input.start_date').val(),
                    end_date: $('input.end_date').val(),
                }
            } else {
                data = {
                    date_filter: dateFilter,
                    sort_by: sortBy,
                    user_filter: $('.user-filter').val(),
                    client_filter: $('.client-filter').val(),
                    activity_filter: $('.activity-filter').val(),
                    status_filter: $('.status-filter').val(),
                    category_filter: $('.category-filter').val(),
                    start_date: $('input.start_date').val(),
                    end_date: $('input.end_date').val(),
                    export: true,
                    report_type: report_type
                }
            }
            $.ajax({
                url: "{{ route('reports.getReports') }}",
                type: "GET",
                data: data,
                success: function(response) {
                    if (response.data) {
                        if (report_type == 'agent') {
                            downloadXLSX(response.data);
                        } else if (report_type == 'qc_report') {
                            downloadAgentXLSX(response.data);
                        } else {
                            ManagerDownloadXLSX(response.data);
                        }
                    }
                }
            });
        }

        function GetSheetName(type = null) {
            var user = "{{$authUser->name}}";
            var filters = [
                { value: $('.user-filter').val(), text: $('.user-filter option:selected').text() },
                { value: $('.client-filter').val(), text: $('.client-filter option:selected').text() },
                { value: $('.activity-filter').val(), text: $('.activity-filter option:selected').text() },
                { value: $('.status-filter').val(), text: $('.status-filter option:selected').text() },
                { value: $('.category-filter').val(), text: $('.category-filter option:selected').text() }
            ];
            if (type == 'qc_agent') {
                filters = [
                    { value: $('#qcAgentReportTable .qc-agent-filter').val(), text: $('#qcAgentReportTable .qc-agent-filter').text() },
                    { value: $('#qcAgentReportTable .qc-user-filter').val(), text: $('#qcAgentReportTable .qc-user-filter').text() },
                    { value: $('#qcAgentReportTable .qc-client-filter').val(), text: $('#qcAgentReportTable .qc-client-filter').text() },
                    { value: $('#qcAgentReportTable .qc-activity-filter').val(), text: $('#qcAgentReportTable .qc-activity-filter').text() },
                    { value: $('#qcAgentReportTable .qc-status-filter').val(), text: $('#qcAgentReportTable .qc-status-filter').text() },
                ];
            }
            var sheetName = user + '_';
            var selectedFilter = filters.find(filter => filter.value);
            sheetName = selectedFilter ? selectedFilter.text : sheetName +'all';
            var now = new Date();
            var time = now.toTimeString().split(' ')[0];
            var today = new Date().toISOString().split('T')[0];
            return sheetName + '_' + today + '_' + time + '.xlsx';
        }



        function downloadXLSX(data) {
            let xlsxData = [
                ["Sr No", "Date", "User Name", "Client Name", "Activity", "Category", "Status", "Start Time", "End Time", "Total Task Time"]
            ];

            data.forEach((row, index) => {
                xlsxData.push([
                    index + 1,
                    row.date,
                    row.user_name || '',
                    row.client_name || '',
                    row.activity || '',
                    row.category || '',
                    row.status || '',
                    row.start_time || '',
                    row.end_time || '',
                    row.category === 'Attendance Activities' ? '-' : row.total_task_time || ''
                ]);
            });

            const ws = XLSX.utils.aoa_to_sheet(xlsxData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data");
            const blob = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
            const blobFile = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            const fileName = GetSheetName();
            link.href = URL.createObjectURL(blobFile);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function downloadAgentXLSX(data) {
            let xlsxData = [
                ["Sr No", "Date", "Call ID", "Agent Name", "User Name", "Client Name", "Activity", "Status", "Call Duration"]
            ];

            data.forEach((row, index) => {
                xlsxData.push([
                    index + 1,
                    row.date,
                    row.call_id,
                    row.qc_agent || '',
                    row.user_name || '',
                    row.client || '',
                    row.activity || '',
                    row.status || '',
                    row.call_duration || ''
                ]);
            });

            const ws = XLSX.utils.aoa_to_sheet(xlsxData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data");
            const blob = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
            const blobFile = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            const fileName = GetSheetName('qc_agent');
            link.href = URL.createObjectURL(blobFile);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function ManagerDownloadXLSX(data) {
            let xlsxData = [
                ["Sr No", "Date", "User Name", "Department", "Task", "Status", "Start Time", "End Time", "Total Task Time"]
            ];

            data.forEach((row, index) => {
                xlsxData.push([
                    index + 1,
                    row.date,
                    row.user_name || '',
                    row.department || '',
                    row.task || '',
                    row.status || '',
                    row.start_time || '',
                    row.end_time || '',
                    row.duration || ''
                ]);
            });

            const ws = XLSX.utils.aoa_to_sheet(xlsxData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Data");
            const blob = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
            const blobFile = new Blob([blob], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            var now = new Date();
            var time = now.toTimeString().split(' ')[0];
            var today = new Date().toISOString().split('T')[0];
            const fileName = 'manager_tasks_' + today + '_' + time + '.xlsx';
            link.href = URL.createObjectURL(blobFile);
            link.setAttribute("download", fileName);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }



    });

    $(document).on('click', '.report_info', function() {
        $('#ReportModal').addClass('loading');
        var id = $(this).data('id');
         $.ajax({
                url: '{{ route("reports.getReportsDetails") }}',
                type: 'GET',
                data: { id:id , _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        $('#ReportModal').removeClass('loading');
                        $('.report-details-container').html(response.html);
                    }
                },
                error: function(xhr) {
                    toastr.error('Something went wrong. Please try again later.', 'Error');
                }
            });
        $('#ReportModal').modal('show');
    });
    initializeRangeDatePicker();
    function initializeRangeDatePicker() {
        console.log('inside datepicker');
        $('.rangedatepicker').not('.flatpickr-applied').each(function () {
            flatpickr(this, {
                mode: "range",
                dateFormat: "Y-m-d",
                allowInput: false,
                onReady: function(selectedDates, dateStr, instance) {
                    instance.input.classList.add('no-custom-arrow');
                    instance.input.setAttribute('placeholder', 'Select a date range');
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.classList.add('no-custom-arrow');
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        var startDate = new Date(selectedDates[0].getTime() + 86400000).toISOString().split('T')[0];
                        var endDate = new Date(selectedDates[1].getTime() + 86400000).toISOString().split('T')[0];
                        $('input.start_date').val(startDate);
                        $('input.end_date').val(endDate);

                        if (typeof agentReportTable !== 'undefined') agentReportTable.ajax.reload();
                        if (typeof reportTable !== 'undefined') reportTable.ajax.reload();
                    }
                }
            });

            // Mark as initialized
            $(this).addClass('flatpickr-applied');
        });
    }



    $(document).on('click', '.filter-btn', function() {
        $('#reportTable, #agentReportTable, #qcAgentReportTable').find('thead tr .filter').toggleClass('d-none');
        $('#reportTable, #agentReportTable, #qcAgentReportTable').find('thead tr th span').toggleClass('d-none');
    });

    var reportTable = '';
    // Manager Script
    function managerReportDatatable() {
        reportTable = $('#reportTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('reports.getReports') }}",
                "type": "GET",
                "data": function(d) {
                    d.report_type = 'manager';
                    d.user_filter = $('#reportTable .user-filter').val();
                    d.department_filter = $('#reportTable .department-filter').val();
                    d.task_filter = $('#reportTable .task-filter').val();
                    d.status_filter = $('#reportTable .status-filter').val();
                    d.start_date = $('input.start_date').val();
                    d.end_date = $('input.end_date').val();
                }
            },
            "columns": [
                { 
                    data: null,  
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart; 
                    },
                    orderable: false
                },
                { data: 'date', name: 'date' },
                { data: 'user_name', name: 'user_name' },
                { data: 'department', name: 'department' },
                { data: 'task', name: 'task' },
                { 
                    data: 'status',
                    render: function(data) {
                        return `<span class="rounded-2 status_badge text-capitalize">${data}</span>`;
                    }
                },
                { data: 'start_time', name: 'start_time' },
                { data: 'end_time', name: 'end_time' },
                { data: 'duration', name: 'duration' },
                {
                    data: null, 
                    render: function(data, type, row) {
                        return `
                            <span class="d-flex actions">
                                <i class="bi bi-info-circle text-warning cursor-pointer fs-5 manager_report_info" title="View Details" data-id="${row.id}"></i>
                            </span>`;
                    }
                }
            ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": [],
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function() {
                $('.page-numbers').remove();
                $('#reportTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });
    }


    // Manager Script
    $(document).on('change','#qcAgentReportTable .qc-agent-filter, #qcAgentReportTable .qc-user-filter, #qcAgentReportTable .qc-client-filter, #qcAgentReportTable .qc-activity-filter, #qcAgentReportTable .status-filter', function() {
        console.log('on change');
        qcAgentReportTable.ajax.reload();
    });
    function qcAgentReportDatatable() {
        qcAgentReportTable = $('#qcAgentReportTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('reports.getReports') }}",
                "type": "GET",
                "data": function(d) {
                    d.report_type = 'qc_report';
                    d.date_filter = dateFilter; 
                    d.sort_by = sortBy;
                    d.agent_filter = $('#qcAgentReportTable .qc-agent-filter').val();
                    d.user_filter = $('#qcAgentReportTable .qc-user-filter').val(); 
                    d.client_filter = $('#qcAgentReportTable .qc-client-filter').val(); 
                    d.activity_filter = $('#qcAgentReportTable .qc-activity-filter').val();
                    d.status_filter = $('#qcAgentReportTable .qc-status-filter').val(); 
                    d.start_date = $('input.start_date').val();
                    d.end_date = $('input.end_date').val();

                },
                dataSrc: function (json) {
                    // Extract unique values for each filter
                    const $agentFilter = $('.qc-agent-filter');
                    const $userFilter = $('.qc-user-filter');
                    const $clientFilter = $('.qc-client-filter');
                    const $activityFilter = $('.qc-ctivity-filter');

                    // Check if all filters are empty
                    const allFiltersEmpty =
                        !$agentFilter.val() &&
                        !$userFilter.val() &&
                        !$clientFilter.val() &&
                        !$activityFilter.val();

                    if (allFiltersEmpty) {
                        let agents = new Set();
                        let users = new Set();
                        let clients = new Set();
                        let activities = new Set();

                        json.data.forEach(item => {
                            agents.add(`${item.qc_agent_id}::${item.qc_agent}`);
                            users.add(`${item.user_id}::${item.user_name}`);
                            clients.add(`${item.client_id}::${item.client}`);
                            activities.add(`${item.activity_id}::${item.activity}`);
                        });

                        // Helper function to populate filter dropdowns
                        const populateFilter = (selector, dataSet) => {
                            const $select = $(selector);
                            $select.empty().append(`<option value="">Select</option>`);
                            [...dataSet].forEach(entry => {
                                const [id, name] = entry.split('::');
                                $select.append(`<option value="${id}">${name}</option>`);
                            });
                        };

                        populateFilter($agentFilter, agents);
                        populateFilter($userFilter, users);
                        populateFilter($clientFilter, clients);
                        populateFilter($activityFilter, activities);
                    }

                    return json.data;
                },
                "beforeSend": function(jqXHR) {
                    // if (previousRequest) {
                    //     previousRequest.abort();
                    // }
                    // previousRequest = jqXHR;
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
                { data: 'date', name: 'date' },
                { data: 'qc_agent', name: 'qc_agent' },
                { data: 'user_name', name: 'user_name' },
                { data: 'client', name: 'client' },
                { data: 'activity', name: 'activity' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        let statusText = '';
                        let badgeClass = '';

                        switch (data) {
                            case 'pending':
                                statusText = 'Pending';
                                badgeClass = 'bg-warning text-dark';
                                break;
                            case 'in_progress':
                                statusText = 'In Progress';
                                badgeClass = 'bg-primary';
                                break;
                            case 'completed':
                                statusText = 'Completed';
                                badgeClass = 'bg-success';
                                break;
                            default:
                                statusText = data;
                                badgeClass = 'bg-secondary';
                        }
                        return `<span class="badge p-3 ${badgeClass} text-capitalize"> ${statusText}</span>`;
                        return `<span class="rounded-2 status_badge badge ${badgeClass} text-capitalize">${statusText}</span>`;
                    }
                },
                { data: 'call_duration', name: 'call_duration' },
                {
                    "data": null, 
                    "render": function(data, type, row) {
                        // clientOptions[data.id] = data.clients_html;
                        var return_val = `
                            <span class="d-flex actions" >`;
                            return_val += `<i class="bi bi-info-circle text-warning cursor-pointer fs-5 qc_report_info" data-type=""  title="View Details" data-id="${data.id}"></i>`;
                           
                            return_val += `</span>`;
                        // if (data.process_id == null) {
                        //     return_val = '';
                        // }
                        return return_val;
                    }
                }
                ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": [],
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "drawCallback": function(settings) {
                $('.page-numbers').remove();
                $('#agentReportTable_paginate .pagination li:not(.previous):not(.next)').wrapAll('<span class="page-numbers"></span>');
            }
        });
    }

    $(document).on('click', '.qc_report_info', function(e) {
        e.preventDefault();
        $('#ReportModal').addClass('loading');
        $('#ReportModal').modal('show');
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("reports.getQCCallReportsDetails") }}',
            type: 'GET',
            data: { id: id, _token: '{{ csrf_token() }}', type:'manager' },
            success: function(response) {
                if (response.success) {
                    $('#ReportModal').removeClass('loading');
                    $('.report-details-container').html(response.html);
                } else {
                    $('#ReportModal').modal('hide');
                    toastr.error(response.message, 'Error');
                }
            },
            error: function(xhr) {
                $('#ReportModal').modal('hide');
                toastr.error('Something went wrong. Please try again later.', 'Error');
            }
        });

    });


    $('#reportTable .user-filter, #reportTable .department-filter, #reportTable .task-filter, #reportTable .status-filter').on('change', function() {
        reportTable.ajax.reload();
    });


    $(document).on('click', '.manager_report_info', function() {
        $('#ReportModal').modal('show');
        $('#ReportModal').addClass('loading');
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("reports.getReportsDetails") }}',
            type: 'GET',
            data: { id: id, _token: '{{ csrf_token() }}', type:'manager' },
            success: function(response) {
                if (response.success) {
                    $('#ReportModal').removeClass('loading');
                    $('.report-details-container').html(response.html);
                } else {
                    $('#ReportModal').modal('hide');
                    toastr.error(response.message, 'Error');
                }
            },
            error: function(xhr) {
                $('#ReportModal').modal('hide');
                toastr.error('Something went wrong. Please try again later.', 'Error');
            }
        });

    });

    if ($('#reportTable').is(':visible')) {
         managerReportDatatable();
    }
    if ($('#qcAgentReportTable').is(':visible')) {
        setTimeout(function() {
            qcAgentReportDatatable();
        }, 1000);
    }

    @if ($trueCount >= 2)
        // Get all toggle buttons and content divs
        $(document).on('click', '.toggle-btn', function() {
            $('input.start_date').val('');
            $('input.end_date').val('');
            // Remove active class from all buttons
            $(".toggle-btn").removeClass("active");

            // Hide all tab contents
            $(".tab-pane").removeClass("show active");

            // Activate clicked button and corresponding tab
            $(this).addClass("active");
            $("#" + $(this).data("target")).addClass("show active");
            if ($(this).data("target") == 'agentModule') {
                agentReportTable.draw();
                // $('.filter-btn, .export_reports').show();
            } else if($(this).data("target") == 'qcModule') {
                if ($.fn.DataTable.isDataTable('#qcAgentReportTable')) {
                    qcAgentReportTable.draw();
                } else {
                   qcAgentReportDatatable();
                }
            } else {
               if ($.fn.DataTable.isDataTable('#reportTable')) {
                    reportTable.draw();
                } else {
                   managerReportDatatable();
                }

                // $('.filter-btn, .export_reports').hide();
            }
        });
    @endif

</script>
@endpush