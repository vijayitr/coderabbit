<!-- Modal -->
<form id="idleModalForm" method="post" action="{{ route('timeEntry.userIdle') }}">
  @csrf
    <div class="modal fade" id="idleModal" tabindex="-1" aria-labelledby="idleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="idleModalLabel">Please let us know the reason for your inactivity to resume your productive time.</h1>
          </div>
          <div class="modal-body">
           <textarea name="idle_reason" class="form-control idle_reason" rows="5" placeholder="Please enter the reason." required></textarea>
          </div>
          <div class="modal-footer d-block border-0">
            <div class="btn-group" id="idleOptionsGroup">
                <input type="radio" class="btn-check" name="breakOption" value="lunchBreak" id="idleLunchBreak" autocomplete="off" required>
                <label class="btn btn break_option" for="idleLunchBreak">Lunch Break</label>

                <input type="radio" class="btn-check" name="breakOption" value="teaBreak" id="idleTeaBreak" autocomplete="off" required>
                <label class="btn btn break_option" for="idleTeaBreak">Tea Break</label>

                <input type="radio" class="btn-check" name="breakOption" value="meeting" id="idleMeeting" autocomplete="off" required>
                <label class="btn btn break_option" for="idleMeeting">Meeting</label>
            </div>

            <button type="submit" id="submitIdleForm" class="btn btn-primary btn-filled float-end">Save</button>
          </div>
        </div>
      </div>
    </div>
</form>

<script type="text/javascript">
 $(document).ready(function() {
    localStorage.setItem('isBreak', String({{$isBrake}}));

    let idleTimer;
    var temp_data = {_token: '{{ csrf_token() }}'};
    var ajax_data = temp_data;
    const idleTimeLimit = 180000;

    function showModal() {
        var isBreak = localStorage.getItem('isBreak');
        if (!isBreak) {
            idleStartTime();
            $('#idleModal').modal('show');
        }
    }

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            showModal();
        }, idleTimeLimit);
    }

    $(document).on('change', '.idle_reason, #idleOptionsGroup input', function() {
        const isRequired = !($('.idle_reason').val().trim() || $('#idleOptionsGroup input:checked').length);
        $('.idle_reason, #idleOptionsGroup input').attr('required', isRequired);
    });

    ['mousemove', 'keydown', 'click', 'scroll'].forEach(event => { // Add event listeners to detect user activity
      window.addEventListener(event, resetIdleTimer);
    });

    resetIdleTimer(); // Start the idle timer when the page loads

    $('#idleModal').modal({
        backdrop: 'static',
        keyboard: false,
    });

   $('#idleModalForm').on('submit', function (e) {
        e.preventDefault();
        ajax_data.reason = $('.idle_reason').val().trim();
        ajax_data.break_type = $('#idleOptionsGroup input:checked').val();
        if (ajax_data.reason || ajax_data.break_type) idleStartTime();
    });

    @if($isIdle)
        showModal();
    @endif
    function idleStartTime() {
         $.ajax({
            url: '{{ route("timeEntry.userIdle") }}',
            type: 'POST',
            data: ajax_data,
            success: function (response) {
                if (ajax_data.reason || ajax_data.break_type) {
                    $('#idleModal').modal('hide');
                    $('.idle_reason').val('');
                    $('#idleOptionsGroup input').prop('checked', false);
                }

                ajax_data = temp_data;
            },
            error: function (xhr) {
                // console.error(xhr.responseText);
            }
        });
    }
 });

</script>