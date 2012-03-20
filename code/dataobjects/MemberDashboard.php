<?php

/**
 * Description of Dashboard
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class MemberDashboard extends WidgetArea {
	public $template = __CLASS__;
	
	public static $has_one = array(
		'Dashboard'				=> 'DashboardPage',
	);

	public static $extensions = array(
		'Restrictable'
	);
	
	public function permissionSource() {
		return $this->Dashboard();
	}
}
