
<% if Owner %>
	<% if Owner.Avatar %>
	<% else %>
	<div class="gravaterImage">
		<img src="http://www.gravatar.com/avatar/$Owner.gravatarHash" />
	</div>
	<% end_if %>
	$Owner.Title
<% else %>

Please <a href="Security/login">login</a>
<% end_if %>