<div class="row">
    <div class="col-md-12">
        <table class="table w-100" id="userTaskDetailsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Field Name</th>
                    <th>Value</th>
                </tr>
            </thead>

            <tbody>
                @foreach($taskValues as $key => $value)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $value->workflowField->field_name }} @if($value->workflowField->is_primary)<i class="bi bi-check2-circle text-success"></i> @endif</td>
                        <td>{{ $value->value }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    $('#userTaskDetailsTable').DataTable({
        "searching": true,
        "paging": false,
        "lengthChange": false,
        "order": [[0, 'asc']],
        "ordering": false,
        "dom": 'Bfrtip',
        "buttons": ['csv', 'excel'],
        "info": false
    });
</script>