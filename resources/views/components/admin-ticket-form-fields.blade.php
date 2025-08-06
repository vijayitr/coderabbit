<div class="field-group mb-3 draggable">
    <div class="accordion option_container overflow-hidden">
        <div class="accordion-item">
            <h2 class="accordion-header position-relative">
                <button class="form-control collapsed text-start d-flex align-items-center justify-content-between drag-handle" type="button" data-bs-toggle="collapse" data-bs-target="#collapseField_{{$index}}" aria-expanded="false" aria-controls="collapseField_{{$index}}">
                    <div class="d-flex align-items-center">
                        <span class="head_s_no me-1"></span> <span class="head_field_name">-</span> 
                        <i class="fa fa-times ticket_field_close text-danger {{$index == 0 ? 'd-none' : ''}}"></i>
                    </div>
                </button>
            </h2>
            <div id="collapseField_{{$index}}" class="accordion-collapse collapse" aria-labelledby="headingOne" style="">
                <div class="accordion-body bg-transparent">
                    <input type="hidden" name="field_id[{{$index}}]" class="field_id" value="{{@$data->id}}">
                    <div class="row align-items-center field-group-box">
                        <div class="col-3">
                            <label class="mb-1">Field Name<span class="text-danger">*</span></label>
                            <input type="text" name="name[{{$index}}]" placeholder="Field Name" class="form-control field-name" value="{{@$data->name}}" required>
                        </div>

                        <div class="col-3">
                            <label class="mb-1">Field Type<span class="text-danger">*</span></label>
                            <select name="type[{{$index}}]" class="form-select field-type-select" required>
                                <option {{isset($data->type) && $data->type == 'text'  ? 'selected' : ''}} value="text">Text</option>
                                <option {{isset($data->type) && $data->type == 'dropdown'  ? 'selected' : ''}} value="dropdown">Dropdown</option>
                                <option {{isset($data->type) && $data->type == 'checklist'  ? 'selected' : ''}} value="checklist">Checklist</option>
                                <option {{ isset($data->type) && $data->type == 'radio' ? 'selected' : '' }} value="radio">Radio</option>
                                <option {{isset($data->type) && $data->type == 'date'  ? 'selected' : ''}} value="date">Date</option>
                            </select>
                        </div>
                        @php
                            $selectedDependencies = isset($data->dependencies) ? $data->dependencies->pluck('depends_on_option_id')->toArray() : [];
                        @endphp
                        <div class="col-4">
                            <label class="mb-1">Field Dependency</label>
                            <select name="dependency[{{$index}}][]" value="{{ json_encode($selectedDependencies) }}" class="form-select field-dependency dependency_select2" multiple>
                            </select>
                        </div>
                        <div class="col-2">
                            <div class="form-check d-flex align-items-center mt-3">
                                <input type="checkbox" name="is_required[{{$index}}]" class="form-check-input me-2" {{isset($data->is_required) && $data->is_required  ? 'checked' : ''}}>
                                <label class="form-check-label mb-0">Required</label>
                            </div>
                        </div>

                        <!-- Text Section -->
                        <div class="col-12 mt-2 text-options {{isset($data->type) && $data->type == 'text'  ? '' : 'd-none'}}"> 
                            <label class="mb-1">Prepopulate Field Options (Optional)</label>
                            <div>
                                <label class="form-check-label mb-0 text-capitalize text-dark me-2">
                                    <input type="radio" name="prepopulate[{{$index}}]" class="form-check-input me-2 mt-0" value="id" {{isset($data->prepopulate_by) && $data->prepopulate_by == 'id'  ? 'checked' : ''}}>
                                    Employee Id
                                </label>
                                <label class="form-check-label mb-0 text-capitalize text-dark me-2">
                                    <input type="radio" name="prepopulate[{{$index}}]" class="form-check-input me-2 mt-0" value="name" {{isset($data->prepopulate_by) && $data->prepopulate_by == 'name'  ? 'checked' : ''}}> Employee Name
                                </label>
                                <label class="form-check-label mb-0 text-capitalize text-dark me-2">
                                    <input type="radio" name="prepopulate[{{$index}}]" class="form-check-input me-2 mt-0" value="email" {{isset($data->prepopulate_by) && $data->prepopulate_by == 'email'  ? 'checked' : ''}}> Employee Email
                                </label>
                            </div>
                        </div>

                        <!-- Radio Section -->
                        <div class="col-6 mt-3 radio-options {{isset($data->type) && $data->type == 'radio'  ? '' : 'd-none'}}"> 
                            <div class="accordion option_container overflow-auto">
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header">
                                        <button class="form-control" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}"> Radio Box </button>
                                    </h2>
                                    <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{isset($data->id) ? '' : 'show'}}" aria-labelledby="headingOne" style="">
                                        <div class="accordion-body bg-transparent">
                                            @if(isset($data->selectOptions) && !empty($data->selectOptions) && isset($data->type) && $data->type == 'radio')
                                                @foreach($data->selectOptions as $key => $option)
                                                    <div class="row m-0 radio-item">
                                                        <div class="col-md-10 mb-2">
                                                            <input type="text" name="radio[{{$index}}][{{$option->id}}]" class="form-control radio-option" data-option_id="{{@$option->id}}" placeholder="Radio Option" value="{{ $option->option }}">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn {{$key == 0 ? 'btn-success add_radio' : 'btn-danger remove_radio'}} " aria-label="Add">
                                                                <i class="fa {{$key == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                             @else
                                                <div class="row m-0 radio-item">
                                                    <div class="col-md-10 mb-2">
                                                        <input type="text" name="radio[{{$index}}][]" class="form-control radio-option" placeholder="Radio Option" value="">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-success add_radio" aria-label="Add">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Section -->
                        <div class="col-6 mt-3 checklist-options {{isset($data->type) && $data->type == 'checklist'  ? '' : 'd-none'}}"> 
                            <div class="accordion option_container overflow-auto">
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header">
                                        <button class="form-control" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_{{$index}}" aria-expanded="true" aria-controls="collapseOne_{{$index}}"> Checklist </button>
                                    </h2>
                                    <div id="collapseOne_{{$index}}" class="accordion-collapse collapse {{isset($data->id) ? '' : 'show'}}" aria-labelledby="headingOne" style="">
                                        <div class="accordion-body bg-transparent">
                                            @if(isset($data->selectOptions) && !empty($data->selectOptions) && isset($data->type) && $data->type == 'checklist')
                                                @foreach($data->selectOptions as $key => $option)
                                                    <div class="row m-0 checklist-item">
                                                        <div class="col-md-10 mb-2">
                                                            <input type="text" name="checklist[{{$index}}][{{$option->id}}]" class="form-control checklist-option" data-option_id="{{@$option->id}}" placeholder="Checklist Option" value="{{ $option->option }}">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn {{$key == 0 ? 'btn-success add_checklist' : 'btn-danger remove_checklist'}} " aria-label="Add">
                                                                <i class="fa {{$key == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                             @else
                                                <div class="row m-0 checklist-item">
                                                    <div class="col-md-10 mb-2">
                                                        <input type="text" name="checklist[{{$index}}][]" class="form-control checklist-option" placeholder="Checklist Option" value="">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-success add_checklist" aria-label="Add">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Options -->
                        <div class="col-6 mt-3 dropdown-options {{isset($data->type) && $data->type == 'dropdown'  ? '' : 'd-none'}}"> 
                            <div class="accordion option_container overflow-auto ">
                                <div class="accordion-item mb-2">
                                    <h2 class="accordion-header">
                                        <button class="form-control" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne_drop_{{$index}}" aria-expanded="true" aria-controls="collapseOne_drop_{{$index}}"> Options </button>
                                    </h2>
                                    <div id="collapseOne_drop_{{$index}}" class="accordion-collapse collapse {{isset($data->id) ? '' : 'show'}}" aria-labelledby="headingOne" style="">
                                        <div class="accordion-body bg-transparent">
                                            @if(isset($data->selectOptions) && !empty($data->selectOptions) && isset($data->type) && $data->type == 'dropdown')
                                                @foreach($data->selectOptions as $key => $option)
                                                    <div class="row m-0 ticket-dropdown-item">
                                                        <div class="col-md-10 mb-2">
                                                            <input type="text" name="dropdown[{{$index}}][{{$option->id}}]" class="form-control dropdown-option" data-option_id="{{$option->id}}" placeholder="Dropdown Option" value="{{ @$option->option }}">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn {{$key == 0 ? 'btn-success add_dropdown' : 'btn-danger remove_dropdown'}}" aria-label="Remove">
                                                                <i class="fa {{$key == 0 ? 'fa-plus' : 'fa-minus'}}"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="row m-0 ticket-dropdown-item">
                                                    <div class="col-md-10 mb-2">
                                                        <input type="text" name="dropdown[{{$index}}][]" class="form-control dropdown-option" placeholder="Dropdown Option" value="">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-success add_dropdown" aria-label="Remove">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>