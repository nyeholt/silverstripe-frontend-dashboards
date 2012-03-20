
(function ($) {
	$.fn.sliderMenu = function (options) {
		var opts = $.extend({}, $.sliderMenu.defaults, options || {});
		return this.each(function () {
			var menu = $(this);
			menu.css({
				position: 'absolute',
				width: '200px',
				top: '' + opts.displayPosition.top + 'px',
//				left: '' + opts.displayPosition.left + 'px'
				left: '-210px'
			})

			if (opts.on == 'left') {
				menu.find('.openCloseWrap').css({
					position: 'absolute',
					right: '0px',
					top: '0px',
					height: '100%',
					width: '10px'
				})
			}
			
			menu.find('.openCloseWrap').click(function () {
				if (menu.css('left') == '-210px') {
					menu.animate({left: '0px'}, 'fast')
				} else {
					menu.animate({left: '-210px'}, 'fast')
				}
			});
		})
	}

	$.sliderMenu = {
		defaults: {
			on: 'left',
			displayPosition: {
				top: 200, 
				left: 0
			}
		}
	};
})(jQuery);