<div class="dialer_content_box">
    <div class="dialer_header">
        <ul class="nav nav-tabs border-0" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                 <x-time-entry.tab-pane.nav-tab-head active="active" id="dial-tab" label="Dial" aria_selected="true" bstarget="#dial" />
            </li>
            <li class="nav-item" role="presentation">
                <x-time-entry.tab-pane.nav-tab-head  active="add_btn" id="notes-tab" label="Notes" aria_selected="false" bstarget="#notes_tab" />
            </li>
            <li class="nav-item" role="presentation">
                 <x-time-entry.tab-pane.nav-tab-head  active="" id="check-list-tab" label="Check List" aria_selected="false" bstarget="#check_list" />
            </li>
            <li class="nav-item" role="presentation">
                <x-time-entry.tab-pane.nav-tab-head  active="" id="call-list-tab" label="Call List" aria_selected="false" bstarget="#call_list_tab" />
            </li>
            <!-- <li class="nav-item" role="presentation">
                <x-time-entry.tab-pane.nav-tab-head  active="add_btn" id="contact-tab" label="Contact" aria_selected="false" bstarget="#task_contact" />
            </li> -->

            <li class="nav-item" role="presentation">
                <x-time-entry.tab-pane.nav-tab-head  active="add_btn" id="script-tab" label="Script" aria_selected="false" bstarget="#task_script" />
            </li>
        </ul>
        <i class="bi bi-plus-circle d-none"></i>
    </div>
    <div class="dialer_content">
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active mx-4 my-3 py-3" id="dial" role="tabpanel" aria-labelledby="home-tab">
                <x-time-entry.tab-pane.dial />
            </div>

            <div class="tab-pane fade bg-transparent" id="notes_tab" role="tabpanel" aria-labelledby="profile-tab">
                <x-time-entry.tab-pane.notes-tab />
            </div>
            <div class="tab-pane fade" id="check_list" role="tabpanel" aria-labelledby="contact-tab">
                <x-time-entry.tab-pane.check-list />
            </div>
            <div class="tab-pane fade bg-transparent" id="call_list_tab" role="tabpanel" aria-labelledby="contact-tab">
            {{--  <x-time-entry.tab-pane.call-list /> --}}
            </div>
            <div class="tab-pane fade bg-transparent" id="task_contact" role="tabpanel" aria-labelledby="contact-tab">
                <x-time-entry.tab-pane.contact />
            </div>
            <div class="tab-pane fade bg-transparent" id="task_script" role="tabpanel" aria-labelledby="script-tab">
                <x-time-entry.tab-pane.script-tab />
            </div>
         
        </div>
    </div>
</div>