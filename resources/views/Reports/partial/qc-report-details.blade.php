<table class="table table-bordered" id="call_details">
    <thead>
        <tr>
            <th>Title</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
            <tr>
                <td>User Name</td>
                <td>{{ $user_name }}</td>
            </tr>

            <tr>
                <td>Agent Name</td>
                <td>{{ $agent_name }}</td>
            </tr>

            <tr>
                <td>Client Name</td>
                <td>{{ $client_name }}</td>
            </tr>

            <tr>
                <td>Workflow Name</td>
                <td>{{ $workflow_name }}</td>
            </tr>

            <tr>
                <td>Process Name</td>
                <td>{{ $process_name }}</td>
            </tr>
    </tbody>
</table>

<div class="row">
    <div class="col-md-12">
        <div class="mb-2 mb-2">
            <div class="row mt-2 recording-container score_recording">
                <h5>
                    <strong>Call Recording:</strong>
                    <button class="ms-2 btn btn-sm btn-primary load-recording-btn">
                      <i class="bi bi-cloud-download"></i> Load Recording
                    </button>
                    <i class="ms-2 fas fa-spinner fa-spin d-none call_recording_loading_icon"></i>
                </h5>
                <audio controls="" data-recording-id="{{$recording_id}}" class="d-none">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
    </div>
</div>

@php
    $scores = isset($score->parameter_scores) ? $score->parameter_scores : [];
    $score_values = ['Fatal','Average','Good','Excellent'];
@endphp

<div class="mt-4">
    <h3>Qc Scores</h3>
    <table class="table table-bordered" id="score_details">
        <thead>
            <tr>
                <th>Score Parameter</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            @foreach($QcParameter as $param)
            @php
                $score_value = isset($scores[$param->id]) ? $scores[$param->id] : 'N/A';
            @endphp
                <tr>
                    <td>{{$param->name}}</td>
                    <td>{{ isset($score_values[$score_value]) ? $score_values[$score_value] : $score_value }}</td>
                </tr>
            @endforeach
            <tr>
                <td>Extra Details</td>
                <td>{{ isset($score->details) ? $score->details : '-' }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="fw-bold">Call Quality Score</td>
                <td class="fw-bold">{{ isset($score->score) ? $score->score : 0 }} / 100</td>
            </tr>
        </tfoot>
</div>
</table>

<!-- <script type="text/javascript">
    $('#call_details, #score_details').DataTable({
            "searching": true,
            "paging": false,
            "lengthChange": false,
            "order": [[0, 'asc']],
            "ordering": false,
            "dom": 'Bfrtip',
            "buttons": ['csv', 'excel'],
            "info": false
        });
</script> -->