jQuery(document).ready(function($){
	if ( firstItem.selected !== 'disabled' ) {
		var tabs = $('.mcm-settings .wptab').length;
		$('.mcm-settings .tabs a[href="#'+firstItem.selected+'"]').addClass('active');
		if ( tabs > 1 ) {
			$('.mcm-settings .wptab').not( '#'+firstItem.selected ).hide();
			$('.mcm-settings .tabs a').on('click',function(e) {
				e.preventDefault();
				$('.mcm-settings .tabs a').removeClass('active');
				$(this).addClass('active');
				var target = $(this).attr('href');
				$('.mcm-settings .wptab').not(target).hide();
				$(target).show();
			});
		}
	}
});