<!-- Modal -->
<form id="PauseTask" method="post" action="{{ route('timeEntry.startBreak') }}">
  @csrf
    <div class="modal fade" id="BreakModal" tabindex="-1" aria-labelledby="BreakModalModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h1 class="modal-title fs-5" id="BreakModalModalLabel">Please enter the reason for pausing the task</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="process_id"/>
            <input type="hidden" name="assignment_id"/>
           <textarea name="pause_reason" class="form-control pause_reason" rows="5" placeholder="Please enter the reason for pausing the task" required></textarea>
          </div>
          <div class="modal-footer d-block border-0">
            <div class="btn-group" id="pauseOptionsGroup">
                <input type="radio" class="btn-check" name="breakOption" value="lunchBreak" id="lunchBreak" autocomplete="off" required>
                <label class="btn btn break_option" for="lunchBreak">Lunch Break</label>

                <input type="radio" class="btn-check" name="breakOption" value="teaBreak" id="teaBreak" autocomplete="off" required>
                <label class="btn btn break_option" for="teaBreak">Tea Break</label>

                <input type="radio" class="btn-check" name="breakOption" value="meeting" id="meeting" autocomplete="off" required>
                <label class="btn btn break_option" for="meeting">Meeting</label>
            </div>

            <button type="submit" class="btn btn-primary btn-filled float-end">Save</button>
            <button type="button" class="btn btn-outlined float-end" data-bs-dismiss="modal">Discard</button>
          </div>
        </div>
      </div>
    </div>
</form>