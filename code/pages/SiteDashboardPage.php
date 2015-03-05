<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SiteDashboardPage extends Page {
	
}

class SiteDashboardPage_Controller extends DashboardController {

	private static $dependencies = array(
		'dataService'		=> '%$DataService',
	);
	
	public function init() {
		parent::init();
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(FRAMEWORK_DIR . '/thirdparty/jquery-ui/jquery-ui.js'); // -1.8.5.custom.min.js');
		Requirements::css('frontend-dashboards/thirdparty/aristo/aristo.css');
		
		if (class_exists('WebServiceController')) {
			Requirements::javascript('webservices/javascript/webservices.js');
		}
	}
	

	/**
	 * Overridden to make sure the dashboard page is attached to the correct controller
	 * @return type 
	 */
	protected function getRecord() {
		$id = (int) $this->request->param('ID'); 
		if (!$id) {
			$id = (int) $this->request->requestVar('ID');
		}
		if ($id) {
			$item = $this->dataService->byId($this->stat('model_class'), $id);
			if ($item instanceof DashboardPage) {
				$item->setController($this);
			}

			return $item;
		}
	}
	
	public function Link($action = null) {
		$dashboard = $this->currentDashboard;

		if($dashboard && $dashboard->URLSegment != 'main') {
			$identifier = Member::get_unique_identifier_field();
			$identifier = $dashboard->Owner()->$identifier;
			
			$segment = $dashboard->URLSegment ? $dashboard->URLSegment : 'main';
			
			return Controller::join_links(
				$this->data()->Link(true), 'board', $segment, $dashboard->Owner()->ID, $action
			);
		} else {
			return $this->data()->Link($action ? $action : true);
		}
	}
	
}
