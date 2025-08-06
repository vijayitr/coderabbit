@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <!-- Table -->
        <table class="table" id="activityLogsTable">
            <!-- Add Status Column in Table Header -->
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>User</th>
                    <th>Activity</th>
                    <th>Model</th>
                    <th class="action-box">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<x-json-viewer />
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        var activityData = [];
        let activityLogsTable = $('#activityLogsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "{{ route('activity.logs.data') }}",
                "type": "GET"
            },
            "columns": [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1 + meta.settings._iDisplayStart; 
                    },
                    orderable: false
                },
                { 
                    data: 'date',
                    orderable: true
                },
                { 
                    data: 'user',
                    orderable: true
                },
                { 
                    data: 'activity',
                    orderable: true
                },
                { 
                    data: 'model',
                    orderable: true
                },
                {
                    data: null,
                    render: function(data) {
                        return `<button class="btn btn-sm btn-info view_history" data-id="${data.id}">
                                    <i class="fa fa-eye"></i> View
                                </button>`;
                    },
                    orderable: false
                }
            ],
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "order": [[1, 'desc']],
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": true,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            }
        });

        $(document).ajaxComplete(function(event, jqXHR, ajaxOptions) {
            console.log('inside ajax');
           var url = ajaxOptions.url;
           var data = jqXHR.responseJSON.data;

           if (url.includes("activity-logs/data")) {
                activityData = data;
            }
        });

        // Handle View Details Click
        $(document).on('click', '.view_history', function() {
            let logId = $(this).data('id');
            const index = activityData.findIndex(item => item.id === logId);
            var el = document.querySelector('.json_viewer');
            el.innerHTML = jsonViewer(activityData[index].changes, true);
            console.log(activityData[index].changes);
            $('#jsonViewerModalLabel').modal('show');
        });
    });
</script>
@endpush
