@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit QC Parameter</h3>
    <form action="{{ route('qc-parameters.update', $qc_parameter) }}" method="POST">
        @csrf @method('PUT')
        @include('qc_parameters.form', ['qc_parameter' => $qc_parameter])
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
