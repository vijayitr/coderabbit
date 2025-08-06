@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Add QC Parameter</h3>
    <form action="{{ route('qc-parameters.store') }}" method="POST">
        @csrf
        @include('qc_parameters.form')
        <button type="submit" class="btn btn-success">Create</button>
    </form>
</div>
@endsection
