/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(function() {
    var $ = jQuery;
    $('.FKS_mathengine_btn').click(function() {
        var attr = {};
        $('.FKS_mathengine_input').each(function() {
            attr['param-' + $(this).attr('id')] = $(this).attr('value');
        });
        var result = engine();
        $.post(DOKU_BASE + 'lib/exe/ajax.php',
                {call: 'plugin_fksmathengine', target: 'fksmathengine', name: 'local', param: attr, result: result},
        function(data) {
            if (data['s']) {
                document.getElementById("results").value = result;
            }
        },
                'json');
    });
});


