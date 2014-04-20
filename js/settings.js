jQuery(document).ready(function($) {
	
	$('input.color-picker').each(function() {
		$(this).wpColorPicker({
			change:	function(event, ui) {
				var hexcolor = $(this).wpColorPicker('color');
			}
		});
	});
	
	var upload_field, upload_preview;
	if ($('.upload_button').length) {
		$('.upload_button').on('click', function(e) {
			upload_field = $(this).closest('td').find('input.upload_field:first');
			upload_preview = $(this).closest('td').find('img.upload_preview:first');
			window.send_to_editor=window.send_to_editor_clone;
			tb_show('','media-upload.php?TB_iframe=true');
			return false;
		});
		window.original_send_to_editor = window.send_to_editor;
		window.send_to_editor_clone = function(html){
			file_url = jQuery('img',html).attr('src');
			if (!file_url) { file_url = jQuery(html).attr('href'); }
			tb_remove();
			upload_field.val(file_url);
			upload_preview.attr('src', file_url);
		}
	}
	$('.upload_clear').on('click', function(e) {
		$(this).closest('td').find('input.upload_field:first').val('');
		$(this).closest('td').find('img.upload_preview:first').hide();
		return false;
	});
	
	$('#scgen').on('click', function(e) {
		e.preventDefault();
		var dt = $('#wp2048-settings').serialize();
		var jqxhr = $.post(wp2048.ajaxurl,{action:'wp2048_shortcode',postdata:dt,security:wp2048.nonce});
		jqxhr.done(function(shortcode){
			console.log(shortcode);
			if (shortcode) {
				$('#customsc').html('<textarea class="large-text" row="5">' + shortcode + '</textarea>');
			} else {
				alert("Shortcode generation failed. Try again.");
			}
		});
		jqxhr.fail(function(errorThrown){console.log(errorThrown);});
		
	});
});