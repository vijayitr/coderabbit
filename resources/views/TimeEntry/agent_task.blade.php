
  <div class="container px-0" id="TimeEntry">

  	<div class="row mb-4">
	    <div class="col-md-3">
	        <input type="input" class="rangedatepicker form-control">
	        <input type="hidden" class="start_date" name="">
	        <input type="hidden" class="end_date" name="">
	    </div>
		<div class="col-md-3 {{$authUser->can('time_entry.quality_check') ? '' : 'd-none'}}">
            <select class="form-control user-filter select2" data-placeholder="Select User">
                <option value="">Select User</option>
                @foreach($filter_data['users'] as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </select>
        </div>		
	    <div class="{{$authUser->can('time_entry.quality_check') ? 'col-md-2' : 'col-md-3'}}">
	        <select class="form-control client-filter select2" data-placeholder="Select Client">
	            <option value="" data-user="">Select Client</option>
	            @foreach($filter_data['clients'] as $client)
	                <option value="{{$client['id']}}" class="d-none" data-user="{{$client['assigned_to']}}">{{$client['client_name']}}</option>
	            @endforeach
	        </select>
	    </div>

	    <div class="{{$authUser->can('time_entry.quality_check') ? 'col-md-2' : 'col-md-3'}}">
	        <select class="form-control workflow-filter select2" data-placeholder="Select Workflow">
	            <option value="" data-client="">Select Workflow</option>
	        </select>
	    </div>

	    <div class="{{$authUser->can('time_entry.quality_check') ? 'col-md-2' : 'col-md-3'}}">
	        <select class="form-control activity-filter select2" data-placeholder="Select Process Name">
	            <option value="" data-workflow="">Select Process Name</option>
	        </select>
	    </div>
</div>
    <div class="table-responsive">
        <table class="table table-borderless align-middle" id="TimeEntryTable">
            <thead class="bg-light">
                <tr class="align-middle">
                    <th class="align-middle py-3" scope="col">
                        <input type="checkbox" class="form-check-input mt-0 me-3">
                        Patients ID/Name
                    </th>
                    <th class="align-middle py-3" scope="col">Client</th>
                    <th class="align-middle py-3" scope="col">Process Name</th>
                    <th class="align-middle py-3" scope="col">Status</th>
                    <th class="align-middle py-3" scope="col">
                    	@if ($authUser->can('time_entry.create'))
	                        <button type="button" class="btn btn-dark float-end d-flex align-items-center create-task-button py-0">
							    <i class="bi bi-plus fs-5"></i>
							    <span>Create Task</span>
							</button>
						@endif
                    </th>
                </tr>
            </thead>
            <tbody>
            	@if ($authUser->can('time_entry.create'))
            	<tr class="create-task-button">
            		<td class="p-3" colspan="6">
            			<i class="bi bi-plus-lg"></i> Create Task
            		</td>
            	</tr>
            	@endif

            	<tr class="create_task_box d-none">
            		<td colspan="6" class="px-0">
		            		<div class="row filter-bar align-items-center m-0">
							    <!-- Main Content: col-10 -->
							    <div class="col-10">
							        <div class="row">
							            <!-- Client -->
							            <div class="col-lg-4 col-md-6 col-sm-12">
							                <x-select
							                    name="client"
							                    label="Client"
							                    :options="[]"
							                    selected="2"
							                    iconImg="/images/track-time/client.png"
							                />
							            </div>

							            <div class="col-lg-4 col-md-6 col-sm-12">
							                <x-select
							                    name="workflow"
							                    label="Workflow"
							                    :options="[]"
							                    selected="2"
							                    iconImg="/images/track-time/client.png"
							                />
							            </div>
							            <!-- Activity -->
							            <div class="col-lg-4 col-md-6 col-sm-12">
							                <x-select
							                    name="activity"
							                    label="Process Name"
							                    :options="[]"
							                    selected="2"
							                    class_type="workflow_id"
							                    iconImg="/images/track-time/activity.png"
							                />
							            </div>
							            <!-- Task Category -->
							          <!--   <div class="col-lg-4 col-md-6 col-sm-12">
							                <x-select
											    name="task_category"
											    label="Task Category"
											    class_type="category_id"
											    :options="[
											        (object) ['id' => '1', 'value' => 'Urgent'], 
											        (object) ['id' => '2', 'value' => 'Category 2'], 
											        (object) ['id' => '3', 'value' => 'Category 3'], 
											        (object) ['id' => '4', 'value' => 'Category 4'], 
											        (object) ['id' => '5', 'value' => 'Category 5']
											    ]"
											    selected="2"
											    iconImg="/images/track-time/task_category.png"
											/>
							            </div> -->
							        </div>
							    </div>
							    
							    <!-- Start Button: col-2 -->
							    <div class="col-2 d-flex align-items-center justify-content-end">
							        <button class="btn btn-outline-primary px-4 py-1 Workflow_start_btn btn-outlined d-none">Start</button>
							        <button class="btn btn-outline-primary px-2 py-1 Workflow_call_btn btn-outlined-call d-none">
							        		<img src="/images/track-time/call.png"/> <span>Call</span>  
							        </button>

									<div class="top-call-controls d-none">
										<a href="#" class="call_end_btn px-3 py-2 text-decoration-none text-white">
										End Call <span class="call_timer">00:00</span></a>
									</div>
									

							    </div>

				        		<form action="{{ route('timeEntry.store') }}" id="TaskForm" class="d-none" method="POST">
				        			@csrf
				        			<input type="hidden" name="client_id" value="" />
				        			<input type="hidden" name="process_id" value="" />
				        			<input type="hidden" name="category_id" value="" />
				        			<input type="hidden" name="assignment_id" value="" />
				        			<input type="hidden" name="qc_status" value="pending" />
				        			@if ($authUser->can('time_entry.quality_check'))
				        				<input type="hidden" class="qc_agent" name="">
				        			@endif
				        			<div class="row">
									     <div class="col-12 filter-bar-row">
				            				<div class="card Workflow_card shadow-sm m-0">
											    <div class="row g-3 Workflow_field_box">
											    </div>
											</div>
									     </div>

									    <div class="col-4 dialer_box d-none">
									    	<x-time-entry.dialer-content-box />
									    </div>
								    </div>
								     <div class="d-flex justify-content-end align-items-center mt-4 filter-bar-buttons">
									        <!-- Buttons -->
									        <button type="button" class="pause_task control"><img src="/images/track-time/pause.png" alt="pause"></button>
									        <button type="button" class="play_task control">
									            <img src="/images/track-time/play.png" alt="Pause">
									        </button>
									        <select class="form-control me-3 pe-4 w-auto btn-outlined task_status d-none" name="status">
									        	<option value="pending">Pending</option>
									        	<option value="completed">Completed</option>
									        	<option value="in_progress">In Progress</option>
									        	<option value="on_hold">On Hold</option>	
									        </select>
									        <button type="button" class="btn btn-outline-primary me-2 Workflow_task_end_btn btn-outlined">End Task</button>
									        <button type="submit" class="btn btn-primary Workflow_saveTask_btn btn-filled me-2">Partial Save</button>
									        <button type="button" class="btn btn-primary onlySaveTask btn-filled">Save Task</button>
									    </div>
								</form>
							</div>

					</td>
            	</tr>
            </tbody>
        </table>
    </div>

	<x-pause-modal />
</div>

<input type="hidden" name="resume_url" value="{{ route('timeEntry.resumeTasks') }}">

<!-- Confirmation Modal -->
@php
$buttons = [
    ['label' => 'Confirm', 'class' => 'btn-primary', 'id' => 'confirmDelete'],
    ['label' => 'Cancel', 'class' => 'btn-secondary'],
];
@endphp
<x-modal 
    id="confirmationModal" 
    title="Confirm Deletion" 
    body="Are you sure you want to delete the selected records?" 
    :buttons="$buttons" 
/>

@push('scripts')
	<script>


	    $(document).ready(function () {
			var clients = [];
		    @foreach($filter_data['clients'] as $client)
		        clients.push({
		            id: "{{ $client['id'] }}",
		            client_name: "{{ $client['client_name'] }}",
		            assigned_to: "{{ $client['assigned_to'] }}"
		        });
		    @endforeach

		    var workflows = [];
		    @foreach($filter_data['workflows'] as $workflow)
		        workflows.push({
		            id: "{{ $workflow['id'] }}",
		            workflow_name: "{{ $workflow['workflow_name'] }}",
		            client_id: "{{ $workflow['client_id'] }}"
		        });
		    @endforeach

		    var activities = [];
		    @foreach($filter_data['process_names'] as $activity)
		        activities.push({
		            id: "{{ $activity['id'] }}",
		            process_name: "{{ $activity['process_name'] }}",
		            workflow_id: "{{ $activity['workflow_id'] }}"
		        });
		    @endforeach

		    $('.user-filter').on('change',async  function () {
			    const userId = $(this).val();
			  	await filterClientByUser(userId);
			  	getTimeEntryTasks();
			});

			function filterClientByUser(userId) {
				return new Promise((resolve) => {
					var filteredClients = clients.filter(function(client) {
					    const assignedUsers = JSON.parse(client.assigned_to).map(String); // make all strings
					    return userId === "" || assignedUsers.includes(String(userId));
					});

			        // If you want to populate a dropdown with these workflows:
			        var $clientFilter = $('.client-filter');
					var previousVal = $clientFilter.val();
					$clientFilter.empty().append('<option value="">Select Workflow</option>');
					filteredClients.forEach(function(client) {
					    $clientFilter.append('<option value="' + client.id + '">' + client.client_name + '</option>');
					});
					if ($clientFilter.find('option[value="' + previousVal + '"]').length > 0) {
					    $clientFilter.val(previousVal);
					} else {
					    $clientFilter.val('');
					}
					$clientFilter.trigger('change');
					$clientFilter.select2();
			        resolve();
			    });
			}

			function filterWorkflowByClient(clientId) {
				return new Promise((resolve) => {
					var filteredWorkflows = workflows.filter(function(workflow) {
			            return workflow.client_id == clientId;
			        });

			        // If you want to populate a dropdown with these workflows:
			        var $workflowFilter = $('.workflow-filter');
					var previousVal = $workflowFilter.val();
					$workflowFilter.empty().append('<option value="">Select Workflow</option>');
					filteredWorkflows.forEach(function(workflow) {
					    $workflowFilter.append('<option value="' + workflow.id + '">' + workflow.workflow_name + '</option>');
					});
					if ($workflowFilter.find('option[value="' + previousVal + '"]').length > 0) {
					    $workflowFilter.val(previousVal);
					} else {
					    $workflowFilter.val('');
					}
					$workflowFilter.trigger('change');
					$workflowFilter.select2();
			        resolve();
			    });
			}

			function filterProcessNameByWorkflow(workflowId) {
				return new Promise((resolve) => {
					var filteredProcesss = activities.filter(function(activity) {
			            return activity.workflow_id == workflowId;
			        });

			        // If you want to populate a dropdown with these workflows:
			        var $processFilter = $('.activity-filter');
					var previousVal = $processFilter.val();
					$processFilter.empty().append('<option value="">Select Process Name</option>');
					filteredProcesss.forEach(function(activity) {
					    $processFilter.append('<option value="' + activity.id + '">' + activity.process_name + '</option>');
					});
					if ($processFilter.find('option[value="' + previousVal + '"]').length > 0) {
					    $processFilter.val(previousVal);
					} else {
					    $processFilter.val('');
					}
					$processFilter.trigger('change');
					$processFilter.select2();
			        // then resolve
			        resolve();
			    });
			}

			// Workflow Filter Change Event
			let isHumanTrigger = false;

			$('.workflow-filter, .activity-filter, .client-filter').on('select2:selecting', function () {
			    isHumanTrigger = true;
			});

			$('.client-filter').on('change',async function () {
				const clientId = $(this).val();
			    const workflowId = $(this).val();
			    if (isHumanTrigger || clientId === '') {
			  		await filterWorkflowByClient(clientId);
			    }

			    if (isHumanTrigger && clientId === '') {
			        getTimeEntryTasks();
			    }
			    isHumanTrigger = false;
			});

			$('.workflow-filter').on('change',async function () {
			    const workflowId = $(this).val();
			    if (isHumanTrigger || workflowId === '') {
			        await filterProcessNameByWorkflow(workflowId);
			    }

			    if (isHumanTrigger && workflowId === '') {
			        getTimeEntryTasks();
			    }
			    isHumanTrigger = false;
			});

			$('.activity-filter').on('change', function () {
				const activity = $(this).val();
				console.log('inside activity filter', activity);
			    if (isHumanTrigger) {
			        getTimeEntryTasks();
			    }
			    isHumanTrigger = false;
			});

	    	$(document).on('click', '.onlySaveTask', function(e) {
			    e.preventDefault();

			    const $form = $('#TaskForm');

			    // Append hidden input `partial` if not already present
			    if ($form.find('input[name="partial"]').length === 0) {
			        $('<input>').attr({
			            type: 'hidden',
			            name: 'partial',
			            value: '1'
			        }).appendTo($form);
			    }

			    const formData = $form.serialize(); // use FormData if files are involved

			    $.ajax({
			        url: $form.attr('action'),
			        type: $form.attr('method'),
			        data: formData,
			        success: function(response) {
			            toastr.success("Task data has been saved successfully.", "Success");
			        },
			        error: function(xhr) {
			            // console.error('Error:', xhr);
			        },
			        complete: function() {
			            // Remove the input after AJAX completes (success or fail)
			        }
			    });
			    $form.find('input[name="partial"]').remove();
			});



	    	flatpickr(".rangedatepicker", {
	            mode: "range",
	            dateFormat: "Y-m-d",
	            allowInput: false,
	            onReady: function(selectedDates, dateStr, instance) {
	                instance.input.classList.add('no-custom-arrow');
	                instance.input.setAttribute('placeholder', 'Select a date range');
	            },
	            onOpen: function(selectedDates, dateStr, instance) {
	                instance.calendarContainer.classList.add('no-custom-arrow');
	            },
	            onChange: function(selectedDates, dateStr, instance) {
	                if (selectedDates.length === 2) { // Ensure both dates are selected
	                    var startDate = new Date(selectedDates[0].getTime() + 86400000).toISOString().split('T')[0];
	                    var endDate = new Date(selectedDates[1].getTime() + 86400000).toISOString().split('T')[0];
	                    $('input.start_date').val(startDate);
	                    $('input.end_date').val(endDate);
	                    getTimeEntryTasks();
	                }
	            }
	        });

	    	var client_options = '<li class="p-2"><input type="text" class="form-control search-list-options" value="" placeholder="Search Client"></li>';
	    	var workflow_options = '<li class="p-2"><input type="text" class="form-control search-list-options" value="" placeholder="Search Workflow"></li>';
	    	var activity_options = '<li class="p-2"><input type="text" class="form-control search-list-options" value="" placeholder="Search Process Name"></li>';
	    	@foreach($AssignActivities as $assigned)
			    client_options += '<li class="list_option" data-assignedto="" value="{{ $assigned->activity->workflow->client->id }}"><span class="option-text">{{ $assigned->activity->workflow->client->client_name }}</span></li>';
			    workflow_options += '<li class="list_option d-none" data-assignedto="" value="{{ $assigned->activity->workflow->id }}" data-client_id="{{$assigned->activity->workflow->client->id}}"><span class="option-text">{{ $assigned->activity->workflow->workflow_name }}</span></li>';
			    activity_options += '<li class="list_option d-none" value="{{$assigned->activity_id}}" data-workflow_id="{{$assigned->activity->workflow->id}}" data-assignment_id="{{$assigned->id}}"><span class="option-text">{{$assigned->activity->process_name}}</span></li>';
			@endforeach
			$('.select-menu [name="client"]').closest('.select-menu').find('ul.option_list').html(client_options);
			$('.select-menu [name="workflow"]').closest('.select-menu').find('ul.option_list').html(workflow_options);
			$('.select-menu [name="activity"]').closest('.select-menu').find('ul.option_list').html(activity_options);

			$(document).on('input', '.search-list-options', function () {
			    var value = $(this).val().toLowerCase();
			    var $list = $(this).closest('.option_list').find('li.list_option');

			    $list.each(function () {
			        var text = $(this).find('.option-text').text().toLowerCase();
			        if (text.includes(value)) {
			            $(this).show();
			        } else {
			            $(this).hide();
			        }
			    });
			});

			$(document).on('mouseenter', '.w-field', function() {
			    const value = $(this).find('input, select').val();
			    if (value != '') {
				    $(this).find('.copy_field_content').removeClass('d-none');
			    }
			});

			$(document).on('mouseleave', '.w-field', function() {
			    $(this).find('.copy_field_content').addClass('d-none');
			});

			$(document).on('click', '.copy_field_content', function() {
			    const container = $(this).closest('.w-field');
			    let value = '';

			    const input = container.find('input');
			    const select = container.find('select');

			    if (input.length > 0) {
			        value = input.val();
			    } else if (select.length > 0) {
			        value = select.find('option:selected').text();
			    }

			    // Copy to clipboard
			    const tempInput = $("<input>");
			    $("body").append(tempInput);
			    tempInput.val(value).select();
			    document.execCommand("copy");
			    tempInput.remove();

			    // Toastr success message
			    toastr.success("Copied to clipboard: " + value, 'Success');
			});



			RemoveDuplicates();
	        function RemoveDuplicates() {
	        	let seen = {};
				$('.select-menu [name="client"]').closest('.select-menu')
				    .find("ul.option_list .list_option")
				    .each(function() {
				        let key = $(this).attr("value");
				        if (seen[key] !== undefined) {
				            $(this).remove();
				        } else {
				            seen[key] = true;
				        }
				    });

				let seen_workflow = {};
				$('.select-menu [name="workflow"]').closest('.select-menu')
				    .find("ul.option_list .list_option")
				    .each(function() {
				        let keys = $(this).attr("value");
				        if (seen_workflow[keys] !== undefined) {
				            $(this).remove();
				        } else {
				            seen_workflow[keys] = true;
				        }
				    });

				let seen_activity = {};
				$('.select-menu [name="activity"]').closest('.select-menu')
				    .find("ul.option_list .list_option")
				    .each(function() {
				        let activity_key = $(this).attr("value");
				        if (seen_activity[activity_key] !== undefined) {
				            $(this).remove();
				        } else {
				            seen_activity[activity_key] = true;
				        }
				    });

	        }

	        $(document).on('click', '.list_option', function() {
	        	if ($(this).closest('.select-menu').find('input[name="client"]').length) {
	        		var client_id = $(this).val();
	        		// getWorkflow($(this).attr('value'), $(this).attr('process_id'));
	        		var select_menu = $('.select-menu [name="workflow"]').closest('.select-menu');
		            select_menu.find('input').val('').trigger('change');
		            select_menu.find('.sBtn-text').text('Select');

		            select_menu.find('.list_option').addClass('d-none');
		            select_menu.find('.list_option[data-client_id="' + client_id + '"]').removeClass('d-none');

		            var select_activity = $('.select-menu [name="activity"]').closest('.select-menu');
		            select_activity.find('.list_option').addClass('d-none');

		        }

		        if ($(this).closest('.select-menu').find('input[name="workflow"]').length) {
	        		var wrokflow_id = $(this).val();
	        		// getWorkflow($(this).attr('value'), $(this).attr('process_id'));
	        		var select_menu = $('.select-menu [name="activity"]').closest('.select-menu');
		            select_menu.find('input').val('').trigger('change');
		            select_menu.find('.sBtn-text').text('Select');

		            select_menu.find('.list_option').addClass('d-none');
		            select_menu.find('.list_option[data-workflow_id="' + wrokflow_id + '"]').removeClass('d-none');
		        }


	        });

	        getTimeEntryTasks();

	        $(document).on('change', '.user-filter, .client-filter, .workflow-filter, .activity-filter', function(){
			    // getTimeEntryTasks();
	        });

	        function getTimeEntryTasks() {
	        	var data = {
	        		'user': $('.user-filter').val(),
	        		'client': $('.client-filter').val(),
	        		'workflow': $('.workflow-filter').val(),
	        		'process_name': $('.activity-filter').val(),
	        		'start_date': $('input.start_date').val(),
	        		'end_date': $('input.end_date').val(),
	        	};
	        	$.ajax({
		            url: "{{ route('timeEntry.getTimeEntryTasks') }}",
		            type: 'GET',
		            dataType: 'json',
		            data:data,
		            success: function (response) {
		                if (response.status === 'success') {
		                	if (response.html != '') {
		                		$('tr.create-task-button').addClass('d-none').fadeOut(500);
		                	}
		                	$('table#TimeEntryTable tbody .taskTr').remove();
							$('table#TimeEntryTable tbody').append(response.html);

		                } else {
		                    console.error('Failed to load workflows');
		                }
		            },
		            error: function (xhr) {
		                console.error(xhr.responseText);
		            }
		        });
	        }

			function getCallList(assignment_id,page=1) {
	        	$.ajax({
		            url: "{{ route('timeEntry.getCallList') }}?assignment_id="+assignment_id+"&page="+page,
		            type: 'GET',
		            dataType: 'json',
		            success: function (response) {
		                if (response.status === 'success') {
		                	if (response.html != '') {
		                		$('#call_list_tab').html(response.html);
		                	}
		                	
		                } else {
		                    console.error('Failed to load CALL LIST');
		                }
		            },
		            error: function (xhr) {
		                console.error(xhr.responseText);
		            }
		        });
	        }

			$(document).on('click', '#call_list_tab .custom-pagination a', function(event) {
				event.preventDefault();
				var assignment_id = $('input[name="activity"]').attr('data-assignment_id');
				var page = $(this).attr('href').split('page=')[1]; // Extract page number
				getCallList(assignment_id,page);
			});

	        $(document).on('click', '.edit_task', function() {
	        	var process_id = $(this).attr('data-process-id');
	        	getCheckList(process_id);
	        	getScript(process_id);
	        	getNotes();
	        });

	        function getCheckList(process_id) {
	        	$('#dial-tab').tab('show');
	        	if (!$('.dialer_box').hasClass('d-none')) {
	        		$('.Workflow_call_btn').trigger('click');
	        	}
	        	var assignment_id = $('#TaskForm input[name="assignment_id"]').val();
	        	$.ajax({
		            url: "{{ route('timeEntry.getCheckList') }}?process_id="+process_id,
		            type: 'GET',
		            data: {
			            assignment_id: assignment_id,
			            process_id: process_id,
			        },
		            dataType: 'json',
		            success: function (response) {
		                if (response.status === 'success') {
		                	$('#check_list').html(response.html);
		                } else {
		                    console.error('Failed to load CALL LIST');
		                }
		            },
		            error: function (xhr) {
		                console.error(xhr.responseText);
		            }
		        });
	        }

	        function getScript(process_id) {
	        	$('#dial-tab').tab('show');
	        	if (!$('.dialer_box').hasClass('d-none')) {
	        		$('.Workflow_call_btn').trigger('click');
	        	}
	        	var assignment_id = $('#TaskForm input[name="assignment_id"]').val();
	        	$.ajax({
		            url: "{{ route('timeEntry.getScript') }}?process_id="+process_id,
		            type: 'GET',
		            data: {
			            assignment_id: assignment_id,
			            process_id: process_id,
			        },
		            dataType: 'json',
		            success: function (response) {
		                if (response.status === 'success') {
		                	if (response.html == '') {
		                		response.html = '<div class="alert alert-warning text-center p-2" role="alert"><i class="fa-solid fa-thumbs-down me-"></i> No Script Available.</div>';
		                	}
		                	$('#task_script .script_content').html(response.html);
		                } else {
		                    console.error('Failed to load Script');
		                }
		            },
		            error: function (xhr) {
		                console.error(xhr.responseText);
		            }
		        });
	        }

	        function getNotes() {
	        	var assignment_id = $('#TaskForm input[name="assignment_id"]').val();
	        	$.ajax({
		            url: "{{ route('timeEntry.getNotes') }}",
		            type: 'GET',
		            data: {
			            assignment_id: assignment_id
			        },
		            dataType: 'json',
		            success: function (response) {
		                if (response.status === 'success') {
		                	$('#myTabContent .accordion.notes_container').html(response.html);
		                } else {
		                    console.error('Failed to load CALL LIST');
		                }
		            },
		            error: function (xhr) {
		                console.error(xhr.responseText);
		            }
		        });
	        }

	        $(document).on('change', '.checklist_option1', function() {
			    var checklist_id = $(this).val();
			    var assignment_id = $('#TaskForm input[name="assignment_id"]').val();
			    var isChecked = $(this).is(':checked');

			    $.ajax({
			        url: "{{ route('timeEntry.checklist.update') }}", 
			        type: 'POST',
			        data: {
			            checklist_id: checklist_id,
			            assignment_id: assignment_id,
			            action: isChecked ? 'add' : 'remove',
			            _token: $('meta[name="csrf-token"]').attr('content') // CSRF token
			        },
			        success: function(response) {
			        	toastr.success(response.message, 'Success');
			        },
			        error: function(xhr) {
			           toastr.error(xhr.responseText, 'Error');
			        }
			    });
			});


			$(document).on('click', '#call-list-tab', function() {
				 //if($("#call_list_tab").children().length > 0) return;
				getCallList($('input[name="activity"]').attr('data-assignment_id'));
			});

	        $(document).on('change', '.select-menu input[name="activity"]', function(){
	        	var id = $(this).val();
	        	getWorkflowFields(id);
	        });

	        function getWorkflowFields(id) {
			    return new Promise((resolve, reject) => {
			    	var is_edit = localStorage.getItem('isEdit');
			        $.ajax({
			            url: '{{ route("workflows.getWorkflowsFields") }}',
			            type: 'GET',
			            dataType: 'json',
			            data: { id: id, _token: '{{ csrf_token() }}' },
			            success: function (response) {
			                if (response.status === 'success') {
			                	if (!is_edit) {
			                    	$('.Workflow_card .Workflow_field_box').html(response.html);
			                	} else {
			                		localStorage.removeItem('isEdit');
			                	}
			                    resolve(response); // Resolve the promise with the response
			                } else {
			                    console.error('Failed to load workflows');
			                    reject('Failed to load workflows'); // Reject the promise with an error message
			                }
			            },
			            error: function (xhr) {
			                console.error(xhr.responseText);
			                reject(xhr.responseText); // Reject the promise with the error response
			            }
			        });
			    });
			}

			$(document).on('click', '.Workflow_task_end_btn', function() {
				$(this).closest('div').find('.task_status').val('completed');
				$(this).closest('form').find('input[name="qc_status"]').val('completed');
				$(this).closest('form').trigger('submit');
			});

			// Single delete
	        $(document).on('click', '.Workflow_end_btn ', function(e) {
	            e.preventDefault();
	            var processID = $(this).closest('tr').data('processid');
	            var assignment_id = $(this).closest('tr').data('assignment_id');
	            $('#confirmationModal .modal-body').html('Are you sure you want to complete this task?');
	            $('#confirmationModal').modal('show');

	            $('#confirmDelete').off('click').on('click', function() {

	                 $.ajax({
	                    url: '{{ route("timeEntry.completeTask") }}',
	                    type: 'post',
	                    data: {  _token: '{{ csrf_token() }}', process_id: processID, assignment_id:assignment_id, status:'completed' },
	                    success: function(response) {
	                        $('#confirmationModal').modal('hide');
	                        location.reload();
	                    },
	                    error: function(xhr) {
	                        toastr.error('Something went wrong. Please try again later.', 'Error');
	                    }
	                });

	            });
	        });

	        $(document).on('click', '.Workflow_start_btn', function() {

			    var process_id = $(document).find('input[name="activity"]').val();
			    var assignment_id = $(document).find('input[name="activity"]').attr('data-assignment_id');

	        	if ($('#TimeEntryTable tr[data-assignment_id="'+assignment_id+'"]').length) {
	        		$('#TimeEntryTable tr[data-assignment_id="'+assignment_id+'"]').find('.edit_task').click();
	        	} else {
				    $('#TaskForm, .Workflow_call_btn').removeClass('d-none').fadeIn(500);
				    $('#TaskForm').attr('mode','add');
				    $(this).addClass('d-none');
				    $.ajax({
	                    url: '{{ route("timeEntry.startTask") }}',
	                    type: 'post',
	                    data: {  _token: '{{ csrf_token() }}', process_id: process_id, assignment_id:assignment_id},
	                    success: function(response) {
	                    	$('input[name="assignment_id"]').val(assignment_id);
	                    	toastr.success(response.message, 'Success');
	                    },
	                    error: function(xhr) {
	                        toastr.error(xhr.responseJSON.message, 'Error');
	                    }
	                });
	        	}
	        	getCheckList(process_id);
	        	getNotes();
			});

			$(document).on('click', '.play_task ', function(e) {
	            e.preventDefault();
	            var processID = $(this).closest('tr').data('processid');
	            $('#confirmationModal .modal-body').html('Are you sure you want to resume this task?');
	            $('#confirmationModal').modal('show');

	            $('#confirmDelete').off('click').on('click', function() {

	                 $.ajax({
	                    url: '{{ route("timeEntry.endBreak") }}',
	                    type: 'post',
	                    data: {  _token: '{{ csrf_token() }}', process_id: processID },
	                    success: function(response) {
	                        $('#confirmationModal').modal('hide');
	                        localStorage.setItem('isBreak', '');
	                        location.reload();
	                    },
	                    error: function(xhr) {
	                        toastr.error('Something went wrong. Please try again later.', 'Error');
	                    }
	                });

	            });
	        });

			$(document).on('change', '.pause_reason, #pauseOptionsGroup input', function() {
		        const isRequired = !($('.pause_reason').val().trim() || $('#pauseOptionsGroup input:checked').length);
		        $('.pause_reason, #pauseOptionsGroup input').attr('required', isRequired);
		    });

		    $(document).on('click', '.save_notes1', function() {
			    var note = $('.notes_box').val();
			    var assignment_id = $('#TaskForm input[name="assignment_id"]').val();

			    if (note !== '') {
			        $.ajax({
			            url: "{{ route('timeEntry.updateNotes') }}",
			            type: 'POST',
			            data: {
			                _token: $('meta[name="csrf-token"]').attr('content'),
			                assignment_id: assignment_id,
			                note: note
			            },
			            success: function(response) {
			                if (response.success) {
			                	$('.notes_box').val('');
			                    getNotes();
			                    toastr.success(response.message, 'Success');
			                } else {
			                    toastr.error(response.message, 'Error');
			                }
			            },
			            error: function(xhr) {
			                 toastr.error(xhr.responseText, 'Error');
			            }
			        });
			    } else {
			        toastr.warning('Enter Note First', 'Warning');
			    }
			});



	    });

		@if ($authUser->can('time_entry.agent_tasks') && $authUser->can('time_entry.manager_tasks') )
		        // Get all toggle buttons and content divs
		        $(".toggle-btn").click(function () {
		            $('input.start_date').val('');
		            $('input.end_date').val('');
		            // Remove active class from all buttons
		            $(".toggle-btn").removeClass("active");

		            // Hide all tab contents
		            $(".tab-pane").removeClass("show active");

		            // Activate clicked button and corresponding tab
		            $(this).addClass("active");
		            $("#" + $(this).data("target")).addClass("show active");
		        });
		    @endif
        
	</script>
@endpush
