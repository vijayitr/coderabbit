@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Chart Section -->
        @if($authUser->can('dashboard.filters') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
        <div class="row mb-4">
            @if($authUser->can('dashboard.view_all_users_data') || $authUser->can('dashboard.show_idle_time') || $authUser->can('dashboard.show_attendance') || $authUser->can('dashboard.show_productivity') || $authUser->can('dashboard.show_top_status'))
            <div class="col-md-3">
                <input type="input" class="rangedatepicker form-control">
                <input type="hidden" class="start_date" name="">
                <input type="hidden" class="end_date" name="">
            </div>

            @else 
                <div class="alert alert-warning alert-dismissible fade show">
                    <strong>Warning!</strong> No Data found.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($authUser->can('dashboard.view_all_users_data') || $authUser->can('dashboard.view_team_dashboard'))
            <div class="col-md-3">
                <select class="form-control user-filter select2" data-placeholder="Select User">
                    <option value="">Select User</option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}" >{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-md-3">
                <select class="form-control client-filter select2" data-placeholder="Select Client">
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{$client->id}}" class="d-none" data-user="{{$client->assigned_to}}">{{$client->client_name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select class="form-control activity-filter select2" data-placeholder="Select Workflow">
                    <option value="">Select Workflow</option>
                    @foreach($activities as $activity)
                        <option value="{{$activity->id}}" data-client="{{$activity->workflow->client_id}}">{{$activity->process_name}}</option>
                    @endforeach
                </select>
            </div>

        </div>
        @endif

        <div class="row">
        @if(!$authUser->can('time_entry.manager_tasks') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
            @if($authUser->can('dashboard.view_all_users_data') || $authUser->can('dashboard.view_team_dashboard') || in_array(1, $userRoles))
            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 user_counts">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">User Management</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Users</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-tertiary text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/manager-management.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 client_counts">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">Clients</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Clients</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-primary text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/manager-management.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 workflow_counts">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">Workflows</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Workflows</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-info text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/manager-management.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 time_entry_count">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">Time Entry</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-info text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/time-entry.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 completed_task_count">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">Completed Tasks</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-success text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/time-entry.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card rounded-2 ms-0 pending_task_count">
                    <div class="card-body">
                        <div class="row">
                            <div class="col">
                                <span class="h6 font-semibold text-muted text-sm d-block mb-2">Pending Tasks</span>
                                <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                            </div>
                            <div class="col-auto">
                                <div class="icon icon-shape bg-warning text-white text-lg rounded-circle dashboard_card_icons">
                                    <img src="/images/sidebar-icons/time-entry.png" alt="manager-management">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if($authUser->can('report.manager_reports') || in_array(1, $userRoles))
                @if(in_array(1, $userRoles))
                    <div class="col-md-3 mb-4">
                        <div class="card rounded-2 ms-0 manager_count">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <span class="h6 font-semibold text-muted text-sm d-block mb-2">Managers Count</span>
                                        <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-tertiary text-white text-lg rounded-circle dashboard_card_icons">
                                            <img src="/images/sidebar-icons/manager-management.png" alt="manager-counts">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-3 mb-4">
                    <div class="card rounded-2 ms-0 manager_completed_task_count">
                        <div class="card-body">
                            <div class="row">
                                <div class="col pe-0">
                                    <span class="h6 font-semibold text-muted text-sm d-block mb-2">{{in_array(1, $userRoles) ? 'Managers' : ''}} Completed Tasks</span>
                                    <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-success text-white text-lg rounded-circle dashboard_card_icons">
                                        <img src="/images/sidebar-icons/time-entry.png" alt="manager-management">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card rounded-2 ms-0 manager_pending_task_count">
                        <div class="card-body">
                            <div class="row">
                                <div class="col pe-0">
                                    <span class="h6 font-semibold text-muted text-sm d-block mb-2">{{in_array(1, $userRoles) ? 'Manager' : ''}} Pending Tasks</span>
                                    <span class="h3 font-bold mb-0"><span class="count">0</span> Tasks</span>
                                </div>
                                <div class="col-auto">
                                    <div class="icon icon-shape bg-warning text-white text-lg rounded-circle dashboard_card_icons">
                                        <img src="/images/sidebar-icons/time-entry.png" alt="manager-management">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="row mb-4">
            @if($authUser->can('dashboard.show_idle_time') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
            <!-- Idle Time -->
            <div class="col-md-6">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="idleTimeChart"></canvas>
                </div>
            </div>
            @endif
            
            @if($authUser->can('dashboard.show_attendance') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
            <!-- Attandence -->
            <div class="col-md-6">
                <div class="h-100 p-25 m-0 card rounded-2">
                    <div class="w-50 m-auto">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="row mb-4">
            @if($authUser->can('report.manager_reports'))
            <!-- Productivity -->
            <div class="col-md-12">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="managerProductivity"></canvas>
                </div>
            </div>
            @endif
        </div>

        <div class="row mb-4">
            @if($authUser->can('dashboard.show_productivity') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
            <!-- Productivity -->
            <div class="col-md-12">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <div class="row mb-4">
            @if($authUser->can('dashboard.show_top_status') && !$authUser->can('report.manager_reports') || in_array(1, $userRoles))
            <!-- Top Status -->
            <div class="col-md-6">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="idleTimeCharts"></canvas>
                </div>
            </div>
            @endif
        </div>
        @if ($authUser->can('dashboard.task_list'))
        <div class="row mb-4">
            <div class="col-md-12 d-grid">
                <hr/>
                <h4>Task List</h4>
                <div class="position-relative task-loader">
                    <div class="dataTables_processing card" role="status">
                        <div>
                            <div></div>
                            <div></div>
                            <div></div>
                            <div></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table d-none" id="taskTable">
                        <thead></thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="dataTables_wrapper my-2">
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" role="status" aria-live="polite"></div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate paging_simple_numbers pt-0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
@endsection

@push('scripts')
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>


    <script>
        var attendanceChartInstance = null;
        var idleTimeChartInstance = null;
        var topStatusChartInstance = null;
        var usersProductivityChartInstance = null;
        var managersProductivityChartInstance = null;
        var page = 1;
        let table;

        $(document).ready(function(){
            GetChartData();
            var clientHtml = $(".client-filter").html();
            var activityHtml = $(".activity-filter").html();
            var previousRequest;
            function GetChartData() {
                if (previousRequest && previousRequest.readyState !== 4) {
                    previousRequest.abort();
                }
                var filter_data = {
                    start_date : $('.start_date').val(),
                    end_date : $('.end_date').val(),
                    user_filter : $('.user-filter').val(),
                    client_filter : $('.client-filter').val(),
                    activity_filter : $('.activity-filter').val()
                }
                var url = "{{ route('dashboard.getAllData') }}";
                previousRequest = $.ajax({
                    url: url,
                    type: 'GET',
                    data:filter_data,
                    success: function(data) {
                        renderCharts(data);
                    },
                    error: function(xhr, status, error) {
                        toastr.error(xhr.responseJSON.message, 'Error');
                    }
                });
            }

            $(document).on('change','.user-filter, .client-filter, .activity-filter', function() {
                GetChartData();
            });

            function renderCharts(data) {
                countCards(data);
                renderAttendanceChart(data);
                renderIdleTimeChart(data);
                renderTopStatusChart(data);
                renderUsersProductivityChart(data);
                renderManagersProductivityChart(data)
            }

            function countCards(data) {
                animateValue(".user_counts span.count", data.usersCount, 500);
                animateValue(".client_counts span.count", data.clientsCount, 500);
                animateValue(".workflow_counts span.count", data.workflowsCount, 500);
                animateValue(".time_entry_count span.count", data.taskCount, 500);
                animateValue(".completed_task_count span.count", data.completedTasks, 500);
                animateValue(".pending_task_count span.count", data.pendingTasks, 500);
                animateValue(".manager_completed_task_count span.count", data.managerCompletedTasks, 500);
                animateValue(".manager_pending_task_count span.count", data.managerPendingTasks, 500);
                animateValue(".manager_count span.count", data.managersCount, 500);
            }

            // Pie Chart - Attendance
            function renderAttendanceChart(data) {
                @if($authUser->can('dashboard.show_attendance'))
                    if(data.attendanceData == undefined) return;
                    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');

                    // Destroy existing chart instance if it exists
                    if (attendanceChartInstance) {
                        attendanceChartInstance.destroy();
                    }

                    // Create a new chart instance
                    attendanceChartInstance = new Chart(attendanceCtx, {
                        type: 'pie',
                        data: {
                            labels: ['LT', 'No Record', 'On Time'],
                            datasets: [{
                                data: [data.attendanceData.late, data.attendanceData.noRecord, data.attendanceData.onTime],
                                backgroundColor: ['#ff6384', '#36a2eb', '#4caf50']
                            }]
                        },
                        options: {
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Attendance %',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    },
                                    padding: {
                                        top: 10,
                                        bottom: 20
                                    }
                                },
                                legend: {
                                    position: 'right',
                                    labels: {
                                        boxWidth: 20,
                                        font: {
                                            size: 14
                                        },
                                        padding: 15
                                    }
                                }
                            }
                        }
                    });
                @endif
            }

            // Bar Chart - IdleTime
            function renderIdleTimeChart(data) {
                @if($authUser->can('dashboard.show_idle_time'))
                var IdleTime = data.IdleTime;
                if(IdleTime == undefined) return;
                if (idleTimeChartInstance) {
                    idleTimeChartInstance.destroy();
                }

                var idle_labels = [];
                var idle_data = [];
                IdleTime.labels.forEach((value, index) => {
                    idle_data.push(value);
                    idle_labels.push(IdleTime.data[index]);
                });

                const topActivitiesCtx = document.getElementById('idleTimeChart').getContext('2d');
                idleTimeChartInstance = new Chart(topActivitiesCtx, {
                    type: 'bar',
                    data: {
                        labels: idle_labels,
                        datasets: [{
                            label: 'IT Idle Time (minutes)',
                            data: idle_data,
                            backgroundColor: '#36a2eb'
                        }]
                    }
                });
                @endif
            }

            $(".user-filter").on("change", function () {
                let selectedValue = $(this).val();
                $(".client-filter").html(clientHtml);
                if (selectedValue != '') {
                    $(".client-filter option").each(function () {
                        if ($(this).data('user') != selectedValue && $(this).val() != '') {
                            $(this).remove();
                        }
                    });
                }
            });

            $(".client-filter").on("change", function () {
                let selectedValue = $(this).val();
                $(".activity-filter").html(activityHtml);
                if (selectedValue != '') {
                    $(".activity-filter option").each(function () {
                        if ($(this).data('client') != selectedValue && $(this).val() != '') {
                            $(this).remove();
                        }
                    });
                }
            });

            // Top 10 Status
            function renderTopStatusChart(data) {
                @if($authUser->can('dashboard.show_top_status'))
                    var top_stauts = data.top_stauts;
                    if(top_stauts == undefined) return;
                    const idleTimeCtx = document.getElementById('idleTimeCharts').getContext('2d');
                    if (topStatusChartInstance) {
                        topStatusChartInstance.destroy();
                    }

                    var status_labels = [];
                    var status_data = [];
                    Object.entries(top_stauts).forEach(([key, value]) => {
                        status_labels.push(key);
                        status_data.push(value);
                    });
                    topStatusChartInstance = new Chart(idleTimeCtx, {
                        type: 'bar',
                        data: {
                            labels: status_labels,
                            datasets: [{
                                label: 'Top 10 Status',
                                data: status_data,
                                backgroundColor: '#4caf50'
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Top 10 Status',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    },
                                    padding: {
                                        top: 10,
                                        bottom: 20
                                    }
                                },
                                legend: {
                                    display: false,
                                }
                            }
                        }
                    });
                @endif
            }

            // User Productivity Chart
            function renderUsersProductivityChart(data) {
                @if($authUser->can('dashboard.show_productivity'))
                    var usersProductivity = data.usersProductivity;
                    if(usersProductivity == undefined) return;
                    const ctx = document.getElementById('myChart').getContext('2d');
                    if (usersProductivityChartInstance) {
                        usersProductivityChartInstance.destroy();
                    }

                    // Data for the chart
                    const labels = [];
                    const productionPercentages = [];
                    const totalTimeInHours = [];
                    const active_time_percentage = [];
                    const time = [];
                    Object.values(usersProductivity).forEach(user => {
                        labels.push(user.user_name);
                        productionPercentages.push(user.active_work_time);
                        totalTimeInHours.push(user.total_task_time);
                        active_time_percentage.push(user.active_time_percentage);
                        time.push(user.time);
                    });

                    usersProductivityChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Sum of Production%',
                                    data: productionPercentages,
                                    backgroundColor: '#4A90E2',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    yAxisID: 'y1',
                                    active_time_percentage: active_time_percentage // Ensure this is in the dataset
                                },
                                {
                                    label: 'Sum of Total Time (Hours)',
                                    data: totalTimeInHours,
                                    backgroundColor: '#FF6F61',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    yAxisID: 'y',
                                    time:time
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            if (context.dataset.label === 'Sum of Total Time (Hours)') {
                                                return `${context.dataset.label}: ${context.dataset.time[context.dataIndex]} hrs`;
                                            }
                                            // Show active_time_percentage in the tooltip for "Sum of Production%"
                                            return `Sum of Production : ${context.dataset.active_time_percentage[context.dataIndex]}%`;
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',  // Place label inside bar
                                    align: 'start', // Align to the right of the bar
                                    formatter: (value, context) => {
                                        if (context.dataset.label === 'Sum of Total Time (Hours)') {
                                            return `${context.dataset.time[context.dataIndex]} hrs`;
                                        } else {
                                            // Display active_time_percentage for "Sum of Production%"
                                            return `${context.dataset.active_time_percentage[context.dataIndex]}%`;
                                        }
                                    },
                                    color: 'white', // Customize text color to make it visible inside the bar
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    title: {
                                        display: true,
                                        text: '',
                                    },
                                    beginAtZero: true
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: {
                                        display: false,
                                        text: 'Total Time (Hours)',
                                    },
                                    position: 'left',
                                },
                                y1: {
                                    stacked: false,
                                    beginAtZero: true,
                                    title: {
                                        display: false,
                                        text: 'Production Percentage (%)',
                                    },
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false,
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });

                @endif
            }

            // User Productivity Chart
            function renderManagersProductivityChart(data) {
                @if($authUser->can('report.manager_reports'))
                    var managersProductivity = data.managersProductivity;
                    if(managersProductivity == undefined) return;
                    const ctx = document.getElementById('managerProductivity').getContext('2d');
                    if (managersProductivityChartInstance) {
                        managersProductivityChartInstance.destroy();
                    }

                    // Data for the chart
                    const labels = [];
                    const productionPercentages = [];
                    const totalTimeInHours = [];
                    const active_time_percentage = [];
                    const completed_tasks = [];
                    const time = [];
                    Object.values(managersProductivity).forEach(user => {
                        labels.push(user.user_name);
                        productionPercentages.push(user.completionRate);
                        totalTimeInHours.push(100);
                        completed_tasks.push(user.completed_tasks);
                        active_time_percentage.push(user.completionRate);
                        time.push(user.total_tasks);
                    });

                    managersProductivityChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Sum of Completed Tasks (%)',
                                    data: productionPercentages,
                                    backgroundColor: '#4A90E2',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    yAxisID: 'y1',
                                    active_time_percentage: active_time_percentage,
                                    completed_tasks:completed_tasks
                                },
                                {
                                    label: 'Sum of Total Tasks',
                                    data: totalTimeInHours,
                                    backgroundColor: '#FF6F61',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    yAxisID: 'y',
                                    time:time,
                                    completed_tasks:completed_tasks
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                @if(in_array(1, $userRoles))
                                title: {
                                    display: true,
                                    text: 'Manager Productivity',
                                    font: {
                                        size: 18,
                                        weight: 'bold'
                                    },
                                    padding: {
                                        top: 10,
                                        bottom: 20
                                    }
                                },
                                @endif
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            if (context.dataset.label === 'Sum of Total Tasks') {
                                                return `${context.dataset.label}: ${context.dataset.time[context.dataIndex]} Tasks ( 100% )`;
                                            }
                                            // Show active_time_percentage in the tooltip for "Sum of Production%"
                                            return `Sum of Completed Tasks : ${context.dataset.completed_tasks[context.dataIndex]} Tasks ( ${context.dataset.active_time_percentage[context.dataIndex]}% )`;
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',  // Place label inside bar
                                    align: 'start', // Align to the right of the bar
                                    formatter: (value, context) => {
                                        if (context.dataset.label === 'Sum of Total Tasks') {
                                            return `${context.dataset.time[context.dataIndex]} Tasks`;
                                        } else {
                                            // Display active_time_percentage for "Sum of Production%"
                                            return `${context.dataset.active_time_percentage[context.dataIndex]}%`;
                                        }
                                    },
                                    color: 'white', // Customize text color to make it visible inside the bar
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    title: {
                                        display: true,
                                        text: '',
                                    },
                                    beginAtZero: true
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: {
                                        display: false,
                                        text: 'Total Time (Hours)',
                                    },
                                    position: 'left',
                                },
                                y1: {
                                    stacked: false,
                                    beginAtZero: true,
                                    title: {
                                        display: false,
                                        text: 'Production Percentage (%)',
                                    },
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false,
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });

                @endif
            }

            flatpickr(".rangedatepicker", {
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
                    if (selectedDates.length === 2) { // Ensure both dates are selected
                        var startDate = new Date(selectedDates[0].getTime() + 86400000).toISOString().split('T')[0];
                        var endDate = new Date(selectedDates[1].getTime() + 86400000).toISOString().split('T')[0];
                        $('input.start_date').val(startDate);
                        $('input.end_date').val(endDate);
                        GetChartData();
                    }
                }
            });
            @if ($authUser->can('dashboard.task_list'))
            getTaskList(page);
            function getTaskList(page) {
                $('.task-loader').removeClass('d-none');
                $.ajax({
                    url: "{{ route('dashboard.getTasks') }}",
                    type: 'get',
                    data:{ page: page },   
                    success: function(response) {
                        if (response.status) {
                            if ($.fn.DataTable.isDataTable("#taskTable")) {
                                table.destroy();
                            }
                            let columns = Object.values(response.data.columns);
                            let records = response.data.records;
                            let dataTables_info = response.data.pagination_info;
                            let dataTables_paginate = response.data.pagination;
                            let filter_records = response.data.filter_records;
                            $('.dataTables_info').html(dataTables_info);
                            $('.dataTables_paginate').html(dataTables_paginate);

                            // Append column headers
                            let tableHead = $("#taskTable thead");
                            tableHead.html('');
                            let tableHeadRow = "<tr>";
                            tableHeadRow += `<th>#</th>`;
                            if (columns.length) {
                                tableHeadRow += `<th>Action</th>`;
                            }

                            columns.forEach(col => {
                                 tableHeadRow += `<th>${col.replace(/_/g, ' ').toUpperCase()}</th>`;
                            });
                            tableHead.append(tableHeadRow);
                            // Append table rows
                            let tableBody = $("#taskTable tbody");
                            tableBody.html('');
                            $.each(filter_records, function(index, record) {
                                let isChecked = false;
                                let checkedAttr = false;
                                let buttonText = isChecked ? 'Reassign' : 'Assign'
                                let sno = ((page - 1) * 10) + (index +1);
                                let row = "<tr>";
                                row += `<td>${sno}</td>`;
                                row += '<td><button class="btn btn-info text-white text-lg cursor-pointer goto_time_entry" title="TimeEntry" data-taskid="'+record+'"><img src="/images/sidebar-icons/time-entry.png" alt="manager-management"></button></td>';
                                columns.forEach(col => {
                                    row += `<td>${records[record][col] ? records[record][col] : '-'}</td>`;
                                });

                                row += "</tr>";
                                tableBody.append(row);
                            });

                            addDataTable();
                        }
                    },
                    error: function(xhr, status, error) {
                        // toastr.error(xhr.responseJSON.message, 'Error');
                    }
                });
            }

            function addDataTable() {
                $('#taskTable').removeClass('d-none');
                $('.task-loader').addClass('d-none');
                if ($.fn.DataTable.isDataTable("#taskTable")) {
                    table.destroy();
                }
                table = $('#taskTable').DataTable({
                    "processing": true,
                    "serverSide": false,
                    "searching": true,
                    "ordering": false,
                    "paging": false,
                    "info":false
                });
            }

            $(document).on('click', 'ul.pagination a', function(e) {
                e.preventDefault();
                page = $(this).attr('data-page');
                getTaskList(page)
            });

            $(document).on('click', '.goto_time_entry', function() {
                var id = $(this).attr('data-taskid');
                localStorage.setItem('selected_task_id', id);
                window.location.href = '/time-entry';
            });
            @endif

        });


        function animateValue(targetClass, value, duration = 2000) {
            let start = 0;
            let startTime = null;
            const element = document.querySelector(targetClass);
            if (element != null) {
                function updateCount(timestamp) {
                    if (!startTime) startTime = timestamp;
                    let progress = Math.min((timestamp - startTime) / duration, 1);
                    element.innerText = Math.floor(progress * value);

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
                        element.innerText = value;
                    }
                }

                requestAnimationFrame(updateCount);
            }

        }
    </script>
@endpush
