
		<nav class="navbar navbar-default" role="navigation">
			<div class="container-fluid">
				<div class="collapse navbar-collapse" >

					<div class="btn-group dropup">
						
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Dashboard</button>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only">Toggle Dropdown</span>
						</button>
						<ul class="dropdown-menu">
							<li><a href="$Link(adddashlet)" data-width="340" data-height="200" data-dialog="dialog">Add Dashlet</a></li>
							<li><a href="$Link(edit)/$CurrentDashboard.ID" data-width="340" data-height="400" data-dialog="dialog">Edit Dashboard</a></li>
							<!-- Dropdown menu links -->
						</ul>
					</div>
				</div>
			</div>
		</nav><!--/.nav-collapse -->