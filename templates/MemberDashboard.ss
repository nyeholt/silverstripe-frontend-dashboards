<div class="gridster" style="position: relative;">
	<ul>
		<% if WidgetControllers %>
		<% loop WidgetControllers %>
		<li data-row="1" data-col="1" data-sizex="2" data-sizey="1">
			<% include DashletLayout %>
		</li>
		<% end_loop %>
		<% end_if %>
	</ul>
</div>


<!--<div class="dynamicgrid" style="position: relative;">
		<% if WidgetControllers %>
		<% loop WidgetControllers %>
		<div data-row="1" data-col="1" data-sizex="1" data-sizey="1" class="brick small">
			<% include DashletLayout %>
		</div>
		<% end_loop %>
		<% end_if %>
</div>-->