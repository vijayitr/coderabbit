// Time Entry Page
//const mobileRegex = /^[6-9]\d{9}$/;

const mobileRegex = /^(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*\d{3}\s*\)|\d{3})[\s.-]?\d{3}[\s.-]?\d{4}$/;
/*var  lineDetails ={
        "id": "e0c0f517-27e8-43bc-ae46-37a2bd843f48",
        "name": "Raghu Ramdas",
        "number": "1111",
        "organization": {
            "id": "b882ae78-76da-4c53-b905-9f50c4e44b8f",
            "name": "Turn Around International"
        },
        "accountKey": "8420499155962678798",
        "primary": true,
        "region": "US"
}; */



$(document).on('click', '.select-btn', function () {
    const menu = $(this).closest('.select-menu');
    $('.select-menu').not(menu).removeClass('active');
    if ($(this).closest('.select-menu').find('input[name="activity"]').length && !$(this).closest('.select-menu').find('ul.option_list li').length) {
        toastr.warning("Please Select Client First.", 'Warning');
    } else {
        menu.toggleClass('active');
    }
});

$(document).on('click', '.list_option', function () {
    const menu = $(this).closest('.select-menu');
    menu.find('.list_option').removeClass('active-option');
    $(this).addClass('active-option');
    const value = $(this).attr('value');
    const assignment_id = $(this).data('assignment_id');
    const text = $(this).find('.option-text').text();
    if (assignment_id != undefined) {
        menu.find('input[type="hidden"]').attr('data-assignment_id',assignment_id);
    }
    menu.find('input[type="hidden"]').val(value).trigger('change');
    menu.find('.sBtn-text').text(text);
    menu.removeClass('active');
    ActiveWorkBtn();
});

$(document).on('change', '.select-menu input', function () {
    ActiveWorkBtn();
});

function ActiveWorkBtn() {
    // Show Start Button
    var client = $('input[name="client"]').val();
    var activity = $('input[name="activity"]').val();
    if (client !== '' && activity !== '' && $('#TaskForm').hasClass('d-none')) {
        $('.Workflow_start_btn').removeClass('d-none');
    } else {
         $('.Workflow_start_btn').addClass('d-none');
    }
}

$(document).on('click', function (event) {
    if (!$(event.target).closest('.select-menu').length) {
        $('.select-menu').removeClass('active');
    }
});
$(document).on('click', '.create-task-button', function () { 
    $('.create_task_box').removeClass('d-none').fadeIn(500);
    $('.filter-bar-buttons').removeClass('d-none');
    $('tr.create-task-button').remove();
    $('.create_task_box').removeClass('paused');
    $('.Workflow_start_btn, .Workflow_call_btn, #TaskForm').addClass('d-none').fadeOut(500);
    var rowToMove = $('.filter-bar').closest('tr');
    rowToMove.prependTo("table#TimeEntryTable tbody");
    $('.taskTr').removeClass('d-none').fadeIn(500);

    $('.select-menu').each(function() {
        $(this).find('.sBtn-text').text('Select');
        $(this).find('input').val('');
        $(this).find('.list_option').removeClass('active-option');
    })
    $('#TaskForm').trigger('reset');
});

var lastClickedButton = null;
if ($('#TimeEntry .pause_task').length) {
    $(document).on('click', '.pause_task', function() {
        var assignment_id = $(this).closest('tr').attr('data-assignment_id');
        if ($(this).closest('form').length) {
            assignment_id = $(this).closest('form').find('input[name="assignment_id"]').val();
        }
        $('#BreakModal').find('input[name="process_id"]').val($(this).data('id'));
        $('#BreakModal').find('input[name="assignment_id"]').val(assignment_id);
        $('#BreakModal').modal('show');
    });

    $(document).on('click','.pause_task', function(){
        lastClickedButton = $(this);
    });
}

// if ($('#TimeEntry #PauseTask').length) {
//     $(document).on('submit', '#PauseTask', function(e) {
//         e.preventDefault();

//         lastClickedButton.closest('tr').addClass('paused');
//         $('#BreakModal').modal('hide');
//     });
// }
var lastEditButton = null;
$(document).on('click', '.edit_task', function() {
    var process_id = $(this).closest('tr').attr('data-processid');
    var assignment_id = $(this).closest('tr').attr('data-assignment_id');
    console.log('process_id = ',process_id, 'assignment_id = ',assignment_id);
    $('input[name="assignment_id"]').val(assignment_id);
    var isView = $(this).hasClass('view_task');
    $('.pause_task').attr('data-id',process_id);
    $('.create_task_box').toggleClass('paused', $(this).closest('tr').hasClass('paused'));
    $('.create_task_box').attr('data-processid',process_id);
    const encodedData = $(this).attr('data');
    const decodedHtml = atob(encodedData);

    var taskDetails = JSON.parse(atob($(this).data('task-details')));

    lastEditButton = $(this);
    $('.edit_task').each(function() {
        if ($(this).closest('tr').hasClass('d-none')) {
            $(this).closest('tr').removeClass('d-none').fadeIn(500);
        }
    });
    lastEditButton.closest('tr').addClass('d-none').fadeOut(500);
    var rowToMove = $('.filter-bar').closest('tr');
    rowToMove.insertAfter($(this).closest('tr'));
    $('#TaskForm').attr('mode','edit');
    $('.Workflow_card .Workflow_field_box').html(decodedHtml);
    if (isView) {
        $('.Workflow_card .Workflow_field_box').find('input, select').each(function () {
            if ($(this).is('input')) {
                $(this).prop('disabled', true); // For input fields
            } else if ($(this).is('select')) {
                $(this).prop('disabled', true); // For select elements
            }
        });
        $('.filter-bar-buttons').addClass('d-none');
    } else {
        $('.filter-bar-buttons').removeClass('d-none');
        resumeTask(process_id, assignment_id);
    }
    localStorage.setItem('isEdit', true);
    var isQcAgent = $('.qc_agent').length > 0;
    var status = isQcAgent ? false : taskDetails.process_status === 'completed';

    $('.Workflow_task_end_btn').attr('disabled', status);
    $('.pause_task').attr('disabled', status);
    $('#TaskForm').find('[name="status"].task_status').val(taskDetails.process_status);
    var client_option = $('.select-menu input[name="client"]').closest('.select-menu').find('.option_list .list_option[value="'+taskDetails.client_id+'"]');
    client_option.attr('process_id',taskDetails.process_id);
    client_option.click();
    // $('.select-menu input[name="client"]').
    var workflow_id = $('.select-menu input[name="activity"]').closest('.select-menu').find('.option_list .list_option[value="'+process_id+'"]').attr('data-workflow_id');
    $('.select-menu input[name="workflow"]').closest('.select-menu').find('.option_list .list_option[value="'+workflow_id+'"]').click();
    var activity_option = $('.select-menu input[name="activity"]').closest('.select-menu').find('.option_list .list_option[value="'+process_id+'"]').click();
    // activity_option.click();
    $('.create_task_box, #TaskForm, .Workflow_call_btn').removeClass('d-none').fadeIn(500);
    $('.Workflow_start_btn').addClass('d-none');
});

function resumeTask(process_id = '', assignment_id) {
    if (process_id != '') {
        var url = $('input[name="resume_url"]').val();
         console.log('assignment_id', assignment_id);
        $.ajax({
            url: url,
            type: 'GET',
            data: { id: process_id, assignment_id:assignment_id, _token: '{{ csrf_token() }}' },
            success: function (response) {
                
            },
            error: function (xhr) {
                // console.error(xhr.responseText);
            }
        });
    }
}

// var htmlContent = '<tr class="d-nonef"><td class="align-middle py-3" scope="col"><input type="checkbox" class="form-check-input mt-0 me-3"><span class="patient_name">[patient_name]</span></td><td class="align-middle py-3 status" scope="col"><span class="status_badge">[schedule]</span></td><td class="align-middle py-3 activity" scope="col">[activity]</td><td class="align-middle py-3 comment" scope="col">[comment]</td><td class="align-middle py-3 client" scope="col">[client]</td><td class="align-middle py-3 action text-end" scope="col"><button type="button" class="edit_task control" data="[data]" ><img src="/images/track-time/pencil.png" alt="Edit"></button><button type="button" class="pause_task control" data-bs-toggle="modal" data-bs-target="#BreakModal"><img src="/images/track-time/pause.png" alt="Pause"></button><button type="button" class="play_task control"><img src="/images/track-time/play.png" alt="Pause"></button><button type="button" class="btn btn-outline-primary px-3 py-1 Workflow_end_btn btn-outlined">End Task</button></td></tr>';


if ($('#TimeEntry .Workflow_saveTask_btn').length) {
    $(document).on('submit', '#TaskForm', function(e) {
        var status = $(this).find('[name="status"].task_status');
        status.css("border", "");
        if (status.val().trim() === "") {
            e.preventDefault();
            status.css("border", "1px solid red").focus();
            toastr.warning("Please Select Task Status.", 'Warning');
        }
        // var mode = $(this).attr('mode');
        // // Extract form data
        // const formData = $('#TaskForm')
        //     .serializeArray()
        //     .reduce((obj, item) => {
        //         obj[item.name] = item.value || '';
        //         return obj;
        //     }, {});

        // // Extract individual fields
        // var patient_name = $('.filter-bar').find('input[name="patient_name"]').val();
        // var status = $('.filter-bar').find('input[name="status"]').val();
        // var activity = $('.filter-bar').find('input[name="activity"]').val();
        // var comment = 'Humphrey  & Associates INC';
        // var client = $('.filter-bar').find('input[name="client"]').val();

        // // Clone and populate the template
        // let rowContent = htmlContent
        //     .replace(/\[patient_name\]/g, patient_name)
        //     .replace(/\[schedule\]/g, status)
        //     .replace(/\[activity\]/g, activity)
        //     .replace(/\[comment\]/g, comment)
        //     .replace(/\[data\]/g, btoa(JSON.stringify(formData)))
        //     .replace(/\[client\]/g, client);

        // // Add the new row and manage visibility
        // const $currentRow = $(this).closest('tr');
        // if (mode == 'add') {
        //     $currentRow.before(rowContent);
        // } else {
        //     var row = lastEditButton.closest('tr');
        //     row.find('.patient_name').text(patient_name);
        //     row.find('.status_badge').text(status);
        //     row.find('.activity').text(activity);
        //     row.find('.comment').text(comment);
        //     row.find('.client').text(client);
        //     row.find('.edit_task').attr('data',btoa(JSON.stringify(formData)));
        //     row.removeClass('d-none').fadeIn(500);
        // }
        // const index = $currentRow.index();
        // $currentRow.fadeOut(500).addClass('d-none');
        // $currentRow.prev().fadeIn(500);
        // $('#TaskForm').attr('mode', 'add');
    });
}

$(document).on('change', '.select-menu input[name="activity"]', function() {
    $('#TaskForm input[name="process_id"]').val($(this).val());
});
$(document).on('change', '.select-menu input[name="client"]', function() {
    $('#TaskForm input[name="client_id"]').val($(this).val());
});

$(document).on('click', '.Workflow_call_btn', function() {
    $('.filter-bar-row').toggleClass('col-12').toggleClass('col-8');
    $('.filter-bar-buttons').toggleClass('justify-content-end');
    $('.dialer_box').toggleClass('d-none');
    $('.Workflow_card .col-md-3, .Workflow_card .col-md-4').toggleClass('col-md-4').toggleClass('col-md-3');
    $('.call_screen').height($('.dialer_screen').height());
    $('#notes_tab > div').height($('.dialer_screen').height());
    $('.number_box').focus();
});

$(document).on('click', '.dialer_header .nav-item', function() {
    if ($(this).find('.nav-link').attr('id') == 'dial-tab') {
        $('.filter-bar-row').removeClass('col-7').addClass('col-8');
        $('.dialer_box').removeClass('col-5').addClass('col-4');
    } else {
        $('.filter-bar-row').removeClass('col-8').addClass('col-7');
        $('.dialer_box').removeClass('col-4').addClass('col-5');
    }

    if ($(this).find('.nav-link').hasClass('add_btn')) {
        $('.dialer_header i.bi-plus-circle').removeClass('d-none').fadeIn(500);
    } else {
        $('.dialer_header i.bi-plus-circle').addClass('d-none').fadeOut(500);
    }
});

$(document).on('click', '.dialer_btn', function(e) {
    e.preventDefault();
    const value = $(this).data('value');
    const $numberBox = $('.number_box');
    $numberBox.val($numberBox.val() + value);
});

$(document).on('click', '.clear_number', function(e) {
    e.preventDefault();
    const $numberBox = $('.number_box');
    $numberBox.val($numberBox.val().slice(0, -1));
});


function getClientInformation() {
    return {
        deviceId: localStorage.getItem('deviceId') || generateDeviceId(),
        appId: "LaravelWebRTC", // Your App Name
        appVersion: "1.0.0", // Your App Version
        platform:   "INTEGRATOR" //navigator.userAgent // Browser & OS Info
    };
}

function generateDeviceId() {
    let deviceId = 'device-' + Math.random().toString(36).substr(2, 16);
    localStorage.setItem('deviceId', deviceId); // Store in browser
    return deviceId;
}

//console.log('getClientInformation',getClientInformation());


async function generateSDP(callback) {
  
    //callback(offer.sdp);

    // const answer = await peerConnection.createAnswer();
    // await peerConnection.setLocalDescription(answer);
    // console.log("Answer SDP:", answer.sdp);
    

    // peerConnection.oniceconnectionstatechange = (event) => {
    //     console.log("ICE Connection State:", peerConnection.iceConnectionState);
    // };
    
}


var lineDetails = null;


async function getLine() {
    if (!localStorage.getItem('lineDetails')) {
        let requestData = JSON.stringify({
            endpoint: "/users/v1/lines",
            method: "GET"
        });

        return new Promise((resolve, reject) => {
            makeCall(requestData, function (requestData, responseData) {
                console.log("User Line Details:", responseData);
                if (responseData.items && responseData.items.length > 0) {
                    localStorage.setItem('lineDetails', JSON.stringify(responseData.items));
                    lineDetails = responseData.items[0];
                    resolve(lineDetails); // Resolve the promise with line details
                } else {
                    console.error("No lines found for the user.");
                    reject("No lines found for the user."); // Reject the promise
                }
            });
        });
    } else {
        lineDetails = JSON.parse(localStorage.getItem('lineDetails'));
        lineDetails = lineDetails[0];
        console.log("Line Details:", lineDetails);
        return lineDetails; // Return the cached line details
    }
}



function makeCall(requestData, callback) {
    fetch('/goto/request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: requestData
    })
    .then(response => {
        // ✅ Check Content-Type before parsing
        let contentType = response.headers.get("content-type");
    
        if (contentType && contentType.includes("application/json")) {
            return response.json(); // ✅ Parse JSON if API returns JSON
        } else {
            return response.blob(); // ✅ Return binary (WAV file) if not JSON
        }
    })
    .then(data => {
        if (data.auth_url) {
            // ✅ Open GoTo Login in new tab

            console.log("Authentication URL:", data.auth_url);
            let authWindow = window.open(data.auth_url, '_blank');

            // ✅ Check every second if OAuth tab is closed, then retry API request
            let checkAuth = setInterval(() => {
                if (authWindow.closed) {
                    clearInterval(checkAuth);
                    makeCall(requestData, callback); // Try again after authentication
                }
            }, 1000);
        } else if (data.error) {

            //toastr.warning(data.error, 'Warning');
            console.log("Error: " + data.error);

            if (callback) callback(requestData, data);

            
            
        } else {

            console.log("API Response:", data);
            if (callback) callback(requestData, data);
            console.log("Call initiated successfully!");
        }
    })
    .catch(error => {
        console.error('Call error:', error);
    });
};


var timerInterval = null;
var callstatusTimeout = null;
var startTime = null;
var legId = null;
var callId = null;
var conversationSpaceId = null;
var callLogId=null;

let peerConnection = null; 
let stream = null; 
let remoteAudio = null;





$(document).ready(function () {
    $(document).on("click", ".load-audio", function () {


        let button = $(this);
        let recordingId = button.data("recording-id");
        let audioElement = button.parents('.accordion-item').find(`audio[data-recording-id="${recordingId}"]`);
        let loader = button.parents('.accordion-item').find('.loader');

        if (audioElement.attr("src")) { return ; }

        button.parents('.accordion-item').find('.recording-container').removeClass('d-none');
        loader.show();


        let requestData = JSON.stringify({
            endpoint: "/recording/v1/recordings/"+recordingId+"/content",  
            method: "GET"
        });

        makeCall(requestData, function(requestData, responseData) {

            let requestDataContent = JSON.stringify({
                endpoint: "/recording/v1/recordings/"+recordingId+"/content/"+responseData.token.token,
                method: "GET",
            });

            makeCall(requestDataContent, function(requestData, responseData) {
                console.log("Recording content:", responseData);

                let audioBlob = new Blob([responseData], { type: "audio/webm" });
                let audioUrl = URL.createObjectURL(audioBlob);
                audioElement.attr("src", audioUrl);
                audioElement[0].load();
                //audioElement[0].play();

                loader.hide();
                
            });
        });
    
        
    });

    $(document).on('click', '.load-recording-btn', function() {
        let parent = $('.score_recording');
        if (parent.length) {
            let audioElement = parent.find('audio');
            let recordingId = audioElement.data("recording-id");
            audioElement.removeClass('d-none');
            $(this).hide();
            GetRecording(recordingId, audioElement);
        }
    });

    $(document).on("click", ".play_call", function (e) {
        e.preventDefault();
        let recordingId = $(this).data("recording-id");
        $('#emptyModalLabel').html('Call Recording <i class="ms-2 fas fa-spinner fa-spin d-none call_recording_loading_icon"></i>');
        $('#emptyModal .modal-body').addClass('p-0');
        $('#emptyModal').modal('show');
        $('#emptyModal .modal-body').html('<div class="px-3"><div class="mt-2 recording-container"><audio class="w-100" controls="" data-recording-id="'+recordingId+'">Your browser does not support the audio element.</audio></div></div>');
        let button = $('#emptyModal'); 
        let audioElement = button.find(`audio[data-recording-id="${recordingId}"]`);
        GetRecording(recordingId, audioElement, true);
    });

    function GetRecording(recordingId, audioElement, play = false) {
        $('.call_recording_loading_icon').removeClass('d-none');
        let requestData = JSON.stringify({
            endpoint: "/recording/v1/recordings/"+recordingId+"/content",  
            method: "GET"
        });

        makeCall(requestData, function(requestData, responseData) {

            let requestDataContent = JSON.stringify({
                endpoint: "/recording/v1/recordings/"+recordingId+"/content/"+responseData.token.token,
                method: "GET",
            });

            makeCall(requestDataContent, function(requestData, responseData) {
                console.log("Recording content:", responseData);

                let audioBlob = new Blob([responseData], { type: "audio/webm" });
                let audioUrl = URL.createObjectURL(audioBlob);
                audioElement.attr("src", audioUrl);
                audioElement[0].load();
                if (play) {
                    audioElement[0].play();
                }
                $('.call_recording_loading_icon').addClass('d-none');
                
            });
        });
    }


    $(document).on("click",".view-summary-btn", function () {

        $("#callSummaryModal .modal-title").text("Call Summary");
        $("#modalCallSummary").text("Loading summary...");

        let summary = $(this).attr("data-summary");
        if (summary && summary != '') {
            $("#modalCallSummary").text(summary);
            return;
        }

        let conversationId = $(this).data("conversationid");
        let requestData = JSON.stringify({
            endpoint: "/call-events-report/v1/reports/"+conversationId,
            method: "GET"
        });

        let viewsummarybtn = $(this);

        makeCall(requestData, function(requestData, responseData) {
            console.log("Call Summary:", responseData);
            let summary = responseData.aiAnalysis ? responseData.aiAnalysis.summary : "";
            viewsummarybtn.attr("data-summary", summary);
            viewsummarybtn.parents('.accordion-item').data("log-id");
            let logId = viewsummarybtn.parents('.accordion-item').data("log-id");
            $("#modalCallSummary").text(summary || "No summary available");

            if(summary)
                updateCallLog('call_details', responseData,logId);


        })
    });

    $(document).on("click",".view-checklist-btn", function () {
        
        $("#callSummaryModal .modal-title").text("Call Checklist");
        let checklist = $(this).data("checklist");
        let checklistoption = $('.checklist_option');

        let checklistHtml = "<ul class='list-style-none'>";
        checklistoption.each(function() {
            let itemText = $(this).next().text();
            if (checklist && checklist.includes($(this).val())) {
                checklistHtml += "<li>✅ " + itemText + "</li>";
            }
            else {
                checklistHtml += "<li>❌ " + itemText + "</li>";
            }
        });
       /*  if (checklist && checklist.length > 0) {
            checklist.forEach(item => {
                let itemText = $('.checklist_option[value="'+item+'"] ').next().text();
                item = itemText || item;
                checklistHtml += "<li>" + item + "</li>";
            });
        } else {
            checklistHtml += "<li>No checklist available</li>";
        } */
        checklistHtml += "</ul>";
        $("#modalCallSummary").html(checklistHtml);


    });


    $(document).on("click",".view-notes-btn", function () {
        
        $("#callSummaryModal .modal-title").text("Call Notes");
        $("#modalCallSummary").text($(this).data("notes") || "No notes available");

    });


    $(document).on("click", ".direct-call-btn", function () {
        if($('.dialer_box').hasClass('d-none')){
            $('.Workflow_call_btn').click();
        }
        $('.number_box').val($(this).next().val());
    })

    
});



function saveCallLog() {

    if(!conversationSpaceId) return console.error("❌ No active call found!");
    
    let requestData = JSON.stringify({
        endpoint: "/call-events-report/v1/reports/"+conversationSpaceId,
        method: "GET"
    });

    conversationSpaceId = null;

    makeCall(requestData, function(requestData, responseData) {
        console.log("Call Log:", responseData);

        let notes = $('.notes_box').val();
        let checklist = $('#check_list [type="checkbox"]:checked').map(function() {return this.value;}).get();

        fetch("/goto/save-call-log", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            },
            body: JSON.stringify({
                assignment_id: $('[name="activity"]').data('assignment_id') || null, // ✅ Save assignment ID if available
                call_details: responseData,
                notes: notes,
                checklist: checklist
            })
        })
        .then(response => response.json())
        .then(data => {
            callLogId = data.data.id;
            console.log("✅ Call log saved successfully:", data);
        })
        .catch(error => console.error("❌ Error saving call log:", error));
    });

    
}




function updateTimer() {
    if (!startTime) return;
    let elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
    let minutes = Math.floor(elapsedSeconds / 60);
    let seconds = elapsedSeconds % 60;
    $('.call_timer').text(String(minutes).padStart(2, "0") + ":" + String(seconds).padStart(2, "0"));
}

function checkCallStatus() {

    let statusRequestData = JSON.stringify({
        endpoint: "/call-events/v1/conversation-spaces?lineId="+lineDetails.id,
        method: "GET"
    });

    makeCall(statusRequestData, (req, res) => {
        if (res.items && res.items.length > 0) {
            let latestCall = res.items[0]; // Get the most recent call
            let callStatus = latestCall.state.type; // Example: "ACTIVE", "ENDING"
            conversationSpaceId = latestCall.metadata.conversationSpaceId;

            console.log("conversationSpaceId:", conversationSpaceId);
            

            if (callStatus === "ACTIVE" && !startTime) {
                startTime = new Date(latestCall.state.timestamp).getTime();
                timerInterval = setInterval(updateTimer, 1000);
                $('.call_record,.call_mute').removeClass('disable');
            }

            if (callStatus === "ENDING") {
                callClean();
                console.log("Call ended!");
            }
            else {
                callstatusTimeout = setTimeout(checkCallStatus, 2000);
            }

            console.log("Call Status:", callStatus);
        } else {
            
            console.log("No active calls found.");

             callClean();
        }
    });
}

let elapsedTime = 0;

$(document).on('click', '.call_btn', async function(e) {
    e.preventDefault();
    const mobileNumber = $('.number_box').val();

    console.log(mobileNumber);

    $('.dialer_screen .error_msg').addClass('d-none').fadeOut(500);

    if (mobileRegex.test(mobileNumber)) {


       // Wait for getLine() to complete
            await getLine();

            if (!lineDetails) {
                console.error("Line details not available. Please try again.");
                return;
            }


       /*  let requestData = JSON.stringify({
            endpoint: "/calls/v2/calls",
            method: "POST", 
            data: {
                "dialString": mobileNumber,
                "from": {
                    "lineId": lineId
                },
                "autoAnswer": true
            }
        });

        makeCall(requestData, function(requestData, responseData) {
            console.log("Call initiated successfully!", responseData);
            checkCallStatus();

        }); */


        if (peerConnection) {
            peerConnection.close();
            peerConnection = null;
        }

        

        peerConnection = new RTCPeerConnection({
            iceServers: [{ urls: "stun:stun.l.google.com:19302" }] 
        });


        if (remoteAudio) {
            remoteAudio.remove();
        }


        remoteAudio = new Audio();
        remoteAudio.autoplay = true;
        document.body.appendChild(remoteAudio); // ✅ Attach to the DOM



        // ✅ Attach received media to the `<audio>` element
        peerConnection.ontrack = (event) => {
            console.log("🔊 Receiving audio track from remote peer:", event.streams);
            remoteAudio.srcObject = event.streams[0];  // Attach stream to audio element
        };

        peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log("New ICE Candidate:", event.candidate);
            } else {
                console.log("All ICE candidates received. Sending SDP now...");
                //callback(peerConnection.localDescription.sdp);  // ✅ Now send SDP after ICE candidates are gathered
            }
        };


        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }


        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        stream.getTracks().forEach(track => peerConnection.addTrack(track, stream));
    
        let offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);

        let cleanedNumber = mobileNumber.replace(/[\s()]/g, "");



        let requestData = JSON.stringify({
            endpoint: "/web-calls/v1/calls",    
            method: "POST", 
            data: {
                "deviceId": "device-7tdk2td04fa",
                "organizationId": lineDetails.organization.id,
                "extensionNumber": lineDetails.number,
                "dialString": cleanedNumber,
                "inCallChannelId": "Webhook.d1571ec0-ebd1-45d8-8638-fecbf65f4fcb",
                "sdp": offer.sdp
             }
        });
    
        makeCall(requestData, async function(requestData, responseData) {
            
            console.log("WebCall initiated successfully!", responseData);
            

            legId = responseData.legId;
            callId = responseData.id;
            
    
             // ✅ Set the SDP Answer from GoToConnect
             await peerConnection.setRemoteDescription(new RTCSessionDescription({
                type: "answer",
                sdp: responseData.sdp
            }));


            setTimeout(() => {
            checkCallStatus();
            }, 1000);


        });
        
        $('.call_screen,.top-call-controls').removeClass('d-none').fadeIn(500);
        $('.dialer_screen,.Workflow_call_btn').addClass('d-none').fadeOut(500);
        $('.call_number').html(mobileNumber);
        callLogId = null;
        $('.notes_box').val('');
        $('#check_list [type="checkbox"]').prop('checked', false);

        /* clearInterval(timerInterval);
        elapsedTime = 0;
        $('.call_timer').text(formatTime(elapsedTime));

        timerInterval = setInterval(function () {
            elapsedTime++;
            $('.call_timer').text(formatTime(elapsedTime));
        }, 1000); */

    } else {
      $('.dialer_screen .error_msg').removeClass('d-none').fadeIn(500);
    }
});

function callClean(){

    clearInterval(timerInterval);
    clearTimeout(callstatusTimeout);
    startTime = null;
    callId = null;
    legId = null;
    //conversationSpaceId = null;   
     

    $('.number_box').val('');
    $('.call_screen,.top-call-controls').addClass('d-none').fadeOut(500);
    $('.dialer_screen,.Workflow_call_btn').removeClass('d-none').fadeIn(500);

    $('.call_record,.call_mute').addClass('disable');
    $('.call_mute').removeClass('active');
    $('.call_record').addClass('active');
    $('.call_timer').text("00:00");
    
    updateTimer();
    if (peerConnection) {
        peerConnection.close();
        peerConnection = null;
    }

    if (stream) {
        stream.getTracks().forEach(track => track.stop()); // ✅ Stop all mic tracks
        stream = null;
    }

    if (remoteAudio) {
        remoteAudio.srcObject = null;
    }


    if(conversationSpaceId) setTimeout(function(){saveCallLog()},1000);
    
    console.log("Call Cleaned!");

    
}



function callEnd(){
   /*  let requestData = JSON.stringify({
        endpoint: "/call-control/v1/call-legs/"+legId+"/hangup",
        method: "POST", 
        data: {
            "accountKey": accountKey
        }
    }); */

    let requestData = JSON.stringify({
        endpoint: "/web-calls/v1/calls/"+callId,
        method: "DELETE"
    });

    makeCall(requestData, function(requestData, responseData) {
        console.log("Call Ended successfully!", responseData);

        callClean();

    });
}

function updateCallLog(fieldName,fieldValue,logid) {

    if(!fieldName || !fieldValue) return console.log("Please provide field name and value!");


    let bodyReq= {
        id: logid
    }

    bodyReq[fieldName] = fieldValue;
    
    
    fetch("/goto/update-call-log", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        },
        body: JSON.stringify(bodyReq)
    })
    .then(response => response.json())
    .then(data => {
        toastr.success(data.message, 'Success');
        console.log("✅ Notes and Checklist saved successfully:", data);
    })
    .catch(error => console.error("❌ Error saving notes and checklist:", error));
   
}

$(document).on('click', '.save_notes,.checklist_option', function() {

    if(!callLogId) return console.log("Please save call log first!");


    let fieldName = '';
    let fieldValue = '';

    if($(this).hasClass('save_notes')) {
        fieldName = 'notes';
        fieldValue = $('.notes_box').val();
    }
    else if($(this).hasClass('checklist_option')) {
        fieldName = 'checklist';
        fieldValue = $('#check_list [type="checkbox"]:checked').map(function() {return this.value;}).get();
    }
    updateCallLog(fieldName,fieldValue,callLogId);
});

$(document).on('click', '.notes_input_box .fa-arrow-up', function(e) {
    e.preventDefault();

    $('.notes_box_container').toggleClass('active');

    if ($('.notes_box_container').hasClass('active')) {
        $('.notes_input_box').appendTo('#drag .content');
        $('#drag .title h2').text('Notes');
        $('#drag').show();
    }
    else {
        $('#drag').hide();
        $('.notes_input_box').prependTo('.notes_box_container');
    }

});

$(document).on('click', '#drag .close', function(e) {
    e.preventDefault();
    $('.notes_box_container').removeClass('active');
    $('.notes_input_box').prependTo('.notes_box_container');
});


$(document).on('click', '.call_end_btn', function(e) {
    e.preventDefault();
    if(callId) callEnd();
});

// Format time in HH:MM:SS
function formatTime(seconds) {
    const hrs = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const mins = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const secs = String(seconds % 60).padStart(2, '0');
    return `${mins}:${secs}`;
}

window.addEventListener("beforeunload", () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});

$(document).on('click', '.call_mute', function(e) {
    e.preventDefault();
    let austream = remoteAudio.srcObject;
    let audioTracks = austream.getAudioTracks();
    let mutebtn = $(this);
    if (audioTracks.length > 0) {
    
        let requestData = JSON.stringify({
            endpoint: "/web-calls/v1/calls/"+callId+"/" + (audioTracks[0].enabled ? "mute" : "unmute"),
            method: "POST",
        });
        makeCall(requestData, function(requestData, responseData) {
            audioTracks[0].enabled = !audioTracks[0].enabled;
            mutebtn.toggleClass('active', !audioTracks[0].enabled);
            console.log("Mute/Unmute successful!", responseData);
        });
    }
});

let recordingStatus = "start";

$(document).on('click', '.call_record', function(e) {
    e.preventDefault();

    if (recordingStatus=="stop") {
        recordingStatus = "start";
        $('.call_record').addClass('active');
    }  else if (recordingStatus=="start")
    {
        recordingStatus = "pause";
        $('.call_record').removeClass('active');
    }
    else if (recordingStatus=="pause"){
        recordingStatus = "unpause";
        $('.call_record').addClass('active');
    }
    else if (recordingStatus=="unpause"){
        recordingStatus = "pause";
        $('.call_record').removeClass('active');
    }

    let requestData = JSON.stringify({
        endpoint: "/call-control/v1/calls/recording/"+recordingStatus,
        method: "POST",
        data: {
            "accountKey": lineDetails.accountKey,
            "legId": legId
        }
    });

    console.log("Recording Status:", recordingStatus);
    makeCall(requestData, function(requestData, responseData) {
        console.log("Recording started successfully!", responseData);
    });
});

$(document).on('click', '.task_trash', function() {
    $(this).closest('.task_contact_box').remove();
});


$(document).ready(function() {
    if ($('.select2').length) {
        $('.select2').select2();
    }
});

$('.list_option_search input').on('input', function () {
    var searchText = $(this).val().toLowerCase(); // Get the input text in lowercase
    
    // Iterate through each list option
    $(this).closest('.select-menu').find('.list_option').each(function () {
        var optionText = $(this).find('.option-text').text().toLowerCase(); // Get the option text in lowercase

        // Show or hide the list item based on whether the text matches the search text
        if (optionText.includes(searchText)) {
            $(this).show(); // Show the matching option
        } else {
            $(this).hide(); // Hide the non-matching option
        }
    });
});

$(document).ready(function () {
        const $dropArea = $('#drop-area');
        if ($dropArea.length) {
            const $fileInput = $('#drop-area #file-input');
            const $fileInfo = $('#drop-area #file-info');

            const acceptedTypes = $fileInput.attr('accept').split(',').map(type => type.trim());

            $dropArea.on('dragover', function (e) {
                e.preventDefault();
                $dropArea.addClass('hover');
            });

            $dropArea.on('dragleave', function () {
                $dropArea.removeClass('hover');
            });

            $dropArea.on('drop', function (e) {
                e.preventDefault();
                $dropArea.removeClass('hover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    validateAndHandleFiles(files);
                }
            });

            $dropArea.on('click', function () {
                $fileInput.click();
            });

            $fileInput.on('click', function (e) {
                e.stopPropagation();
            });

            $fileInput.on('change', function (e) {
                const files = e.target.files;
                if (files.length > 0) {
                    validateAndHandleFiles(files);
                }
            });
        }

        function validateAndHandleFiles(files) {
            const file = files[0];

            const isValidType = acceptedTypes.some(type => {
                if (type === '*') return true;
                return file.type.match(type) || file.name.endsWith(type);
            });

            if (isValidType) {
                $fileInfo.html(`<strong>File Selected:</strong> ${file.name} (${(file.size / 1024).toFixed(2)} KB)`);
            } else {
                $fileInfo.html(`<span style="color: red;">Invalid file type! Please upload a valid file.</span>`);
                $fileInput.val('');
            }
        }
    });