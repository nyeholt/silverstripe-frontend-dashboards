<?php

// Director::addRules(40, array(
// uncomment the following for all requests to route to the dashboard controller
// 	''					=> 'DashboardController',
// Add a rule like the following to have /dashboard work as a catchall url
// Alternatively, create your own DashboardPage
//	'dashboard'				=> 'DashboardController',
// ));

Object::add_extension('Member', 'DashboardUser');
Object::add_extension('Dashlet',	'Restrictable');
Object::add_extension('DateField', 'DateFieldExtension');

