<?php

/**
 * Arbitrary listings of data objects
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class ListingDashlet extends Dashlet {
	static $title = "Listing";
	static $cmsTitle = "Simple Listing";
	static $description = "List objects";

	public static $db = array(
		'ListType'			=> 'Varchar',
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$types = ClassInfo::subclassesFor('DataObject');
		$types = array_combine($types, $types);
		ksort($types);
		$fields->push(new DropdownField('ListType', _t('ListingDashlet.LIST_TYPE', 'List Items of Type'), $types));
		return $fields;
	}
	
	public function getDashletFields() {
		$fields = parent::getDashletFields();
		$types = ClassInfo::subclassesFor('DataObject');
		$types = array_combine($types, $types);
		ksort($types);
		$fields->push(new DropdownField('ListType', _t('ListingDashlet.LIST_TYPE', 'List Items of Type'), $types));
		return $fields;
	}
	
	public function Items() {
		if ($this->ListType) {
			return singleton('DataService')->getAll($this->ListType, $filter = "", $sort = "", $join = "", $limit = "0, 50");
		}
	}
	
	public function AddLink() {
		if ($this->ListType) {
			return Controller::join_links(strtolower($this->ListType), 'edit');	
		}
	}
}


class ListingDashlet_Controller extends Dashlet_Controller {
	
}