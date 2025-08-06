@php
    use Carbon\Carbon;
@endphp
<table class="table responsive" id="reportTable">
    <thead>
        <tr>
            <th>#</th>
            <th>
                <div class="filter d-none">
                    <input type="input" class="rangedatepicker form-control date-filter">
                    <input type="hidden" class="start_date" name="">
                    <input type="hidden" class="end_date" name="">
                </div>
                <span>Date</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control user-filter">
                        <option value="">Select</option>
                        @foreach($filterManagerData['users'] ?? [] as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>
                <span>User</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control department-filter">
                        <option value="">Select</option>
                        @foreach($filterManagerData['departments'] ?? [] as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>
                <span>Department</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control task-filter">
                        <option value="">Select</option>
                        @foreach($filterManagerData['tasks'] ?? [] as $key => $value)
                            <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                </div>
                <span>Task</span>
            </th>
            <th>
                <div class="filter d-none">
                    <select class="form-control status-filter">
                        <option value="">Select</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <span>Status</span>
            </th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Total Time
                <br>
            <div class="table_time_format">(HH:MM:SS)</div></th>
            <th class="action-box">Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>