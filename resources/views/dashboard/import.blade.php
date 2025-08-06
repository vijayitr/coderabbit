@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Import Testing Data</h4>
    <form action="{{ route('importTestingData') }}" id="importWorkFlow" method="POST" enctype="multipart/form-data">
        @csrf
        <x-drop-area accept=".xls,.xlsx" />
        <button type="submit" class="btn btn-primary"> <i class="bi bi-upload me-1"></i> Import</button>
        <select class="form-control w-25 d-inline choose_client">
            <option value="">Choose Client For Sample File</option>
            @foreach($clients as $name => $id)
            <option value="{{$id}}" href="{{url('/test-workflow-data/'.$id)}}">{{$name}}</option>
            @endforeach
        </select>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Attach a submit event handler to the form using jQuery
    $(document).on('change', '.choose_client', function() {
        var client = $(this).val();
        var url = $(".choose_client option:selected").attr("href");
        if (client != '') {
            window.open(url, "_blank");
        }
    })
</script>
@endpush
