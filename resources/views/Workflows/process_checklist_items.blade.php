<form id="ProcessItemForm" action="{{ route('workflows.storeChecklistItems', $id) }}" method="POST">
    @csrf
    <div class="form-group mb-4 process_item_box">

        <!-- Table structure for checklist items -->
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $key => $item)
                    <tr class="process-row">
                        <td>{{$key+1}}</td>
                        <td>
                            <input type="text" 
                                   name="process_item[{{ $item->id }}]" 
                                   value="{{ old('item.' . $loop->index, $item->item) }}" 
                                   class="form-control item_name" 
                                   placeholder="Item Name">
                        </td>
                        <td>
                            <button type="button" class="btn {{ $key == 0 ? 'btn-success add_process_item' : 'btn-danger remove_process_item' }}">
                                <i class="fa {{ $key == 0 ? 'fa-plus' : 'fa-minus' }}"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <!-- Show an empty row when there are no checklist items -->
                    <tr class="process-row">
                        <td>1</td>
                        <td>
                            <input type="text" 
                                   name="process_item[new][]" 
                                   class="form-control item_name" 
                                   placeholder="Item Name">
                        </td>
                        <td>
                            <button type="button" class="btn btn-success add_process_item">
                                <i class="fa fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>
