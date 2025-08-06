<div class="p-3">
    @forelse($checklist as $value)
        <div class="form-check mb-2">
            <input class="form-check-input checklist_option" type="checkbox" name="checklist[]" value="{{$value->id}}" {{ in_array($value->id, $checkListIds) ? 'checked' : '' }}>
            <label class="form-check-label ps-2 text-dark" for="checkbox1">{{$value->item}}</label>
        </div>
    @empty
        <div class="alert alert-warning text-center p-2" role="alert"><i class="fa-solid fa-thumbs-down me-"></i> No Checklist Available.</div>
    @endforelse
</div>