jQuery(function($) {
	var collapsed = [];
	var cookie    = $.cookie("dashlets-collapsed");

	if(cookie) {
		collapsed = cookie.split(",");

		$.each(collapsed, function() {
			var dashlet = $(".dashlet[data-id=" + this + "]");
			
			dashlet.addClass("dashlet-collapsed")
			       .find(".dashlet-content")
			       .slideUp("fast")
			       .end()
			       .find(".dashlet-action-toggle")
			       .addClass("ui-icon-plus")
			       .removeClass("ui-icon-minus");
		});
	}

	var dialog = $("<div />").addClass("dashlet-dialog").hide().appendTo("body");

	function helper() {
		return $("<div />").addClass("dashlet-drag ui-state-highlight").appendTo("body");
	}

	function update(e, ui) {
		var dashboard = $(ui.item).parent(".dashboard");
		var id        = dashboard.data("id");

		var ids = dashboard.find(".dashlet").map(function(i, el) {
			return $(el).data("id");
		});

		$.post("dashboard/updateDashboard", { dashboard: id, 'order[]': ids.get() })
	}

	$(".dashboard").each(function() {
		$(this).sortable({
			placeholder: "ui-state-highlight dashlet-placeholder",
			handle:      ".dashlet-title",
			connectWith: ".dashboard",
			tolerance:   "pointer",
			revert:      true,
			helper:      helper,
			update:      update
		});
	});

	$(".dashlet-action-toggle").live("click", function() {
		var link    = $(this);
		var dashlet = link.parents(".dashlet");
		var id      = dashlet.data("id");

		if(dashlet.hasClass("dashlet-collapsed")) {
			dashlet.removeClass("dashlet-collapsed").find(".dashlet-content").slideDown("fast");
			link.addClass("ui-icon-minus").removeClass("ui-icon-plus");
			collapsed.splice(collapsed.indexOf(id), 1);
		} else {
			dashlet.addClass("dashlet-collapsed").find(".dashlet-content").slideUp("fast");
			link.addClass("ui-icon-plus").removeClass("ui-icon-minus");
			collapsed.push(id);
		}

		return false;
	});

	$(".dashlet-action-edit").live("click", function() {
		var dashlet = $(this).parents(".dashlet");
		var id      = dashlet.data("id");

		dialog.empty().addClass("dashlet-dialog-loading");

		$.get("dashboard/editorfor", { "DashletID": id }, function(html) {
			dialog.removeClass("dashlet-dialog-loading").html(html);
		});

		var buttons = {
			"Save": function() {
				var self = $(this);
				self.dialog("disable");

				dialog.find("form").ajaxSubmit({
					success: function(replace) {
						dashlet.replaceWith(replace);
						self.dialog("enable");
						self.dialog("close");
					}
				});
			},
			"Cancel": function() {
				$(this).dialog("close")
			}
		};

		dialog.dialog({
			title:   "Edit Dashlet",
			width:   800,
			height:  600,
			modal:   true,
			buttons: buttons
		});

		return false;
	});

	$(".dashlet-action-delete").live("click", function() {
		var dashlet = $(this).parents(".dashlet");
		var id      = dashlet.data("id");

		if(confirm("Are you sure you want to delete this dashlet?")) {
			$.post("dashboard/deletedashlet", { "DashletID": id }, function(resp) {
				if(resp.success) dashlet.remove();
			});
		}

		return false;
	});

	$(window).unload(function() {
		$.cookie("dashlets-collapsed", collapsed.join(","), { expires: 9999 });
	});
});
