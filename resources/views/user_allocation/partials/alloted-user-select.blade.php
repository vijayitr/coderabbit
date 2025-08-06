<form action="{{ route('userAllocation.assignParent') }}" id="allotedUsersForm" method="POST">
    @csrf
    <input type="hidden" name="parent" value={{$parent}}>
    <div class="form-group mb-3">
        <label for="select" class="form-label">Select Users</label>
        <select class="user_hierarchy form-select" name="users[]" multiple>
            @foreach( $users as $user )
            <option value="{{$user->id}}" {{!empty($selected) && in_array($user->id, $selected) ? 'selected' : ''}} >{{$user->name}} ( {{getRoleNames($user)}} )</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-outline-primary">Save</button>
</form>