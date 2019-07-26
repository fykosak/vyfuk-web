/**
 * JavaScript for DokuWiki plugin FKS-NewsFeed
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author Michal Červeňák <miso@fykos.cz>
 */
/* global LANG, DOKU_BASE, FB, JSINFO, PluginSocial */
jQuery(function () {
    "use strict";
    let $ = jQuery;
    const CALL_PLUGIN = 'plugin_news-feed';
    const CALL_TARGET = 'feed';
    const CALL_MORE = 'more';
    const CALL_STREAM = 'stream';

    const fetch = (stream, start, length, action, callback) => {
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
            {
                call: CALL_PLUGIN,
                target: CALL_TARGET,
                news: {
                    do: action,
                    stream,
                    start,
                    length
                },
                page_id: JSINFO.id
            },
            callback,
            'json');
    };

    const loadBar = '<div class="load-bar w-100" style="text-align:center;clear:both">' +
        '<svg xmlns="http://www.w3.org/2000/svg" width="25%" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-blank"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"/><g transform="scale(0.55)"><circle cx="30" cy="150" r="30" fill="#1175da"><animate attributeName="opacity" from="0" to="1" dur="1s" begin="0" repeatCount="indefinite" keyTimes="0;0.5;1" values="0;1;1"/></circle><path d="M90,150h30c0-49.7-40.3-90-90-90v30C63.1,90,90,116.9,90,150z" fill="#1175da"><animate attributeName="opacity" from="0" to="1" dur="1s" begin="0.1" repeatCount="indefinite" keyTimes="0;0.5;1" values="0;1;1"/></path><path d="M150,150h30C180,67.2,112.8,0,30,0v30C96.3,30,150,83.7,150,150z" fill="#1175da"><animate attributeName="opacity" from="0" to="1" dur="1s" begin="0.2" repeatCount="indefinite" keyTimes="0;0.5;1" values="0;1;1"/></path></g></svg>' +
        '</div>';


    $('div.news-stream').each(function () {
        const $container = $(this);
        const $streamContainer = $container.find('.stream').eq(0).append(loadBar);

        const renderNews = (data) => {
            data.html.news.forEach(function (news) {
                $streamContainer.append(news);
            });
            if (window.PluginSocial) {
                window.PluginSocial.parse();
            }
        };

        const removeLoadBar = () => {
            $streamContainer.find('.load-bar').remove();
        };

        const renderNext = (data) => {
            removeLoadBar();
            renderNews(data);
            $streamContainer.append(data.html.button);
        };

        const renderInit = (data) => {
            $container.prepend(data.html.head);
            renderNext(data);
        };

        const start = +$streamContainer.data('start');
        fetch($streamContainer.data('stream'), start ? start : 0, $streamContainer.data('feed'), CALL_STREAM, renderInit);

        $container.on('click', '.more-news', function () {
            const $buttonContainer = $(this);
            const start = $buttonContainer.data('view');
            const stream = $buttonContainer.data('stream');
            $streamContainer.append(loadBar);
            $buttonContainer.remove();
            fetch(stream, start, 3, CALL_MORE, renderNext);
        });
    });

    return true;
});
