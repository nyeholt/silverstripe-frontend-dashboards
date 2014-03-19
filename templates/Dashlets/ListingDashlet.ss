<% if AddLink %>
	<span class="editLink">[<a href="$AddLink">Add</a>]</span>
<% end_if %>

<% if Items %>
	<ul id="ListingDashlet-$ID">
		<% loop Items %>
			<li><a href="$Link">$Title.XML</a></li>
		<% end_loop %>
	</ul>
<% end_if %>