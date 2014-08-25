;(function($) {
	$(function () {
		var segment = 'dashboard';
		if ($('#DashboardUrl').length) {
			segment = $('#DashboardUrl').val();
		}
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
//
//		function helper() {
//			return $("<div />").addClass("dashlet-drag ui-state-highlight").appendTo("body");
//		}
//
//		function update(e, ui) {
//			var dashboard = $(ui.item).parent(".dashboard");
//			var id        = dashboard.data("id");
//
//			var ids = dashboard.find(".dashlet").map(function(i, el) {
//				return $(el).data("id");
//			});
//
//			$.post(segment + "/updateDashboard", { dashboard: id, 'order[]': ids.get() })
//		}
//
//		$(".dashboard").each(function() {
//			$(this).sortable({
//				placeholder: "ui-state-highlight dashlet-placeholder",
//				handle:      ".dashlet-title",
//				connectWith: ".dashboard",
//				tolerance:   "pointer",
//				revert:      true,
//				helper:      helper,
//				update:      update
//			});
//		});

		var gridster = $(".gridster ul").gridster({
			widget_margins: [10, 10],
			widget_base_dimensions: [120, 120],
			avoid_overlapped_widgets: true,
			resize: {
				enabled: true,
				handle_class: 'dashlet-resize', //Setting the class of the resize handle
				stop: function(e, i, widget) {
					/*
					*	This function creates post requests for the resized object
					*	and also every object that had its position changed.
					*	The resized object, isn't classified as a changed object
					*	hence why there are two separate posts, as we cannot loop
					*	over just the $changed objects.
					*/
					$.post($(widget[0].firstElementChild).attr('data-link') + '/save', this.serialize($(widget))[0])
					.done(function(data) {});

					if(this.$changed.length > 0) {
						for(var i = 0; i < $(this.$changed).length; i++) {
							$.post($(this.$changed[i].firstElementChild).attr('data-link') + '/save', this.serialize($(this.$changed))[i])
							.done(function() {});
						}
					}

					/*
					*	Using this as to clear out what objects are in a "changed" state
					*	as we already have it saved to the database.
					*	This is because the changed states persists across every
					*	change regardless of if the object was actually changed
					*	in that specific call.
					*	Otherwise we'd be writing to the database when we didn't
					*	need to because nothing had changed :)
					*/
					this.$changed = $([]);
				}
			},
			draggable: {
				handle: '.dashlet-title h3',
				stop: function(e, i) {
					/*
					*	If objects have changed position, cycle through all changed objects
					*	and create post requests to update positions in the database.
					*/
					if(this.$changed.length > 0) {
						for(var i = 0; i < $(this.$changed).length; i++) {
							$.post($(this.$changed[i].firstElementChild).attr('data-link') + '/save', this.serialize($(this.$changed))[i])
							.done(function() {});
						}
					}

					//See above in resize.stop to see why we do this.
					this.$changed = $([]);
				}
			}
		}).data('gridster');

//		var gridly = $('.dynamicgrid').gridly()

		$(document).on('click', ".dashlet-action-toggle", function() {
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

		$(document).on('click', '.dashlet-action-refresh', function (e) {
			e.preventDefault();
			var dashlet = $(this).closest(".dashlet");
			dashlet.refresh();
			return false;
		});

		$(document).on('click', ".dashlet-action-edit", function() {
			var dashlet = $(this).parents(".dashlet");
			var id      = dashlet.data("id");

			dialog.empty().addClass("dashlet-dialog-loading");

			$.get(segment + "/editorfor", { "DashletID": id }, function(html) {
				dialog.removeClass("dashlet-dialog-loading").html(html);

				//Using jQuery ColorPicker
				//Commented out for usage of HTML5 <input type='color' />
				//Uncomment and follow the steps in /code/dashlets/Dashlet.php:getDashletFields()
				/*dialog.find("form").children('fieldset').children('.dashlet-color').each(function() {
					$(this).children('div').children('input').colorpicker({
						autoOpen: false,
						parts: ['map', 'bar', 'footer'],
						layout:{
							map: [0,0,1,1],
							bar: [1,0,1,1]
						},
						part: {
							map:{size:128},
							bar:{size:128}
						},
						inlineFrame: false
					});
				});*/
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
				width:   600,
				height:  400,
				modal:   true,
				buttons: buttons
			});

			return false;
		});

		$(document).on('click', ".dashlet-action-delete", function() {
			var dashlet = $(this).parents(".dashlet");
			var id      = dashlet.data("id");

			if(confirm("Are you sure you want to delete this dashlet?")) {
				$.post(segment + "/deletedashlet", { "DashletID": id }, function(resp) {
					if(resp.success) dashlet.remove();
				});
			}

			return false;
		});
		
		$('div.dashlet').entwine({
			refresh: function () {
				var id = this.attr('data-id');
				var reloadUrl = segment + '/loaddashlet';
				this.loadUrl(reloadUrl, {DashletID: id});
			},
			loadUrl: function (reloadUrl, params) {
				var _this = this;
				$.get(reloadUrl, params, function (data) {
					if (data && data.length && data.indexOf('dashlet') >= 0) {
						_this.replaceWith(data);
						delete _this;
					}
				})
			}
		})

		$(window).unload(function() {
			$.cookie("dashlets-collapsed", collapsed.join(","), { expires: 9999 });
		});
	})
})(jQuery);
