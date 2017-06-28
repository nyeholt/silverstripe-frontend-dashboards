
window.SS = window.SS || {}
window.DashboardHelper = window.DashboardHelper || {};

;(function($) {
	
    DashboardHelper.showDialog = function (name) {
        var popup;
        var dialog = $('#dashboard-dialog');
        if (!dialog.length) {
            dialog = $('<div class="dashboard-overlay" id="dashboard-dialog">');
            popup = $('<div class="dashboard-popup">').appendTo(dialog);
            popup.append('<a class="dashboard-dialog-close" href="#">&times;</a>');
            popup.append('<div class="dashboard-dialog-content">');
            $('body').append(dialog);
        }

        dialog.attr('data-name', name);
        // we want to actually return the content bit
        popup = dialog.find('.dashboard-dialog-content');
        $(dialog).addClass('active-dialog');
        return popup;
    }

    DashboardHelper.closeDialog = function () {
        var dialog = $('#dashboard-dialog');
        if (dialog.length) {
            dialog.removeClass('active-dialog');
            dialog.find('dashboard-dialog-content').html('');
            dialog.remove();
        }
    }
    
    $(document).on('click', '.dashboard-dialog-close', function (e) {
        e.preventDefault();
        DashboardHelper.closeDialog();
        return false;
    });
    
    window.SS.BasicDialog = function () {
        
    };
    
	var dialog = function(url, opts, requestParams) {
		var defaults = {
			width:     400,
			height:    400,
			modal:     true,
		};

		var dialog = DashboardHelper.showDialog();
        dialog.addClass('dialog-loading');
		
		if (url) {
			$.get(url, requestParams, function(data) {
				showInDialog(data);
			});
		}

		SS.currentDialog = dialog;

		return dialog;
	};
    
    function showInDialog(content) {
        if (!content.length) {
            return;
        }
        var data = $(content);
        
        var dialog = DashboardHelper.showDialog();

        dialog.removeClass("dialog-loading");

        if(data.length == 1 && data.is("form")) {
            dialog.empty().append(data);
                // remove any form buttons added, as we've already got some specified
//                    dialog.find('form .Actions').remove();
        } else {
            dialog.empty().append(data);
        }

        // if there's any form included, ajaxify it
        dialog.find('form').ajaxForm(function (response) {
            if (response && response.success) {
                location.href = location.href;
                return;
            } 
            showInDialog(response);
        });
    }

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

//		dialog.dialog("option", "buttons", buttons);
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
	});
	
	$(document).on('submit', "#dashboard-dialog form", function() {
		var form   = $(this);
		var dialog = form.parents("#dashboard-dialog");
		
		form.find("input[type=submit],button")
		    .prop("disabled", true)
		    .first()
		    .val("Loading...");
	});
	
	
})(jQuery);
