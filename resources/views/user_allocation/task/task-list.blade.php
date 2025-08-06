<div class="row">
    <div class="col-md-12 d-grid">
    <hr>
        <h4>Assigned Tasks</h4>
        @if(isset($data['columns']) && !empty($data['columns']))
            <div class="table-responsive">
                <table class="table" id="AssignTaskTable">
                    <thead>
                        <tr>
                            <th class="w-25 text-center">#</th>
                            @foreach($data['columns'] as $column)
                                <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                            <th width="30%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['records'] as $task_id => $record)
                            <tr data-taskid="{{$data['taskIds'][$task_id]}}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                @foreach($data['columns'] as $column)
                                    <td>{{ $record[$column] ?? '-' }}</td>
                                @endforeach
                                <td class="text-nowrap">
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-light btn-sm view_task me-2" data-id="{{$task_id}}" title="Assign Task">
                                            <i class="bi bi-info-circle text-warning fs-5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <div class="alert alert-warning text-center" role="alert">
                    <i class="fa fa-thumbs-down"></i>
                    No tasks assigned at the moment.
                </div>  
            @endif
    </div>
</div>