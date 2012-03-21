<?php

/**
 * A dashboard page is a url identifiable dashboard in the system that a user can
 * customise to their whim.
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class DashboardPage extends DataObject {

	public static $max_dashboards = 3;
	public static $db = array(
		'Title'					=> 'Varchar(128)',
		'URLSegment'			=> 'Varchar(64)',
		'ColumnLayout'			=> "Enum('1col,2colLW,2colRW,3col','2colRW')"
	);
	
	public static $defaults = array(
		'ColumnLayout'			=> '2colRW'
	);
	
	public static $has_many = array(
		'Dashboards'			=> 'MemberDashboard',
	);
	public static $extensions = array(
		'Restrictable',
	);
	
	private $controller;
	
	/**
	 * @param type $controller 
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}

	public function getDashboard($index) {
		$dashboards = $this->Dashboards();
		/* @var $dashboards ComponentSet */
		$board = $dashboards->getIterator()->getOffset($index);
		return $board;
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		$dashboards = $this->Dashboards();
		if (!$dashboards->Count()) {
			$i = 0;
			while ($i < self::$max_dashboards) {
				$area = new MemberDashboard;
				$area->DashboardID = $this->ID;
				$area->write();
				$i++;
			}
		}
	}
	
	public function getFrontEndFields($params = null) {
		$fields = parent::getFrontEndFields($params);
		
		$fields->removeByName('InheritPerms');
		$fields->removeByName('OwnerID');
		$fields->removeByName('PublicAccess');
		
		return $fields;
	}

	public function forTemplate() {
		if (!$this->ColumnLayout) {
			$this->ColumnLayout = '2colRW';
		}
		return $this->renderWith('DashboardPage_' . $this->ColumnLayout);
	}

	public function Link($action='') {
		if ($this->controller) {
			return Controller::join_links($this->controller->Link(), 'board', $this->URLSegment, $action);
		}
		return Controller::join_links(Director::baseURL(), 'dashboard', 'board', $this->URLSegment, $action);
	}
}
