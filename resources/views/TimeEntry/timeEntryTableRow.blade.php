@foreach($AssignActivities as $key => $AssignActivity)
	<tr class="taskTr {{$AssignActivity->break ? 'paused' : ''}}" data-processid="{{$AssignActivity->activity->id}}" data-assignment_id="{{$AssignActivity->id}}">
	 	<td class="align-middle py-3" scope="col">
	    	<input type="checkbox" class="form-check-input mt-0 me-3">
	    	<span class="patient_name">{{$AssignActivity->primary_value}}</span>
	   </td>
	   <td class="align-middle py-3 client" scope="col">{{$AssignActivity->activity->workflow->client->client_name}}</td>


	    <td class="align-middle py-3 activity" scope="col">{{$AssignActivity->activity->process_name}}</td>
	    <td class="align-middle py-3 status" scope="col">
		    <span class="status_badge">{{ucwords(str_replace('_', ' ', $AssignActivity->status))}}</span>
		</td>

		<td class="align-middle py-3 action text-end" scope="col">
		  	@if ($authUser->can('time_entry.edit') || $authUser->can('time_entry.view'))
			    <button type="button" class="{{$AssignActivity->status != 'completed' ? '' : ' '}} edit_task control" data-task-details="{{base64_encode(json_encode(['process_id' => $AssignActivity->activity->id, 'client_id' => $AssignActivity->activity->workflow->client->id, 'process_status' => $AssignActivity->status]))}}" data-process-id="{{$AssignActivity->activity->id}}" data-client-id="{{$AssignActivity->activity->workflow->client->id}}" data="{{ $AssignActivity->fieldsHtmlBase64 }}">
			      <img src="/images/track-time/{{$AssignActivity->status != 'completed' ? 'pencil.png' : 'view.png '}}" alt="Edit">
			    </button>
		    @endif
		  	@if($AssignActivity->status != 'completed')
			    @if(!$AssignActivity->TimeEntry->where('type', 'break')->whereNull('end_time')->count())
			    <button type="button" class="pause_task control" data-id="{{$AssignActivity->activity->id}}" {{$AssignActivity->status == 'completed' ? 'disabled' : ''}}>
			      <img src="/images/track-time/pause.png" alt="Pause">
			    </button>
			    @else
			    <button type="button" class="play_task control" >
			      <img src="/images/track-time/play.png" alt="Pause">
			    </button>
			    @endif
			    <button type="button" class="btn btn-outline-primary px-3 py-1 Workflow_end_btn btn-outlined" {{$AssignActivity->status == 'completed' ? 'disabled' : ''}}>End Task</button>
		    @endif
		  </td>
	</tr>
@endforeach