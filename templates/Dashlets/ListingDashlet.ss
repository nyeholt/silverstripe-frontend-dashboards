
<% if AddLink %>
<span class="editLink">[<a href="$AddLink">Add</a>]</span>
<% end_if %>

<% if Items %>
<ul id="ListingDashlet-$ID">
	<% control Items %>
	<li><a href="$Link">$Title.XML</a></li>
	<% end_control %>
</ul>
<% end_if %>