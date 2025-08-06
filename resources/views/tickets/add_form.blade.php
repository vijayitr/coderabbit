@extends('layouts.app')

@section('content')
<div class="container">
    <h3>{{ empty($id) ? 'Add' : 'Edit' }} Form</h3>
    @php
        $selectedRoles = json_decode(old('roles', $form->roles ?? '[]'), true);
    @endphp
    <form action="{{ empty($id) ? route('tickets.formStore') : route('tickets.editForm',$id) }}" method="POST" id="addUserForm">
        @csrf
        <div class="row">
            <div class="col-8">
                <div class="form-group mb-2">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{@$form->name}}" required>
                </div>
                <div class="form-group mb-2">
                    <label>Form Title</label>
                    <input type="text" name="title" class="form-control" value="{{@$form->form_details}}" required>
                </div>

                <div class="form-group mb-2">
                    <label for="roles">Roles:</label>
                    <select name="roles[]" id="roles" class="form-control select2" multiple required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ in_array($role->id, $selectedRoles) ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-2">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option {{isset($form->status) && $form->status == '1' ? 'selected' : ''}} value="active">Active</option>
                        <option {{isset($form->status) && $form->status == '0' ? 'selected' : ''}} value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">{{ empty($id) ? 'Add' : 'Update' }} Form</button>
            </div>
        </div>
    </form>
</div>
@endsection
