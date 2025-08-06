@php use Carbon\Carbon; @endphp

<div class="p-3">

    <!-- Bootstrap 5 Modal -->
    <div class="modal fade" id="callSummaryModal" tabindex="-1" aria-labelledby="callSummaryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="callSummaryModalLabel">📋 Call Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modalCallSummary">Loading summary...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <div class="accordion notes_container overflow-auto">

    @foreach($callLogs as $call)
    @php
        $callDetails = json_decode($call->call_details, true);
                
        // Check if it's a JSON string inside a JSON string
        if (is_string($callDetails)) {
            $callDetails = json_decode($callDetails, true);
        }
        $startTime = isset($callDetails['callCreated']) ? Carbon::parse($callDetails['callCreated'])->format('d M Y, h:i A') : 'N/A';
        $endTime = isset($callDetails['callEnded']) ? Carbon::parse($callDetails['callEnded'])->format('d M Y, h:i A') : 'N/A';

        // ✅ Extract Caller Details
            $calleeName = 'Unknown';
            $calleeNumber = '0000000000';


            if(isset($callDetails['interactiveVoiceResponseSystems'][0])) {
                $calleeNumber = $callDetails['interactiveVoiceResponseSystems'][0]['type']['number'];
            }
            else if (!empty($callDetails['participants'])) {
                foreach ($callDetails['participants'] as $participant) {
                    if (isset($participant['type']['value']) && $participant['type']['value'] === 'PHONE_NUMBER') {
                        $calleeName = $participant['type']['callee']['name'] ?? 'Unknown';
                        $calleeNumber = $participant['type']['callee']['number'] ?? '0000000000';
                        break; // Stop after finding the first phone number
                    }
                }
            }


            $recordingId = null;
            if (!empty($callDetails['participants'])) {
                foreach ($callDetails['participants'] as $participant) {
                    if (!empty($participant['recordings'])) {
                        $recordingId = $participant['recordings'][0]['id'] ?? null;
                        $callStarted = $participant['recordings'][0]['startTimestamp'] ?? null;
                        break; // Stop after finding the first recording
                    }
                }
            }

            $startTime = isset($callStarted) ? Carbon::parse($callStarted) : null;
            $endTime = isset($callDetails['callEnded']) ? Carbon::parse($callDetails['callEnded']) : null;

            // ✅ Calculate Call Duration
            $duration = ($startTime && $endTime) ? $startTime->diffInSeconds($endTime) : 0;

            // ✅ Convert duration to readable format (HH:MM:SS)
            $formattedDuration = gmdate("H:i:s", $duration);
            $summary = isset($callDetails['aiAnalysis']) ? $callDetails['aiAnalysis']['summary'] : '';

            $conversationId = $callDetails['conversationSpaceId'] ?? null;
            $callLogId = $call->id;

            $checklist = $call->checklist ?? null;
            $notes = $call->notes ?? null;

    @endphp






        <div class="accordion-item mb-2" data-log-id="{{ $call->id }}">
            <h2 class="accordion-header" id="call_item_{{ $call->id }}">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCall_{{ $call->id }}" aria-expanded="true" aria-controls="collapseCall_{{ $call->id }}">
                    Department <span class="note_date">Duration: {{ $formattedDuration }}</span>
                </button>
            </h2>
            <div id="collapseCall_{{ $call->id }}" class="accordion-collapse collapse show" aria-labelledby="call_item_{{ $call->id }}">
                <div class="accordion-body bg-transparent">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div><strong class="call_list_lable">Process</strong></div>
                            <div class="call_list_content">Matrix SDT Calling</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div><strong class="call_list_lable">Case Id</strong></div>
                            <div class="call_list_content">0000000</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div><strong class="call_list_lable">Start Time</strong></div>
                            <div class="call_list_content">{{ $startTime ? $startTime->format('d M Y, h:i A') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div><strong class="call_list_lable">End Time</strong></div>
                            <div class="call_list_content">{{ $endTime ? $endTime->format('d M Y, h:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3 mb-2">
                            <div><strong class="call_list_lable">Number</strong></div>
                            <div class="call_list_content">{{ $calleeNumber }}</div>
                        </div>
                        <div class="col-md-9 mb-2">
                            <div><strong class="call_list_lable">Action</strong></div>
                            <div class="action-icons">
                                <x-time-entry.tab-pane.call-list-icons.call_outline />
                                <x-time-entry.tab-pane.call-list-icons.envelop_outline />
                                @if ($checklist)
                                    <x-time-entry.tab-pane.call-list-icons.checklist_outline :callLogId="$callLogId" :checklist="$checklist" />
                                @endif
                                @if ($notes)
                                    <x-time-entry.tab-pane.call-list-icons.notes_outline :notes="$notes"  />
                                @endif
                                <x-time-entry.tab-pane.call-list-icons.summary_outline  :conversationId="$conversationId" :summary="$summary" />
                                @if ($recordingId)
                                    <x-time-entry.tab-pane.call-list-icons.recording_outline :recordingId="$recordingId" />
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2 d-none recording-container">
                        @if ($recordingId)
                            <h6><strong>Recording:</strong>         <span class="loader" data-recording-id="{{ $recordingId }}" style="display: none;">Loading...</span>
                            </h6>
                            <audio controls data-recording-id="{{ $recordingId }}">
                                Your browser does not support the audio element.
                            </audio>
                        @else
                            <p><strong>Recording:</strong> Not Available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    
    @endforeach

    
        <div class="d-flex justify-content-center mt-3 custom-pagination">
            {{ $callLogs->links() }}
        </div>
    </div>
</div>