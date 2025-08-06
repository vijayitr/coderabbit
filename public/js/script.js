// script.js
$(document).ready(function() {
    $('.selected-option').click(function() {
        const $dropdown = $(this).siblings('.options');
        $dropdown.toggle();
        $(this).find('.chevron').toggleClass('bi-chevron-down bi-chevron-up');
    });

    $('.option').click(function() {
        const selectedText = $(this).html(); // Get the HTML which already includes <strong>
        const $selectedOption = $(this).closest('.filter-select').find('.selected-option');
        const label = $selectedOption.html().split(':')[0]; // Get label (Date: or Sort By:)
        $selectedOption.html(label + ': <strong>' + selectedText + '</strong> <i class="bi bi-chevron-down chevron"></i>');
        $(this).parent().hide(); // Hide the options of the current dropdown
    });

    $(document).click(function(event) {
        if (!$(event.target).closest('.filter-select').length) {
            $('.options').hide(); // Hide all dropdowns
            $('.chevron').removeClass('bi-chevron-up').addClass('bi-chevron-down'); // Reset chevrons
        }
    });

    function getGreeting() {
        const hour = new Date().getHours();

        if (hour >= 5 && hour < 12) {
            return "Good Morning";
        } else if (hour >= 12 && hour < 17) {
            return "Good Afternoon";
        } else if (hour >= 17 && hour < 21) {
            return "Good Evening";
        } else {
            return "Good Night";
        }
    }
    $("#greeting").text(getGreeting());

    $('#global-search').on('keyup', function() {
        const searchValue = $(this).val();

        if ($.fn.DataTable.isDataTable('.inner-content .dataTable')) {
            $('.inner-content .dataTable').DataTable().search(searchValue).draw();
        }
    });

    // toastr.options = {
    //     "closeButton": true,             
    //     "debug": false,
    //     "newestOnTop": false,
    //     "progressBar": true,
    //     "positionClass": "toast-top-right",
    //     "preventDuplicates": false,
    //     "onclick": null,
    //     "showDuration": "300",
    //     "hideDuration": "1000",
    //     "timeOut": "0",                  
    //     "extendedTimeOut": "0",          
    //     "showEasing": "swing",
    //     "hideEasing": "linear",
    //     "showMethod": "fadeIn",
    //     "hideMethod": "fadeOut"
    // }    
    
}); //document.ready ends
