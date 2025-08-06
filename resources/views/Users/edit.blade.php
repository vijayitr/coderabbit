@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit User</h1>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-8">
                <div class="form-group mb-2">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ $user->name }}" required>
                </div>
                <div class="form-group mb-2">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ $user->email }}" required>
                </div>

                <!-- Assign Roles -->
                <div class="form-group mb-2">
                    <label for="roles">Roles:</label>
                    <select name="roles[]" id="roles" class="form-control select2" multiple>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required data-value="{{$user->status}}">
                        <option value="1" {{ $user->status == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $user->status == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </div>
    </form>
</div>
@endsection
