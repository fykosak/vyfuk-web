jQuery(function() {
    function split(val) {
        return val.split(/,\s*/);
    }
    function extractLast(term) {
        return split(term).pop();
    }
    var $tags = jQuery("#fkstaskrepo-tags");
    if (!$tags) {
        return;
    }
    
    var availableTags = $tags.data('tags');

    $tags.bind("keydown", function(event) {
        if (event.keyCode === jQuery.ui.keyCode.TAB &&
                jQuery(this).data("ui-autocomplete").menu.active) {
            event.preventDefault();
        }
    }).autocomplete({
        minLength: 2,
        source: function(request, response) {
            response(jQuery.ui.autocomplete.filter(
                    availableTags, extractLast(request.term)));
        },
        focus: function() {
            return false;
        },
        select: function(event, ui) {
            var terms = split(this.value);
            terms.pop();
            terms.push(ui.item.value);
            terms.push("");
            this.value = terms.join(", ");
            return false;
        }
    });
});

