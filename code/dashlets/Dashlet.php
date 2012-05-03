<?php

/**
 * A widget specifically for use on the frontend
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class Dashlet extends Widget {
	public static $db = array(
		'Title'					=> 'Varchar'
	);

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->Title) {
			$this->Title = Object::get_static($this->class, 'title');
		}
	}

	public function Title() {
		return $this->dbObject('Title');
	}

	/**
	 * Gets the fields used for editing this dashlet on the frontend
	 *
	 * @return FieldSet 
	 */
	public function getDashletFields() {
		$fields = new FieldSet(new TextField('Title', _t('Dashlet.TITLE', 'Title')));
		$this->extend('updateDashletFields', $fields);
		return $fields;
	}

	public function permissionSource() {
		if ($this->ParentID) {
			// explicitly load as a member dashboard so correct permission checks are
			// used (widget area doesn't have restrictable applied to it!)
			$parent = DataObject::get_by_id('MemberDashboard', $this->ParentID);
			return $parent;
		}
	}

	/**
	 * The permission needed for a user to be able to create and view this dashlet
	 * to the site config object 
	 */
	protected function requiredPermission() {
		return 'View';
	}
	
	/**
	 * Can this dashlet be created by the current user?
	 * 
	 * @param type $member
	 * @return type 
	 */
	public function canCreate($member=null) {
		
		$config = SiteConfig::current_site_config();
		
		$required = $this->requiredPermission();
		if ($config->checkPerm($required)) {
			return true;
		}
		
		return parent::canCreate($member);
	}
}

class Dashlet_Controller extends Widget_Controller {
	protected $parentDashboardPage = null;
	
	/**
	 * Store the page we were attached to
	 * 
	 * @param type $widget
	 * @param type $parent 
	 */
	public function __construct($widget = null, $parent = null) {
		parent::__construct($widget);
		$this->parentDashboardPage = $parent;
	}
	
	/**
	 * Overloaded from {@link Widget->Content()}
	 * to allow for controller/form linking.
	 * 
	 * @return string HTML
	 */
	function Content() {
		$templates = array_reverse(array_values(ClassInfo::ancestry($this->widget->class)));
		return $this->renderWith($templates);
	}
	
	/**
	 * Overridden to avoid infinite loop bug if $this === Controller::curr()
	 */
	public function Link($action = null) {
		$curr = Controller::curr();
		if ($this->parentDashboardPage) {
			return Controller::join_links($this->parentDashboardPage->Link(), 'widget', ($this->widget ? $this->widget->ID : null), $action);
		}
		if ($curr != $this) {
			$pageLink = Controller::curr()->Link();
			return Controller::join_links($pageLink, 'widget', ($this->widget ? $this->widget->ID : null), $action);
		}
		
		return Controller::join_links('widget', ($this->widget ? $this->widget->ID : null), $action);
	}
}
