var $ = jQuery;
$(function () {
    $('.fksdbexport.js-renderer').each(function (e) {
        var data = JSON.parse($(this).attr('data'));
        const f = new Function("let data = arguments[0]; let container = arguments[1];" + $(this).data('js'));
        f.call({}, data, this);
    });
});
