<table class="table" id="reportDetails">
    <thead>
        <tr>
            <th>#</th>
            <th>Activity</th>
            <th>Comments</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Total Time</th>
        </tr>
    </thead>
    <tbody>
        @foreach($timeEntries as $key => $timeEntry)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $timeEntry->type == 'break' ? 'Break' :  ucfirst($timeEntry->activity) }}</td>
                <td>{{ $timeEntry->comments ?? $timeEntry->break_option }}</td>
                <td>{{ date('h:i:s A', strtotime($timeEntry->start_time)) }}</td>
                <td>{{ $timeEntry->end_time != null ? date('h:i:s A', strtotime($timeEntry->end_time)) : '-' }}</td>
                <td>{{ $timeEntry->timesheet['total_task_time'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<script type="text/javascript">
    $('#reportDetails').DataTable({
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": false
        });
</script>