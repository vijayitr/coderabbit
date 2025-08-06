@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Role: <span class="text-capitalize">{{ $role->name }}</span></h3>

        <form action="{{ route('roles.updatePermissions', $role->id) }}" method="POST">
            @csrf
            @method('POST')

            <div class="form-group">
                <label for="role_name">Role Name</label>
                <input type="text" name="name" id="role_name" class="form-control" value="{{$role->name}}" required>
            </div>

            <div class="form-group mt-3">
                <label for="role_name">Position</label>
                <select class="form-control" name="position">
                    @for ($i = 1; $i <= $role_counts; $i++)
                        <option value="{{ $i }}" {{ $i == $role->position ? 'selected' : '' }}  {{$position >= $i ? 'disabled' : ''}}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="form-group mt-3">
                <label>Permissions</label>
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Group Name</th>
                            <th>Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissionGroups as $groupName => $groupPermissions)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $groupName)) }}</td>
                                <td class="align-middle">
                                     @foreach ($groupPermissions as $permission)
                                        <div class="d-inline-flex align-items-center me-2">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" class="form-check-input me-2 mt-0" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0 text-capitalize">{{ formatPermissionName($permission->name) }}</label>
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary mt-3">
                    <i class="bi bi-save fs-5 me-2"></i>
                    <span>Update Role</span>
                </button>
            </div>
        </form>
    </div>
@endsection
