<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class UserProfileDashlet extends Dashlet {
	public static $title = 'User profile';
	
	public function canCreate($member = null) {
		if (!$member) {
			$member = Member::currentUser();
		}
		return $member->ID > 0;
	}
}

class UserProfileDashlet_Controller extends Dashlet_Controller {
	public function UpdateForm() {
		$member = Member::currentUser();
		if (!$member) {
			return '';
		}
		// if there's a member profile page availble, use it
		$filter = array();
		if (class_exists('Multisites')) {
			$filter = array('SiteID' => Multisites::inst()->getCurrentSiteId());
		}
		
		$profilePage = MemberProfilePage::get()->filter($filter)->first();
		if ($profilePage) {
			$controller = MemberProfilePage_Controller::create($profilePage);
			$form = $controller->ProfileForm();
			$form->addExtraClass('ajax-form');
			$form->loadDataFrom($member);
			return $form;
		}

		return;
	}
}