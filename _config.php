<?php

Director::addRules(40, array(
	''						=> 'DashboardController',
	'dashboard'				=> 'DashboardController',
));

Object::add_extension('Member', 'DashboardUser');
Object::add_extension('Dashlet',	'Restrictable');
Object::add_extension('DateField', 'DateFieldExtension');

include_once dirname(__FILE__).'/code/db/LoggingSQLite3Database.php';
