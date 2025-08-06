@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        @if($authUser->can('call_allocation.assign_calls'))
        @endif
            <div class="d-flex justify-content-between align-items-center mb-3 filter-bar">
                <h4 class="mb-0">QC Parameters</h4>
                
                <div>
                    <a href="{{ route('qc-parameters.create') }}" class="btn btn-dark py-0 d-flex align-items-center">
                        <i class="bi bi-plus fs-5 me-1"></i>
                        <span>Add</span>
                    </a>
                </div>
            </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table" id="qc_param">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Weight</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($parameters as $param)
                <tr>
                    <td>{{ $param->name }}</td>
                    <td>{{ $param->weight }}</td>
                    <td>{{ $param->order }}</td>
                    <td>{{ $param->status ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('qc-parameters.edit', $param) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('qc-parameters.destroy', $param) }}" method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this parameter?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        let callsTable;
        callsTable = $('#qc_param').DataTable({
            "searching": true,
            "paging": true,
            "lengthChange": false,
            "ordering": false,
            "dom": 'Bfrtip',
            "info": false,
        });
    });
</script>
@endpush
