(function ($) {
	var vp = $("meta[name='viewport']"),
		sc = $(".sitescore-container"),
		fr = $("form#wp2048"),
		bt = $(".highscore-button");
		
	if (vp.length && wp2048.viewport.length > 1) {
		vp.attr("content", wp2048.viewport);
	} else {
		$("head").append('<meta name="' + wp2048.viewport + '">');
	}
	
	if (wp2048.metahead) {
		$("head").append('<meta name="HandheldFriendly" content="True">').append('<meta name="MobileOptimized" content="320">').append('<meta name="format-detection" content="telephone=no" />');
	}
	
	$(".fullscreen-button").on("click", function(e) {
		e.preventDefault();
		$("#wp2048game").toggleFullScreen();
	});
		
	fr.on("submit", function(e) {
		e.preventDefault();
		bt.html(wp2048.textvars.loading);
		var dt = $(this).serialize();
		var jqxhr = $.post(wp2048.ajaxurl,{action:'wp2048_highscore',postdata:dt,security:wp2048.nonce});
		jqxhr.done(function(score){
			console.log(score);
			if (score) {
				sc.text(score);
				bt.attr("disabled",true);
				bt.html(wp2048.textvars.submitted);
				if ( score > wp2048.highscore ) {
					fr.prepend('<strong>'+ wp2048.textvars.newhighscore +'</strong>');
				} else {
					fr.prepend('<strong>'+ wp2048.textvars.newuserscore +'</strong>');
				}
				wp2048.highscore = score;
			} else {
				bt.html(wp2048.textvars.failed).delay(1200).html(wp2048.textvars.submitbtn);
				fr.show();
			}
		});
		jqxhr.fail(function(errorThrown){console.log(errorThrown);});
	});
})(jQuery);