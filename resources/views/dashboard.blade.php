@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Chart Section -->
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

            @if($authUser->can('dashboard.view_all_users_data'))
            <div class="col-md-3">
                <select class="form-control user-filter select2">
                    <option value="">Select</option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}" >{{$user->name}}</option>
                    @endforeach
                </select>
            </div>
            @endif

        </div>
        <div class="row">
            @if($authUser->can('dashboard.show_idle_time'))
            <!-- Idle Time -->
            <div class="col-md-6">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="idleTimeChart"></canvas>
                </div>
            </div>
            @endif
            
            @if($authUser->can('dashboard.show_attendance'))
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

        <div class="row mt-4">
            @if($authUser->can('dashboard.show_productivity'))
            <!-- Productivity -->
            <div class="col-md-12">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
            @endif
        </div>

        <div class="row mt-4">
            @if($authUser->can('dashboard.show_top_status'))
            <!-- Top Status -->
            <div class="col-md-6">
                <div class="h-100 p-2 m-0 card rounded-2">
                    <canvas id="idleTimeCharts"></canvas>
                </div>
            </div>
            @endif
        </div>

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

        $(document).ready(function(){
            GetChartData();

            function GetChartData() {
                console.log('Fetching chart data...');
                var filter_data = {
                    start_date : $('.start_date').val(),
                    end_date : $('.end_date').val(),
                    user_filter : $('.user-filter').val()
                }
                var url = "{{ route('dashboard.getAllData') }}";
                $.ajax({
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

            $(document).on('change','.user-filter', function() {
                GetChartData();
            });

            function renderCharts(data) {
                renderAttendanceChart(data);
                renderIdleTimeChart(data);
                renderTopStatusChart(data);
                renderUsersProductivityChart(data);
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

            // Top 10 Status
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

        });
    </script>
@endpush
