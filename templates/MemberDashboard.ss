<div class="dashboard" data-id="$ID">
	<% if WidgetControllers %>
		<% loop WidgetControllers %>
			<% include DashletLayout %>
		<% end_loop %>
	<% end_if %>
</div>