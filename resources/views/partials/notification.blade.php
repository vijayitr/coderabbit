<input type="hidden" name="" class="chat_notification_status">
<script type="text/javascript">
    window.userId = '{{ auth()->id() }}';
    $(document).ready(function() {

        $(document).on('click', '.all_notifications .notification', function (e) {
            e.stopPropagation();
            $('#notification-dropdown').slideToggle();
        });

        $(document).on('click', function (e) {
            // If the click is outside the dropdown and the notification icon
            if (!$(e.target).closest('#notification-dropdown, .all_notifications .notification').length) {
                $('#notification-dropdown').slideUp();
            }
        });

        $(document).on('click', '.mark-read', function() {
            clearAllNotifications()
        });
        function clearAllNotifications() {
            $.ajax({
                url: "{{route('markAsRead')}}",
                type: 'get',
                success: function(response) {
                    getNotifications();
                },
                error: function(xhr) {
                    // toastr.error('Something went wrong. Please try again later.', 'Error');
                }
            });
        }

        var no_notifications_html = '<div id="notification-alert" class="alert alert-info text-center mb-0" role="alert"><span id="notification-message"><i class="fa fa-thumbs-down me-2"></i>No Notifications Available</span></div>';
        getNotifications();
        function getNotifications() {
            $.ajax({
                url: "{{route('notifications')}}",
                type: 'get',
                success: function(response) {
                    var count = 0;
                    if (response.status) {
                        count = response.data.length;
                        if (response.data.length) {
                            $('.all_notifications .list-group').append('');
                            for (const notification of response.data) {
                                var html = `<a href="`+notification.url+`" class="list-group-item list-group-item-action notification_url" data-type="`+notification.type+`" data-taskid="`+notification.task_id+`">`+notification.message+` <div class="float-endf text-secondary">`+notification.formatted_date+`</div></a>`;
                                $('.all_notifications .list-group').append(html);
                            }
                        } else {
                            $('.all_notifications .list-group').html(no_notifications_html);
                        }
                    }
                    $('.all_notifications .notification_count').html(count);
                },
                error: function(xhr) {
                    // toastr.error('Something went wrong. Please try again later.', 'Error');
                }
            });
        }

        $(document).on('click', '.notification_url', function(e){
            e.preventDefault();
            var type  = $(this).attr('data-type');
            if (type == 'task') {
                var id = $(this).attr('data-taskid');
                localStorage.setItem('selected_task_id', id);
                window.location.href = '/time-entry';
            }
        });
        // Echo.channel('chat').listen('MessageSent', (e) => {
        //     console.log(e.message);
        // });
        window.Echo.private('user.' + window.userId)
            .listen('.task.assigned', (data) => {
                var html = `<a href="`+data.url+`" class="list-group-item list-group-item-action data_url py-3" data-type="`+data.type+`" data-taskid="`+data.task_id+`">`+data.message+` <div class="float-endf text-secondary">`+getFormatedDate(data.created_at)+`</div></a>`;
                let current = parseInt($('.all_notifications .notification_count').text()) || 0;
                $('.all_notifications .notification_count').text(current + 1);
                toastr.success(data.message, 'Success');
                $('.all_notifications .list-group').prepend(html);
                if (data.type == 'ticket_reply') {
                    var randomValue = Math.floor(Math.random() * 1000) + 1;
                    $('.chat_notification_status').val(randomValue+'__'+data.task_id);
                    $('.chat_notification_status').trigger('change');
                }
            });

        function getFormatedDate(dateString) {
            // Parse the date string
            var date = new Date(dateString);

            // Get components
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var ampm = hours >= 12 ? 'PM' : 'AM';

            // Convert to 12-hour format
            hours = hours % 12;
            hours = hours ? hours : 12; // '0' hour should be '12'

            // Add leading 0 to minutes if needed
            minutes = minutes < 10 ? '0' + minutes : minutes;

            // Month names
            var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                              "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            return hours + ':' + minutes + ' ' + ampm + ' ' +
                                ('0' + date.getDate()).slice(-2) + '-' +
                                monthNames[date.getMonth()] + '-' +
                                date.getFullYear();
        }
    });
</script>