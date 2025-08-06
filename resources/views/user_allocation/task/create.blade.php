@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">{{isset($client) ? 'Edit' : ''}} Assign Activity</h3>
    
    <form action="{{ isset($client) ? route('userAllocation.taskUpdate', $client->id) : route('userAllocation.taskStore') }}" method="POST">
        @csrf
        @if(isset($client))
            @method('PUT')
        @endif
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <label>User</label>
                       <select name="user" class="form-control select2" placeholder="Select User" required>
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{$user->id}}">{{$user->name}}</option>
                        @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <label>Clients</label>
                       <select name="client" class="form-control select2" placeholder="Select Client" required>
                            <option value="">Select Client</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <label>Activities</label>
                        <select name="process" class="form-control select2" required>
                            <option value="">Select Activity</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mb-2">
                    <div class="form-group">
                        <label>Priority</label>
                       <select name="priority" class="form-control" required>
                            <option value="">Select Priority</option>
                            <option value="4">Urgent</option>
                            <option value="3">High Priority</option>
                            <option value="2">Normal</option>
                            <option value="1">Low Priority</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{isset($client) ? 'Update' : 'Assign'}}</button>
    </form>
</div>

@endsection

@push('scripts')
<script type="text/javascript">

    // Get Clients
    $(document).on('change', 'select[name="user"]', function() {
        let userId = $(this).val();
        let options = '<option value="">Select Client</option>';
        $('select[name="client"]').html(options);
        $('select[name="process"]').html('<option value="">Select Activity</option>');
        if (userId == '') return;
        var url = '{{ route("clients.getClientByUser", "/") }}';
        $.ajax({
            url: url+'/'+userId,
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    response.data.forEach(client => {
                        options += `<option value="${client.id}">${client.client_name}</option>`;
                    });

                    $('select[name="client"]').html(options);
                } else {
                    toastr.warning('No clients found for the selected user.', 'Warning');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong.', 'Error');
            }
        });
    });

    // Get Activities
    $(document).on('change', 'select[name="client"]', function() {
        let clientId = $(this).val();
        let options = '<option value="">Select Activity</option>';
        $('select[name="process"]').html(options);
        if (clientId == '') return;
        console.log('clientId = ',clientId);
        var url = '{{ route("workflows.getAllWorkflows", "/") }}';
        $.ajax({
            url: url+'/'+clientId,
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    response.data.forEach(activity => {
                        activity.process_names.forEach(process => {
                            options += `<option value="${process.id}">${process.process_name}</option>`;
                        });
                    });

                    $('select[name="process"]').html(options);
                } else {
                    toastr.warning('No Activities found for the selected client.', 'Warning');
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Something went wrong.', 'Error');
            }
        });
    });

   

</script>
@endpush
