@php
    use Illuminate\Support\Str;
@endphp
@foreach($qc_fields as $field)
	@php
        $text = $field->field_name;
        $text = str_replace([' ', '.'], '_', $text);
        $text = preg_replace('/[^A-Za-z0-9_]/', '', $text);
        $text = Str::lower($text);
        $where_field = isset($field->process_id) ? 'workflow_qc_field_id' : 'globle_qc_field_id';
        $data = $qc_data->where($where_field, $field->id)->first();
        $name = isset($field->process_id) ? 'qc' : 'globalqc';
    @endphp
		<div class="col-md-3 w-field">
		    <label class="form-label {{ $field->field_type == 'checkbox' ? 'opacity-0' : '' }} ellipsis" data-snake="{{ucfirst(str_replace('_', ' ', trim($text)))}}" title="{{ html_entity_decode($field->field_name) }}">
		        {{ html_entity_decode($field->field_name) }}
		        <img class="ms-1 copy_field_content cursor-pointer d-none float-end" src="/images/copy.svg" alt="copy content" title="Copy">
		    </label>
		    @if ($field->options->isNotEmpty())
		        <select class="form-control workflow_dropdown" name="{{$name}}[{{ $field->id }}]" value="{{$field->value}}">
		        	<option value="">Select</option>
		            @foreach ($field->options as $option)
		                <option value="{{ htmlspecialchars($option->id) }}" {{@$data->value == $option->id ? 'selected' : ''}}>{{ htmlspecialchars($option->option_text) }}</option>
		            @endforeach
		        </select>
		    @else
				@if($field->field_name == 'Phone')
					<button type="button" class="btn btn-outlined-call direct-call-btn">
						<img src="/images/track-time/call.png" /> 
					</button>
				@endif
					<input type="{{ $field->field_type == 'checkbox' ? 'checkbox' : 'text' }}" 
						name="{{$name}}[{{ $field->id }}]" 
						class="form-control {{ $field->field_type == 'date' ? 'datepicker' : '' }}"
						value="{{@$data->value}}">
		    @endif
		</div>
@endforeach