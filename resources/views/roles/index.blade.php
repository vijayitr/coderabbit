@extends('layouts.app')

@section('content')
  <div class="container mt-3" id="">

    <!-- Table -->
    <div class="table-responsive">
        @if ($authUser->can('role.create'))
        <div class="text-end">
            <a href="{{ route('roles.create') }}" class="btn btn-dark py-0">
                <i class="bi bi-plus fs-5"></i>
                <span>Add New Role</span>
            </a>
        </div>
        @endif

         <table class="table table-bordered table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Role Name</th>
                            <th>Permissions</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="">
                                <td>{{ $loop->iteration }}</td>
                                <td class="text-capitalize fw-bold">{{ $role->name }}</td>
                                <td>
                                    @php
                                    $groupedPermissions = $role->permissions->groupBy('group_name');
                                    @endphp
                                        @foreach ($groupedPermissions as $groupName => $permissions)
                                            @foreach($permissions as $value)
                                                <span class="common-badge me-2">{{formatPermissionName($value->name)}}</span>
                                            @endforeach
                                        @endforeach
                                </td>
                                <td class="text-capitalize fw-bold">{{ $role->position }}</td>
                                <td class="text-end" width="15%">
                                    @if($role->id != 1)
                                    <a href="{{ route('roles.assignPermissions', ['role' => $role->id]) }}" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('roles.delete', $role->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this role?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
    </div>
</div>
@endsection
