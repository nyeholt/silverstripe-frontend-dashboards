<div class="dashlet-content-anchor recent-dashlet-content">
	<% if Items %>
		<ol class="recent-dashlet">
			<% loop Items %>
				<li><a href="$Link" data-pageid="$ID">$Title.XML</a> $LastEdited.Ago</li>
			<% end_loop %>
		</ol>
	<% end_if %>
</div>