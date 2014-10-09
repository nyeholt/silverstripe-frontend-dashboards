<?php

/**
 * A dashboard page is a url identifiable dashboard in the system that a user can
 * customise to their whim. This is NOT inherited from SS's SiteTree/Page, 
 * but is a standalone type. 
 * 
 * These are accessed in the context of a DashboardController; that dashboard can be the controller
 * to a SilverStripe Page object, but doesn't need to be (ie this can 
 * operate via direct controller requests). 
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class DashboardPage extends DataObject {

	public static $layouts = array(
		'dynamic'	=> 'Dynamic',
//		'1col'   => 'One Column',
//		'2colLW' => 'Two Columns - Left Wide',
//		'2colRW' => 'Two Columns - Right Wide',
//		'3col'   => 'Three Columns'
	);

	public static $default_layout = 'dynamic';

	public static $max_dashboards = 3;
	public static $db = array(
		'Title'			=> 'Varchar(128)',
		'URLSegment'	=> 'Varchar(64)',
		'Layout'		=> 'Varchar(15)',
	);
	
	public static $defaults = array(
		'ColumnLayout'			=> 'dynamic'
	);

	public static $has_many = array(
		'Dashboards'			=> 'MemberDashboard',
	);

	public static $extensions = array(
		'Restrictable',
	);

	private $controller;

	public function getController() {
		return $this->controller;
	}

	/**
	 * @param type $controller 
	 */
	public function setController($controller) {
		$this->controller = $controller;
	}
	
	public function __construct($record = null, $isSingleton = false, $model = null) {
		parent::__construct($record, $isSingleton, $model);
	}

	public function getDashboard($index) {
		$dashboards = $this->Dashboards();

		$board = $dashboards->offsetGet($index);
		$board->parent = $this;

		return $board;
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		
		if ($this->isChanged('URLSegment')) {
			$this->URLSegment = URLSegmentFilter::create()->filter($this->URLSegment);
		}
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
		
		$fields->replaceField('Layout', $dd = DropdownField::create('Layout', null, self::$layouts));
		$dd->setHasEmptyDefault(true);


		$fields->removeByName('InheritPerms');
		$fields->removeByName('OwnerID');
//		$fields->removeByName('PublicAccess');
		
		return $fields;
	}

	public function forTemplate() {
		if($this->Layout) {
			$layout = $this->Layout;
		} else {
			$layout = self::$default_layout;
		}

		return $this->renderWith("DashboardPage_$layout");
	}

	public function Link($action='') {
		$identifier = Member::get_unique_identifier_field();
		$identifier = $this->Owner()->$identifier;
			
		if ($this->controller) {
			return Controller::join_links($this->controller->Link(), 'user', $identifier, $this->URLSegment, $action);
		}
		return Controller::join_links(Director::baseURL(), 'dashboard', 'user', $identifier, $this->URLSegment, $action);
	}
}
