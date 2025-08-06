@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Users</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Manager ID</th>
                <th>Name</th>
                <th>Email Address</th>
                <th>Last Login</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>
                        @if($user->logo)
                            <img src="{{ asset('storage/' . $user->logo) }}" alt="Logo" width="50" height="50">
                        @else
                            <span class="bi bi-user"></span>
                        @endif
                        {{ str_pad($user->id, 5, '0', STR_PAD_LEFT) ?? 'N/A' }}
                    </td> 
                    <td>{{ $user->name }}</td>
                    <td><a href="mailto:{{ $user->email }}" >{{ $user->email }}</a></td>
                    <td>{{ $user->last_login ?? 'Never' }}</td>
                    <td>{{ $user->role->name ?? 'No Role' }}</td>
                    <td>{{ $user->status ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
