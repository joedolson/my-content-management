(function ($) {
    $(function () {
		function split( val ){
			return val.split( /,s*/ );
		}
		var users = $( '.mcm-autocomplete-users' );
        $('.mcm-autocomplete-users').autocomplete({
                minLength: 3,
                source: function (req, response) {
                    $.getJSON(ajaxurl + '?callback=?&action=' + mcm_user_ajax_action, {
						'role': users.attr( 'data-value' ),
						'term': split( req.term ).pop()
					}, response);
                },
                select: function (event, ui) {
                    var label = $(this).attr('id');
                    $(this).val(ui.item.id);
                    $('label[for=' + label + '] span').text(' (' + mcm_i18n.selected + ': ' + ui.item.value + ')');
                    return false;
                }
            }
        );
		var posts = $( '.mcm-autocomplete-posts' );
        $('.mcm-autocomplete-posts').autocomplete({
                minLength: 3,
                source: function (req, response) {
                    $.getJSON(ajaxurl + '?callback=?&action=' + mcm_post_ajax_action, {
						'post_type': posts.attr( 'data-value' ),
						'term': split( req.term ).pop()
					}, response);
                },
                select: function (event, ui) {
                    var label = $(this).attr('id');
                    $(this).val(ui.item.id);
                    $('label[for=' + label + '] span').text(' (' + mcm_i18n.selected + ': ' + ui.item.value + ')');
                    return false;
                }
            }
        );		
    });
}(jQuery));