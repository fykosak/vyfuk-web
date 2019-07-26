/* DOKUWIKI:include_once tablesort/jquery.tablesorter.min.js*/
/* DOKUWIKI:include_once tablesort/jquery.tablesorter.staticrow.min.js*/
;
/* DOKUWIKI:include_once dwmediaselector.js*/

jQuery(function () {
    var $ = jQuery;
    // $('.content table').tablesorter();
    $('.content table').tablesorter({
            widgets: ['staticRow'],
            sortInitialOrder: 'desc'
        }
    );

    $('.person').on('mouseenter', function (event) {
        "use strict";
        const src = $(this).attr('data-src');
        const $tooltip = $(document.createElement('div'));
        var display = true;
        $('<img/>').attr({src: src}).load(function () {
            if(display){
                $tooltip.css({
                    position: 'absolute',
                    top: event.pageY + 5,
                    left: event.pageX + 10,
                    width: '70px'
                }).append(this);
                $('html').append($tooltip);
            }
        });
        $(this).on('mouseleave', function () {
            display = false;
            $tooltip.remove();
        });
    });
});
