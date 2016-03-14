
<div class="<% if $parent.DashboardLayout == 'dynamic' %>gridster<% end_if %>" style="position: relative;">
	<ul class="grid-container" data-id="$ID">
		<% if WidgetControllers %>
		<% loop WidgetControllers %>
		<li data-row="$PosY" data-col="$PosX" data-sizex="$Width" data-sizey="$Height">
			<% include DashletLayout %>
		</li>
		<% end_loop %>
		<% end_if %>
	</ul>
</div>

<% if $parent.DashboardLayout == 'dynamic' %>
<div class="mobile-layout">
	<% if WidgetControllers %>
	<% loop WidgetControllers %>
	<div class="mobile-dashlet-icon {$ClassName}Mobile">
		<span class="dashlet-title-icon" data-id="$ID" data-link="$Link" data-tooltip title="$Title.ATT" aria-haspopup="true" class="has-tip tip-top">$Title.LimitCharacters(1,'')</span>
	</div>
	<% end_loop %>
	<% end_if %>
</div>
<% end_if %>