@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Role</h1>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="role_name">Role Name</label>
                <input type="text" name="name" id="role_name" class="form-control" required>
            </div>

            <div class="form-group mt-3">
                <label for="role_name">Position</label>
                <select class="form-control" name="position">
                    @for ($i = 1; $i <= $role_counts; $i++)
                        <option value="{{$i}}" {{$position >= $i ? 'disabled' : ''}}>{{$i}}</option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Create Role</button>
        </form>
    </div>
@endsection
