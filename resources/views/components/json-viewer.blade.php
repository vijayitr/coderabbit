<link href="{{asset('css/json_viewer.css')}}" rel="stylesheet">
<div class="modal fade loading" id="jsonViewerModalLabel" tabindex="-1" aria-labelledby="jsonViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="max-height: 80vh; height: 80vh;">
            <div class="modal-header border-0">
                <h1 class="modal-title fs-5 d-flex align-items-center" id="jsonViewerModalLabel">User Activity History</h1>
                <i class="fas fa-close float-end cursor-pointer" data-bs-dismiss="modal"></i>
            </div>
            <div class="modal-body overflow-auto border border-secondary-subtle" id="modalBody">
                <div class="json_viewer"></div>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('js/json_viewer.js')}}"></script>