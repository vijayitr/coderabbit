<link href="{{asset('css/ticketChat.css')}}" rel="stylesheet">
<section class="msger">
    <div class="h-100">
        <div class="">
            <header class="msger-header">
                <div class="msger-header-title">
                  <i class="fas fa-comment-alt me-1"></i> {{optional($ticket->replies->first())->message ?? 'No reply yet' }}
                </div>
                <div class="msger-header-options">
                  <span><i class="fas fa-close close_ticket_view"></i></span>
                </div>
            </header>
        </div>

            <main class="msger-chat">
                <div class="w-100">
                        @if($ticket->grouped_replies->count() > 0)
                            @foreach ($ticket->grouped_replies as $date => $replies)
                                <div class="d-flex align-items-center">
                                    <hr class="flex-grow-1">
                                    <span class="mx-2">{{ $date }}</span>
                                    <hr class="flex-grow-1">
                                </div>
                                @foreach ($replies as $reply)
                                        <div class="msg {{ $authUser->id == $reply->user_id ? 'right-msg' : 'left-msg' }} w-100 mb-2">
                                            <div class="msg-img" style="background-image: url('{{ $reply->user->profile_image ? $reply->user->profile_image : asset('images/dummy_user_avatar.png') }}');"></div>

                                            
                                            <div class="msg-bubble">
                                                <div class="msg-info">
                                                    <div class="msg-info-name">{{ $authUser->id == $reply->user_id ? 'You' : $reply->user->name }}</div>
                                                    <div class="msg-info-time">{{ $reply->created_at->format('H:i') }}</div>
                                                </div>

                                                <div class="msg-text">{{ $reply->message }}</div>
                                            </div>
                                        </div>
                                @endforeach
                            @endforeach
                        @endif

              
                </div>
            </main>
            @if($authUser->can('ticket.reply'))
                <div class="">
                    <form class="msger-inputarea" action="{{ route('tickets.storeReply', $ticket->id) }}" data-id="{{$ticket->id}}">
                        <input type="text" class="msger-input" placeholder="Enter your message...">
                        @if($ticket->status != 'closed')
                        <select name="status" class="form-control msger-status w-10 ms-2">
                            <option value="open" {{$ticket->status == 'open' ? 'selected' : ''}}>Open</option>
                            <option value="pending" {{$ticket->status == 'pending' ? 'selected' : ''}}>Pending</option>
                            <option value="resolved" {{$ticket->status == 'resolved' ? 'selected' : ''}}>Resolved</option>
                            <option value="closed" {{$ticket->status == 'closed' ? 'selected' : ''}}>Closed</option>
                        </select>
                        @endif
                        <button type="submit" class="msger-send-btn">Send</button>
                    </form>
                </div>
            @endif
    </div>
</section>
<script src="{{asset('js/ticketChat.js')}}"></script>