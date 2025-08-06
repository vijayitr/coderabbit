@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Import Task</h4>
    <form action="{{ route('userAllocation.importTaskList') }}" id="importAssignedTaskList" method="POST" enctype="multipart/form-data">
        @csrf
        <x-drop-area accept=".xls,.xlsx" />
        <button type="submit" class="btn btn-primary"> <i class="bi bi-upload me-1"></i> Import</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Attach a submit event handler to the form using jQuery
    $('#importAssignedTaskList').on('submit', function(e) {
        const fileInput = $('#file-input'); 
        if (fileInput[0].files.length === 0) {
            e.preventDefault();
            toastr.warning('Please select a file to upload.', 'Warning');
        }
    });
</script>
@endpush
