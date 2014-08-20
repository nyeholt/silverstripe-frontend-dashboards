<div class="gridster" style="position: relative;">
	<ul>
		<% if WidgetControllers %>
		<% loop WidgetControllers %>
		<li data-row="$PosY" data-col="$PosX" data-sizex="$Width" data-sizey="$Height">
			<% include DashletLayout %>
		</li>
		<% end_loop %>
		<% end_if %>
	</ul>
</div>
