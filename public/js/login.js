// login.js
$(document).ready(function() {
    $('#togglePassword').on('click', function() {
        const passwordField = $('#password');
        const eyeIcon = $('#eyeIcon');
        
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        
        eyeIcon.toggleClass('fa-eye-slash fa-eye');
    });
}); //document.ready ends
