
<% if Owner %>
	<div class="userProfile">
	<% if Owner.Avatar %>
	<% else %>
	
	<div class="gravatarImage">
		<img src="http://www.gravatar.com/avatar/{$Owner.gravatarHash}.jpg" />
	</div>
		
	<% end_if %>
	
	$Owner.Title

<% else %>

Please <a href="Security/login">login</a>
<% end_if %>