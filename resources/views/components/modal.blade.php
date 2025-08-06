<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! $body !!}
            </div>
            <div class="modal-footer">
                @foreach ($buttons as $button)
                <button type="button" data-bs-dismiss="modal" class="btn {{ $button['class'] }}" 
                    @if(isset($button['id'])) id="{{ $button['id'] }}" @endif
                    @if(isset($button['action'])) onclick="{{ $button['action'] }}" @endif>
                        {{ $button['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
