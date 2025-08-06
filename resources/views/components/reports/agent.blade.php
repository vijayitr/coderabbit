<table class="table w-100" id="agentReportTable">
    <thead>
        <tr>
            <th>#</th>
            <th class="w-10">

            	 <div class="filter d-none">
                    <input type="input" class="rangedatepicker form-control date-filter">
                    <input type="hidden" class="start_date" name="">
                    <input type="hidden" class="end_date" name="">
                </div>
                <span>Date</span>
            </th>
            @if($authUser->can('report.view'))
            <th>
            	<div class="filter d-none">
                    <select class="form-control user-filter">
                  	  <option value="">Select</option>
	                    @foreach($filterData['users'] ?? [] as $user)
	                        <option value="{{$user['id']}}" >{{$user['name']}}</option>
	                    @endforeach
	                </select>
                </div>
                <span>Name</span>
            </th>
            @endif
            <th class="w-10">
            	<div class="filter d-none">
                  	<select class="form-control client-filter">
	                    <option value="">Select</option>
	                    @foreach($filterData['clients'] ?? [] as $client)
	                        <option value="{{$client['id']}}" >{{$client['client_name']}}</option>
	                    @endforeach
	                </select>
                </div>
                <span>Client</span>
              
            </th>
            <th class="w-10">
            	<div class="filter d-none">
                  	<select class="form-control activity-filter">
	                    <option value="">Select</option>
	                    @foreach($filterData['process'] ?? [] as $process)
	                        @if(!empty($process))
	                            <option value="{{$process}}" >{{$process}}</option>
	                        @endif
	                    @endforeach
	                </select>
                </div>
                <span>Activity</span>
            </th>
            <th>
            	<div class="filter d-none">
                  	<select class="form-control category-filter">
	                    <option value="">Select</option>
	                    @foreach($filterData['categorys'] ?? [] as $category)
	                        @if(!empty($category))
	                            <option value="{{$category}}" >{{ucfirst($category)}}</option>
	                        @endif
	                    @endforeach
	                </select>
                </div>
                <span>Category</span>
            </th>
            <!-- <th>Sub Category</th> -->
            <th>
            	<div class="filter d-none">
                  	<select class="form-control status-filter">
	                    <option value="">Select</option>
	                    @foreach($filterData['status'] ?? [] as $status)
	                        <option value="{{$status}}" >{{ucfirst($status)}}</option>
	                    @endforeach
	                </select>
                </div>
                <span>Status</span>
               
            </th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Total Time<br><div class="table_time_format">(HH:MM:SS)</div></th>
            <th class="action-box">Actions</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>