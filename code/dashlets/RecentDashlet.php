<?php

/**
 * Recent Pages listing.
 *
 * @author Nathan Glasl <nathan@silverstripe.com.au>
 */

class RecentDashlet extends Dashlet {
	static $title = "Recently Modified";
	static $cmsTitle = "Recently Modified Pages";
	static $description = "List Pages";

	public static $db = array(
		'ListType'	=> 'Varchar'
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$types = ClassInfo::subclassesFor('Page');
		$types = array_combine($types, $types);
		ksort($types);
		$fields->push(new DropdownField('ListType', _t('RecentDashlet.LIST_TYPE', 'List Items of Type'), $types));
		return $fields;
	}
	
	public function getDashletFields() {
		$fields = parent::getDashletFields();
		$types = ClassInfo::subclassesFor('Page');
		$types = array_combine($types, $types);
		ksort($types);
		$fields->push(new DropdownField('ListType', _t('RecentDashlet.LIST_TYPE', 'List Items of Type'), $types));
		return $fields;
	}

	public function Items() {
		if ($this->ListType) {
			return singleton('DataService')->getAll($this->ListType, $filter = "", $sort = "LastEdited DESC", $join = "", $limit = "0, 10");
		}
	}
}


class RecentDashlet_Controller extends Dashlet_Controller {
	
}