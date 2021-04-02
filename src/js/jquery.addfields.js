jQuery(document).ready(function($) {
	$('.add_field').on('click',function() {
		var num     = $('.clonedInput').length; // how many "duplicatable" input fields we currently have
		var newNum  = new Number(num + 1);      // the numeric ID of the new input field being added
		// create the new element via clone(), and manipulate it's ID using newNum value
		var newElem = $('#field' + num).clone().attr('id', 'field' + newNum);
		// manipulate the name/id values of the input inside the new element
		// insert the new element after the last "duplicatable" input field
		$('#field' + num).after(newElem);
		// enable the "remove" button
		$('.del_field').removeAttr('disabled');
		// business rule: you can only add 16 occurrences
		if (newNum == 16)
			$('.add_field').attr('disabled','disabled');
	});

	$('.del_field').on('click',function() {
		var num = $('.clonedInput').length; // how many "duplicatable" input fields we currently have
		$('#field' + num).remove();     // remove the last element
		// enable the "add" button
		$('.add_field').removeAttr('disabled');
		// if only one element remains, disable the "remove" button
		if (num-1 == 1)
			$('.del_field').attr('disabled','disabled');
	});
	$('.del_field').attr('disabled','disabled');
	
	$('#mcm-settings select').on('change',function() {
		var selected = $(this).val();
		if ( selected == 'richtext' || selected == 'select' ) {
			$(this).parents("tr:first").find('.mcm-repeatable').attr('disabled', true );
		} else {
			$(this).parents("tr:first").find('.mcm-repeatable').removeAttr( 'disabled' );
		}
	});
	
	$(".up,.down").click(function(e){
		e.preventDefault();
		$('#mcm-settings input[class=mcm-delete]').attr('disabled',true);
		$('#mcm-settings table tr').removeClass('fade');
		var row = $(this).parents("tr:first");
		if ($(this).is(".up")) {
			row.insertBefore(row.prev()).addClass('fade');
		} else {
			row.insertAfter(row.next()).addClass('fade');
		}
	});
	$('input[name=mcm_new_fieldset]').on('change',function() {
		var regex = new RegExp( "(['])", 'g' );
		if ( regex.exec($(this).val()) ) {
			$('#warning').html( mcmi18n.mcmWarning );
		} else {
			$('#warning').html( mcmi18n.mcmOK );
		}	
	});
});