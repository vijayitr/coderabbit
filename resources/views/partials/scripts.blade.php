<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{asset('js/script.js')}}"></script>
<script src="{{asset('js/custom_script.js')}}"></script>

<script type="text/javascript">

    $(document).ready(function () {
        const $emptyModal = $('#emptyModal');
        const $modalBody = $('#modalBody');
        const $emptyModalLabel = $('#emptyModalLabel');

        function resetEmptyModal() {
            $modalBody.html(`
                <div id="reportTable_processing" class="dataTables_processing card" role="status">
                    <div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
            `);
            $emptyModalLabel.html('Empty Modal');
            $emptyModal.find('.modal-footer .checkList_submit_btn, .modal-footer .qc_submit_btn, .modal-footer .global_qc_submit_btn').remove();
        }

        $emptyModal.on('hidden.bs.modal', function () {
            resetEmptyModal();
        });
    });


    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    @if (session('success'))
        toastr.success("{{ session('success') }}", 'Success');
    @endif

    @if (session('error'))
        toastr.error("{{ session('error') }}", 'Error');
    @endif

    @if (session('warning'))
        toastr.warning("{{ session('warning') }}", 'Warning');
    @endif

    function BSDatePicker() {
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            allowInput: false
        });
    }

    function BSSelect() {
        $("select.select2:not(.field-dependency)").select2({
            closeOnSelect: true,
            placeholder: function() {
                return $(this).data('placeholder') || "Select an option";
            },
            allowClear: true,
            tags: true
        });
    }
    BSSelect();

    $(document).ajaxComplete(function(event, jqXHR, ajaxOptions) {
        BSDatePicker();
        BSSelect();
    });

    document.addEventListener("DOMContentLoaded", function() {
        BSDatePicker();
    });
</script>