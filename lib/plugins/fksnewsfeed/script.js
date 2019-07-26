/**
 * JavaScript for doku plugin FKSnewsfeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */

jQuery(function() {

    var $ = jQuery;
    _edit_news();
    _more_news();
    _link_news();
    $(window).load(function() {
        
        $('div.FKS_newsfeed_stream').each(function() {
            var $stream = $(this);
            $(this).append(_add_load_bar());
            _start_load_animation();

            var newsSTREAM = $(this).data("stream");
            var newsFEED = $(this).data("feed");
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'stream', news_stream: newsSTREAM, news_feed: newsFEED},
            function(data) {
                $stream.html(data["r"]);
                _edit_news();
                _more_news();
                _link_news();
                _link_rss();
                 _news_manage();
            },
                    'json');
        });
        ;
    })
            ;
    function _edit_news() {
        $('div.FKS_newsfeed_even,div.FKS_newsfeed_odd').mouseover(function() {
            var newsID = $(this).data("id");
            var $editdiv = $('div.FKS_newsfeed_edit[data-id=' + $(this).data("id") + ']');
            if ($editdiv.html() !== "") {
                return false;
            }
            ;
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'edit', news_id: newsID},
            function(data) {
                $editdiv.html(data["r"]);
                _link_news();
                _news_share_FB();
            }, 'json');
        });
    }
    ;
    function _more_news() {
        $('div.FKS_newsfeed_more').click(function() {
            //event.preventDefault();

            var newsVIEW = $(this).data("view");
            var newsSTREAM = $(this).data("stream");
            var $streamdiv = $('div.FKS_newsfeed_stream[data-stream=' + newsSTREAM + ']');
            $(this).append(_add_load_bar());
            _start_load_animation();
            $.post(DOKU_BASE + 'lib/exe/ajax.php',
                    {call: 'plugin_fksnewsfeed', target: 'feed', name: 'local', news_do: 'more', news_stream: newsSTREAM, news_view: newsVIEW},
            $.proxy(function(data) {
                $(this).html("");
                $streamdiv.html($streamdiv.html() + data["news"]);
                if (data['more']) {
                    $('div.FKS_newsfeed_more[data-stream=' + newsSTREAM + ']').remove();
                }
                _edit_news();
                _more_news();
                _link_news();
                _link_rss();
                _news_manage();
            }, this)
                    , 'json');
        });
    }
    ;
    function _link_news() {
        $('button.FKS_newsfeed_link_btn').click(function() {
            var ID = $(this).data('id');
            $('input.FKS_newsfeed_link_inp[data-id=' + ID + ']').slideDown();
        }
        );
    }
    ;
    function _news_manage() {
        $('.FSK_newsfeed_manage_btn').click(function() {
            $('.FKS_newsfeed_manage').slideDown();
        });
    }
    ;
    function _link_rss() {
        $('button.FKS_newsfeed_rss_btn').click(function() {

            $('input.FKS_newsfeed_rss_inp').slideDown();
        }
        );
    }
    ;
    function _news_share_FB() {
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id))
                return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_PI/sdk.js#xfbml=1&version=v2.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }
    ;
    function _add_load_bar() {
        return '<div class="load" style="text-align:center;clear:both">' +
                '<img src="/lib/plugins/fksnewsfeed/images/load.gif" alt="load">' +
                '</div>';
    }

    function _start_load_animation() {
        var $load = $('.progress-bar');

        $load.animate({
            width: 99 + "%"

        }, 4000, "linear");
        ;
    }
    ;
    return true;
});









