@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Client</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="clientName">Client Name</label>
            <input type="text" class="form-control" id="clientName" name="clientName" value="{{ $client->clientName }}">
        </div>
        <div class="form-group">
            <label for="logo">Logo</label>
            <input type="file" class="form-control" id="logo" name="logo">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection