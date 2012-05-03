<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class DashboardUser extends DataObjectDecorator {
	
	public static $default_dashlets = array(
		
	);
	
	public function extraStatics() {
		return array(
			'db'		=> array(
			),

			'has_many'	=> array(
				'Dashboards'			=> 'DashboardPage'
			),
		);
	}
	
	public function myDashboards() {
		return singleton('DataService')->getAllDashboardPage('"OwnerID" = '.$this->owner->ID);
	}
	
	public function sharedDashboards() {
		return singleton('DataService')->getAllDashboardPage('"OwnerID" <> '.$this->owner->ID);
	}

	public function getNamedDashboard($segment) {
		$dashboard = singleton('DataService')->getOneDashboardPage('"OwnerID" = '.$this->owner->ID.' AND "URLSegment" = \''.Convert::raw2sql($segment).'\'');
		return $dashboard;
	}

	public function createDashboard($name, $createDefault = false) {
		$url = preg_replace('/ +/', '-', trim($name)); // Replace any spaces
		$url = preg_replace('/[^A-Za-z0-9.+_\-]/', '', $url); // Replace non alphanumeric characters
		$url = strtolower($url);

		$existing = $this->getNamedDashboard($url);
		if ($existing) {
			return $existing;
		}

		$dashboard = new DashboardPage;
		$dashboard->URLSegment = $url;
		$dashboard->Title = trim($name);
		$dashboard->OwnerID = $this->owner->ID;
		$dashboard->write();
		
		if ($createDefault) {
			foreach (self::$default_dashlets as $dbid => $dashlets) {
				foreach ($dashlets as $type) {
					if (class_exists($type)) {
						$dashlet = new $type;
						$dashboard = $dashboard->getDashboard($dbid);
						if ($dashboard && $dashlet->canCreate()) {
							$dashlet->ParentID = $dashboard->ID;
							$dashlet->write();
						}
					}
				}
			}
		}
		return $dashboard;
	}
}
