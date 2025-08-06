<label class="mb-1 fw-normal">Clients</label>
<div class="form-group mb-2">
    <select class="clientDropdown w-100" name="clients[]" multiple="multiple">
        @foreach($clients as $client)
                <option value="{{$client->id}}" {{$client->assignedTo->where('user_id', $user_id)->isNotEmpty() ? 'selected' : ''}} >{{$client->client_name}}</option>
        @endforeach
    </select>
</div>