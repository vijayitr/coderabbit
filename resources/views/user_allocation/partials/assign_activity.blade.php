@extends('layouts.app')

@section('content')
<div class="row manage_allocation">
    <div class="col-md-12">

        <div class="d-block mb-3 ">
            <h4 class="d-contents">Activity Assigned To : {{$user->name}}</h4> 
            <i class="bi bi-funnel btn btn-outlined filter-btn float-end"></i>
            <button class="btn btn-success me-2 d-none assignAllBtn float-end" type="button">
                <i class="bi bi-plus"></i>
                <span>Assign Selected</span>
            </button>
        </div>

        <form action="{{route('userAllocation.assignActivities', $id)}}" method="POST" id="AssignTaskForm">
            @csrf
            @php
                $trData = '';
            @endphp

            <div class="allCheckbox d-none">
                @foreach ($workflows as $workflow)
                    @foreach ($workflow as $activity)
                    <input type="checkbox" name="selected_activities[]" value="{{$activity->activity_id}}" {{$activity->existing_task ? 'checked disabled' : ''}} >
                        
                        @php
                            $trData .= '
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input activity_checkbox" data-id="' . $activity->activity_id . '" ' .($activity->existing_task ? 'checked disabled' : '') . '>
                                        </div>
                                    </td>
                                    <td>' . e($activity->client_name) . '</td>
                                    <td>' . e($activity->workflow_name) . '</td>
                                    <td>' . e($activity->activity_name) . '</td> 
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm assignBtn" data-id="' . $activity->activity_id . '" title="Assign Task" ' .($activity->existing_task ? 'checked disabled' : '') . '>
                                            <img src="/images/sidebar-icons/time-entry.png" alt="time-entry"> <span>' .($activity->existing_task ? 'Task Assigned' : 'Assign Task') . '</span>
                                        </button>
                                    </td>
                                </tr>';
                        @endphp
                    @endforeach
                @endforeach
            </div>
            <table class="table" id="AssignTaskTable">
                <thead>
                    <tr>
                        <th width="10%">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="checkAll">
                            </div>
                        </th>
                        <th width="20%">
                            <span class="columnTitle">Client Name</span>
                            <div class="filterBox d-none">
                                <select class="form-control client-filter">
                                    <option value="" data-filter="">Select</option>
                                    @foreach($data['clients'] ?? [] as $id => $client)
                                        <option value="{{ $id }}" data-filter="{{$client}}">{{ $client }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th width="20%">
                            <span class="columnTitle">Workflow Name</span>
                            <div class="filterBox d-none">
                                <select class="form-control workflow-filter">
                                    <option value="" data-filter="">Select</option>
                                    @foreach($data['workflows'] ?? [] as $id => $workflow)
                                        <option value="{{ $id }}" data-id="{{$workflow->client_id}}" data-filter="{{$workflow->name}}">{{ $workflow->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th width="30%">
                            <span class="columnTitle">Activity Name</span>
                            <div class="filterBox d-none">
                                <select class="form-control activity-filter">
                                    <option value="" data-filter="">Select</option>
                                    @foreach($data['activities'] ?? [] as $id => $activity)
                                        <option value="{{ $id }}" data-id="{{$activity->workflow_id}}" data-filter="{{$activity->name}}">{{ $activity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </th>
                        <th  width="20%">
                            <span>Action</span>
                        </th>
                    </tr>
                </thead>
                <tbody> {!! $trData !!} </tbody>
            </table>
        </form>

    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        var workflowsHtml = $(".workflow-filter").html(); // Store original workflows
        var activityHtml = $(".activity-filter").html(); // Store original activities

        $(".client-filter").on("change", function () {
            let selectedValue = $(this).val();
            $(".workflow-filter").html(workflowsHtml); // Reset workflow dropdown
            $(".activity-filter").html(activityHtml); // Reset activity dropdown

            let selectedWorkflows = FilterWorkflow(selectedValue);
            FilterActivity(selectedWorkflows);
        });

        $(".workflow-filter").on("change", function () {
            let selectedValue = $(this).val();
            FilterActivity([selectedValue]);
        });

        function FilterWorkflow(selectedValue) {
            let returnArr = [];
            
            $(".workflow-filter option").each(function () {
                let workflowId = $(this).data('id');
                let value = $(this).val();

                if (selectedValue && workflowId != selectedValue && value) {
                    $(this).remove();
                } else if (value) {
                    returnArr.push(value);
                }
            });

            return returnArr;
        }

        function FilterActivity(selectedValues) {
            $(".activity-filter").html(activityHtml); // Reset activity dropdown
            if (selectedValues.length) {
                $(".activity-filter option").filter(function () {
                    return !selectedValues.includes($(this).attr('data-id')) && $(this).val();
                }).remove();
            }
        }

        if (!$('.allCheckbox input:not(:checked)').length) {
           $('#checkAll').prop('checked', true);
        }
        // Initialize DataTable
        const table = $('#AssignTaskTable').DataTable({
            "processing": true,
            "serverSide": false,
            "searching": true,
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "lengthChange": false,
            "info": false,
            "language": {
                "paginate": {
                    "previous": '<i class="fa-solid fa-arrow-left"></i> Previous', 
                    "next": 'Next <i class="fa-solid fa-arrow-right"></i>' 
                }
            },
            "ordering": false
        });

        table.on('draw', function() {
            $('.activity_checkbox').each(function() {
                const id = $(this).data('id');
                $(this).prop('checked', $('.allCheckbox input[value="' + id + '"]').is(':checked'));
            });
        });
        $('.client-filter, .workflow-filter, .activity-filter').change(function() {
            var client_filter = $('.client-filter').find('option:selected').attr('data-filter');
            var workflow_filter = $('.workflow-filter').find('option:selected').attr('data-filter');
            var activity_filter = $('.activity-filter').find('option:selected').attr('data-filter');
            table.column(1).search(client_filter, true, false);
            table.column(2).search(workflow_filter, true, false);
            table.column(3).search(activity_filter, true, false);
            table.draw();
        });

        $('#checkAll').on('change', function() {
            $('.activity_checkbox, .allCheckbox input').not(':disabled').prop('checked', this.checked);
            $('.assignAllBtn').toggleClass('d-none', !$('.allCheckbox input:checked:not(:disabled)').length);

        });

        $(document).on('change', '.activity_checkbox', function() {
            const checked = this.checked;
            const id = $(this).data('id');
            $('.allCheckbox input[value="' + id + '"]').prop('checked', checked);
            $('.assignAllBtn').toggleClass('d-none', !$('.allCheckbox input:checked:not(:disabled)').length);
        });

        $(document).on('click', '.assignAllBtn', function() {
            $('.manage_allocation form').trigger('submit');
        });

        $(document).on('submit', '.manage_allocation form', function(e) {
            if (!$('.allCheckbox input:checked:not(:disabled)').length) {
                e.preventDefault();
                toastr.error('Please select a task to assign', 'Error');
            }
        });

        $(document).on('click', '.assignBtn', function(e) {
            e.preventDefault();
            var self = $(this);
            var id = $(this).data('id');
            var url = $('.manage_allocation form').attr('action');
            // console.log(url);
            // return;
            $.ajax({
                url: url,
                type: 'Post',
                data: { id:id , _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status) {
                        self.prop('disabled', true).find('span').text('Task Assigned');
                        self.closest('tr').find('input').prop({ checked: true, disabled: true });
                        toastr.success(response.message, 'success');
                    }

                },
                error: function(xhr, status, error) {
                    toastr.error(xhr.responseJSON.message, 'Error');
                }
            });
        });

       $(document).on('click', '.filter-btn', function() {
            $('.filterBox, .columnTitle').toggleClass('d-none');

            // Ensure select elements have the 'select2' class
            $('.filterBox select').addClass('select2');

            // Destroy Select2 only if it's already initialized
            $('.filterBox select').each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) {  // Check if Select2 is applied
                    $(this).select2('destroy');
                }
            });

            // Reinitialize Select2
            $('.filterBox select').select2();
        });

    });
</script>
@endpush