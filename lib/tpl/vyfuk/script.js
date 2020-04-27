/*var $ = jQuery;
 window.onscroll = function() {
 var $window = $(window);
 
 var wWidth = $window.width();
 if (wWidth > 900) {
 document.getElementById("scrollmenu").className = "scrollmenu";
 var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
 if (scrollTop > 150) {
 $("#scrollmenu").slideDown();
 }else{
 $("#scrollmenu").slideUp();
 }
 ;
 } else {
 document.getElementById("scrollmenu").className = "scrollmenu_mini";
 }
 ;
 };*/
jQuery(function () {
    var $ = jQuery;
    $("button.fks_minimenu_btn").click(function () {

        $("div.fksmenu").slideToggle("slow");
    });
    $(window).load(function () {

        console.log("%cNesockuj tento kód!!!", "color: red; font-size: x-large");



    });

});



 