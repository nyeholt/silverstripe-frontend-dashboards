


<input id="DashboardID" value="$Dashboard.ID" type="hidden" />
<input id="DashboardUrl" value="$Link" type="hidden"/>

<div class="row">
	<div class="col-md-12">
		<div id="header-buttons" class="ui-buttonset">
			<a href="$Link(adddashlet)" data-width="340" data-height="200" data-dialog="dialog" class="ui-button ui-widget ui-state-default ui-corner-left ui-button-text-icon-primary">
				<span class="ui-button-icon-primary ui-icon ui-icon-plus"></span>
				<span class="ui-button-text">Add Dashlet</span>
			</a>
			<a href="$Link(edit)/$CurrentDashboard.ID" data-width="340" data-height="300" data-dialog="dialog" class="ui-button ui-widget ui-state-default ui-corner-right ui-button-text-icon-primary">
				<span class="ui-button-icon-primary ui-icon ui-icon-pencil"></span>
				<span class="ui-button-text">Edit Dashboard</span>
			</a>
		</div>
	</div>
</div>

<div class="row">
$Dashboard
</div>
