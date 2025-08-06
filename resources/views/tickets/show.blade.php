<div class="row m-0">
    <div class="col-md-12">
        <table class="table w-100" id="userTaskDetailsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Value</th>
                </tr>
            </thead>

            <tbody>
            	@if(is_array($response))
	                @foreach($response as $key => $value)
	                    <tr>
	                    	<td>{{ $loop->iteration }}</td>
	                        <td>{{ str_replace('_', ' ', html_entity_decode($value['field'])) }}</td>
	                        <td>{{ $value['value'] }}</td>
	                    </tr>
	                @endforeach
                @endif
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