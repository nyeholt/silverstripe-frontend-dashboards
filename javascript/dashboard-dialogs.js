
window.SS = window.SS || {}

;(function($) {
	
	var dialog = function(url, opts) {
		var defaults = {
			width:     400,
			height:    400,
			modal:     true,
			draggable: false,
			resizable: false,
			close: function (e) {
				if (SS.currentDialog) {
					SS.currentDialog.remove();
					delete SS.currentDialog;
				}
			}
		};

		var dialog  = $("<div class='dialog-loading'></div>");
		var options = $.extend({}, defaults, opts);
		
		if ($('.ui-dialog').length && $('.ui-dialog').is(':visible')) {
			$(".ui-dialog").dialog("close").dialog("destroy");
		}
		dialog.dialog(options);
		
		$.get(url, function(data) {
			var data = $(data);

			dialog.removeClass("dialog-loading");

			if(data.length == 1 && data.is("form")) {
				dialog.empty().append(data);
				buttons(dialog);
			} else {
				dialog.empty().append(data);
			}
		});
		
		SS.currentDialog = dialog;

		return dialog;
	};

	function buttons(dialog) {
		var form    = dialog.find("form");
		var actions = form.find(".Actions").hide();
		var buttons = {};

		actions.find("input[type=submit]").each(function(i, button) {
			var name  = $(this).attr("name");
			var input = $("<input>", { type: "hidden", name: name, val: "1" });

			buttons[this.value] = function() {
				form.append(input);
				button.click();
				input.detach();
			}
		});

		dialog.dialog("option", "buttons", buttons);
	}

	$.extend(true, window.SS, {
		Dialog: { open: dialog, buttons: buttons }
	});
	
	$(document).on('click', "[data-dialog]", function(e) {
		
		if (e.shiftKey) {
			return false;
		}
		
		var link = $(this);

		var dialog = SS.Dialog.open(link.attr("href"), {
			title:  link.data("title") || link.text(),
			width:  link.data("width") || 400,
			height: link.data("height") || 400
		});

		return false;
	});
	
	$('#editDashlets').click(function () {
		$('div.dashlet-title').toggle();
		return false;
	})
	
	$(document).on('submit', ".ui-dialog form", function() {
		var form   = $(this);
		var dialog = form.parents(".ui-dialog-content");
		
		form.find("input[type=submit]")
		    .attr("disabled", "disabled")
		    .first()
		    .val("Loading...");

		dialog.parent()
		      .find(".ui-button")
		      .button("disable")
		      .first()
		      .find(".ui-button-text")
		      .text("Loading...");
	});
	
	$(window).resize(function() {
		$(".ui-dialog-content").dialog("option", "position", "center");
	});
})(jQuery);
