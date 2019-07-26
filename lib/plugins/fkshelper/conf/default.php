<?php
$conf['deny_html_out']='
        <html>
        <head>
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script>
        jQuery(function(){
        
            var $ = jQuery;
            $(window).load(function(){
            $("img").animate({width:"500%",},20000);
            });
        });
        </script>
            </head>
            <body style="overflow:hidden;">
            <div style="margin-left:auto;margin-right:auto;width:50%;text-align:center">
            <h1>You are fucking spamer</h1>
           
  </div>    
   <img src="http://cdn.meme.am/instances/400x/54995689.jpg" style="position:absolute" alt="fuck you bro"/>
</body>
            </html>';

$conf['person-page-link']='';
$conf['person-image-src']='';