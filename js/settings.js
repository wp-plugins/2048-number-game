jQuery(document).ready(function($) {
	
	$('input.color-picker').each(function() {
		$(this).wpColorPicker({
			change:	function(event, ui) {
				var hexcolor = $(this).wpColorPicker('color');
			}
		});
	});
	
	var upload_field, upload_preview;
	jQuery('.upload_button').on( 'click', function( event ) {
		event.preventDefault();
		
		var $el = $(this);
		upload_field = $el.closest('td').find('input.upload_field:first');
		upload_preview = $el.closest('td').find('img.upload_preview:first');

		var insertImage = wp.media.controller.Library.extend({
			defaults :  _.defaults({
				id: 'wp2048-image',
				title: wp2048.media_modal_title,
				allowLocalEdits: true,
				displaySettings: true,
				displayUserSettings: false,
				multiple : false,
				type : 'image'
			}, wp.media.controller.Library.prototype.defaults )
		});
		var frame = wp.media({
			button : { text : wp2048.media_modal_button },
			state : 'wp2048-image',
			states : [
				new insertImage()
			]
		});
		
		frame.on( 'select', function() {
			var state = frame.state('wp2048-image');
			var selection = state.get('selection');

			if ( ! selection ) return;

			selection.each( function(attachment) {
				var display = state.display( attachment ).toJSON();
				var obj_attachment = attachment.toJSON();
				var obj_display = wp.media.string.props( display, obj_attachment );
				if ( 'image' === obj_attachment.type ) {
					if ( obj_display.height < 107 || obj_display.width < 107 ) {
						alert(wp2048.alert_image_size);
					} else {
						upload_field.val( obj_display.src );
						upload_preview.attr( 'src', obj_display.src ).show();
					}
				} else {
					alert(wp2048.alert_not_image);
				}
			});
		});
		
		frame.open();
	});
	
	$('.upload_clear').on('click', function(e) {
		$(this).closest('td').find('input.upload_field:first').val('');
		var defimg = $(this).closest('td').find('img.upload_preview:first').attr('data-default');
		$(this).closest('td').find('img.upload_preview:first').attr('src',defimg);
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