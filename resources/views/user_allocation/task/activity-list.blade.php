<div class="row">
    <div class="col-md-12">
        <table class="table w-100" id="assignActivityListTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Client Name</th>
                    <th>Workflow Name</th>
                    <th>Activity Name</th>
                </tr>
            </thead>

            <tbody>
                @foreach($assignActivities as $key => $assignActivity)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ date('d-M-Y', strtotime($assignActivity->created_at)) }}</td>
                        <td>{{ $assignActivity->activity->workflow->client->client_name }}</td>
                        <td>{{ $assignActivity->activity->workflow->workflow_name }}</td>
                        <td>{{ $assignActivity->activity->process_name }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    $('#assignActivityListTable').DataTable({
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