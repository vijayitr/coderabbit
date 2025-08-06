function adjustChatHeight() {
    var headerHeight = $('.msger-header').outerHeight();
    var inputAreaHeight = $('.msger-inputarea').length ? $('.msger-inputarea').outerHeight() : 0;
    var chatHeight = `calc(100% - ${headerHeight + inputAreaHeight}px)`;
    $('.msger-chat').css('height', chatHeight);
}
setTimeout(function() {
    adjustChatHeight();
}, 200);