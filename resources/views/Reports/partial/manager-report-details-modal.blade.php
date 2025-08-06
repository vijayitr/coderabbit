<div class="container">
    <table class="table table-bordered">
        <tr>
            <th>Date</th>
            <td>{{ $report->created_at->format('d-M-Y') }}</td>
        </tr>
        <tr>
            <th>User</th>
            <td>{{ $report->user->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $report->department->field_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Task</th>
            <td>{{ $report->task->option_value ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $report->details ?? 'No Details' }}</td>
        </tr>
        @if(isset($extraData['clients']) && !empty($extraData['clients']))
        <tr>
            <th>Clients</th>
            <td>
                @foreach($extraData['clients'] as $client)
                <span class="common-badge me-2">{{$client->client_name}}</span>
                @endforeach
            </td>
        </tr>
        @endif

        @if(isset($extraData['user']) && !empty($extraData['user']))
        <tr>
            <th>User</th>
            <td>
                {{$extraData['user']->name}} ( {{$extraData['user']->email}} )
            </td>
        </tr>
        @endif

        @if(isset($extraData['full_time_employee']) && !empty($extraData['full_time_employee']))
        <tr>
            <th>Full Time Employee</th>
            <td>
                {{$extraData['full_time_employee']}} Employees 
            </td>
        </tr>
        @endif

        @if(isset($extraData['todays_team']) && !empty($extraData['todays_team']))
        <tr>
            <th>Today`s Team</th>
            <td>
                {{$extraData['todays_team']}} Employees
            </td>
        </tr>
        @endif
    </table>
</div>
