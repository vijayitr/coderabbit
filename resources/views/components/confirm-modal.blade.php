<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body" id="confirmModalBody">
                Are you sure you want to proceed with this action?
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    const confirmModal = document.getElementById('confirmModal');
    const confirmModalBody = document.getElementById('confirmModalBody');

    // Function to reset modal
    function resetConfirmModal() {
        confirmModalBody.innerHTML = `Are you sure you want to proceed with this action?`;
    }

    // Event listener for hidden.bs.modal
    confirmModal.addEventListener('hidden.bs.modal', resetConfirmModal);
</script>