@php
    use Illuminate\Support\Str;
@endphp
@foreach($AssignActivity->activity->workflowFieldValues->sortBy(fn($val) => $val->workflowField->order) as $workflowFieldValue)
	@if($workflowFieldValue->assignment_id == $AssignActivity->id)
	@php
        $text = $workflowFieldValue->workflowField->field_name;
        $text = str_replace([' ', '.'], '_', $text);
        $text = preg_replace('/[^A-Za-z0-9_]/', '', $text);
        $text = Str::lower($text);
    @endphp
		<div class="col-md-3 w-field">
		    <label class="form-label {{ $workflowFieldValue->workflowField->field_type == 'checkbox' ? 'opacity-0' : '' }}" data-snake="{{ucfirst(str_replace('_', ' ', trim($text)))}}">
		        {{ html_entity_decode($workflowFieldValue->workflowField->field_name) }}
		        @if($workflowFieldValue->workflowField->is_primary)
		        	<i class="bi bi-check2-circle text-success"></i>
		        @endif
		        <img class="ms-1 copy_field_content cursor-pointer d-none float-end" src="/images/copy.svg" alt="copy content" title="Copy">
		    </label>
		    @if ($workflowFieldValue->workflowField->options->isNotEmpty())
		        <select class="form-control workflow_dropdown" name="fields[{{ $workflowFieldValue->workflowField->id }}]" value="{{$workflowFieldValue->value}}" {{$workflowFieldValue->workflowField->is_primary ? 'required' : ''}} {{$authUser->can('time_entry.quality_check') ? 'disabled' : ''}}>
		        	<option value="">Select</option>
		            @foreach ($workflowFieldValue->workflowField->options->sortBy('option_order') as $option)
		                <option value="{{ htmlspecialchars($option->id) }}" {{$workflowFieldValue->value == $option->id ? 'selected' : ''}}>{{ htmlspecialchars($option->option_value) }}</option>
		            @endforeach
		        </select>
		    @else
				@if($workflowFieldValue->workflowField->field_name == 'Phone')
					<button type="button" class="btn btn-outlined-call direct-call-btn">
						<img src="/images/track-time/call.png" /> 
					</button>
				@endif
					<input type="{{ $workflowFieldValue->workflowField->field_type == 'checkbox' ? 'checkbox' : 'text' }}" 
						name="fields[{{ $workflowFieldValue->workflowField->id }}]" 
						class="form-control {{ $workflowFieldValue->workflowField->field_type == 'date' ? 'datepicker' : '' }}"
						value="{{$workflowFieldValue->value}}" {{$workflowFieldValue->workflowField->is_primary ? 'required' : ''}} {{$authUser->can('time_entry.quality_check') ? 'disabled' : ''}}>
		    @endif
		</div>
	@endif
@endforeach