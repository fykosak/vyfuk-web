jQuery(function () {
    var $ = jQuery;
    $('.imageShowWrap .imageShow').each(function () {
        var r = (Math.random() * 30) - 15;
        $(this).css({transform: 'rotate(' + r + 'deg)'});
    });
});



