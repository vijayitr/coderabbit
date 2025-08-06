@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add User</h1>
    <form action="{{ route('users.store') }}" method="POST" id="addUserForm">
        @csrf
        <div class="row">
            <div class="col-8">
                <div class="form-group mb-2">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                <div class="form-group mb-2">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <!-- Assign Roles -->
                <div class="form-group mb-2">
                    <label for="roles">Roles:</label>
                    <select name="roles[]" id="roles" class="form-control select2" multiple required>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group mb-2">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <div class="form-group mb-2">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add User</button>
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('addUserForm').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        // Password Validation
        if (password.length < 8) {
            toastr.warning('Password must be at least 8 characters long.', 'Warning');
            event.preventDefault();
            return;
        }

        if (!/[A-Z]/.test(password)) {
            toastr.warning('Password must contain at least one uppercase letter.', 'Warning');
            event.preventDefault();
            return;
        }

        if (!/[a-z]/.test(password)) {
            toastr.warning('Password must contain at least one lowercase letter.', 'Warning');
            event.preventDefault();
            return;
        }

        if (!/[0-9]/.test(password)) {
            toastr.warning('Password must contain at least one number.', 'Warning');
            event.preventDefault();
            return;
        }

        if (!/[!@#$%^&*]/.test(password)) {
            toastr.warning('Password must contain at least one special character (!@#$%^&*).', 'Warning');
            event.preventDefault();
            return;
        }

        if (password !== confirmPassword) {
            toastr.warning('Passwords do not match.', 'Warning');
            event.preventDefault();
        }
    });
</script>
@endsection
