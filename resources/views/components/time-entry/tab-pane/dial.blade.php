<div class="dialer_screen">
    <input type="text" class="number_box" placeholder="Add Number" />
    <div class="error_msg d-none text-danger text-center">Please enter a valid number</div>
    <div class="dialer_row input_numners">
        <a class="dialer_btn single_btn" href="#" data-value="1">
            <div class="number">1</div>
        </a>
        <a class="dialer_btn" href="#" data-value="2">
            <div class="number">2</div>
            <div class="text">ABC</div>
        </a>
        <a class="dialer_btn" href="#" data-value="3">
            <div class="number">3</div>
            <div class="text">DEF</div>
        </a>
    </div>
    <div class="dialer_row input_numners">
        <a class="dialer_btn" href="#" data-value="4">
            <div class="number">4</div>
            <div class="text">GHI</div>
        </a>
        <a class="dialer_btn" href="#" data-value="5">
            <div class="number">5</div>
            <div class="text">JKL</div>
        </a>
        <a class="dialer_btn" href="#" data-value="6">
            <div class="number">6</div>
            <div class="text">MNO</div>
        </a>
    </div>
    <div class="dialer_row input_numners">
        <a class="dialer_btn" href="#" data-value="7">
            <div class="number">7</div>
            <div class="text">PQRS</div>
        </a>
        <a class="dialer_btn" href="#" data-value="8">
            <div class="number">8</div>
            <div class="text">TUV</div>
        </a>
        <a class="dialer_btn" href="#" data-value="9">
            <div class="number">9</div>
            <div class="text">WXYZ</div>
        </a>
    </div>
    <div class="dialer_row input_numners">
        <a class="dialer_btn single_btn" href="#" data-value="*">
            <div class="number">*</div>
        </a>
        <a class="dialer_btn" href="#" data-value="0">
            <div class="number">0</div>
            <div class="text">+</div>
        </a>
        <a class="dialer_btn single_btn" href="#" data-value="#">
            <div class="number">#</div>
        </a>
    </div>
    <div class="dialer_row">
        <a href="#" class="invisible">0</a>
        <a href="#" class="d-inline-flex call_btn">
            <x-call_icon />
        </a>
        <a href="#" class="bg-transparent d-inline-flex align-items-center align-center clear_number">
            <x-dialer_clear_icon />
        </a>
    </div>
</div>
<div class="call_screen d-none">
    <div class="call_btn_section pt-3">
        <div class="dialer_row d-block">
            <div class="call_number">987XXXXXXX</div>
            <div class="call_timer">00:00</div>
        </div>

        <div class="dialer_row">
            <a href="#" class="d-inline-flex1 call_mute bg-white1 border-0 disable" title="Mute Call">
                <i class="fas fa-microphone-slash" ></i>
            </a>
          <!--   <a href="#" class="d-inline-flex call_record bg-white border-0 disable active" title="Record Call">
                <i class="fas fa-record-vinyl" ></i>
            </a> -->
            <a href="#" class="call_end_btn px-3">
                <i class="bi bi-x-lg me-1"></i>End Call
            </a>
        </div>
    </div>
</div>


