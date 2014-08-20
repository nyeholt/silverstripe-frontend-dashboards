(function($){
	$('.markdown-content').entwine({
		onmatch: function() {
			$(this).html(markdown.toHTML($(this).html()))
		}
	});

	$('.markdown-content').mousedown(function(e){
		e.stopPropagation();
	});
})(jQuery);