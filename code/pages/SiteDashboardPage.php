<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SiteDashboardPage extends Page {
	
}

class SiteDashboardPage_Controller extends DashboardController {

	public static $allowed_actions = array(
		
	);

	public function init() {
		parent::init();
		Requirements::javascript('dashboards/javascript/jquery-ui-1.8.5.custom.min.js');
		Requirements::css('dashboards/thirdparty/aristo/aristo.css');
	}
	
	public function Link($action = null) {
		$dashboard = $this->currentDashboard;
		$base      = $this->data()->Link($action ? $action : true);

		if($dashboard && $dashboard->URLSegment != 'main') {
			return Controller::join_links(
				$this->data()->Link(true), 'board', $dashboard->URLSegment, $action
			);
		} else {
			return $this->data()->Link($action ? $action : true);
		}
	}

}