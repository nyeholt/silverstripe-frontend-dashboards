<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class MemberDashboard extends WidgetArea {

	public $template = __CLASS__;
	public $parent;

	public static $has_one = array(
		'Dashboard'				=> 'DashboardPage',
	);

	public static $extensions = array(
		'Restrictable'
	);
	
	public function addDashlet(Dashlet $dashlet) {
		$dashlet->ParentID = $this->ID;
		
		// get all dashlets and figure out a posX and posY
		$all = $this->Widgets();
		
		$maxY = 0;

		foreach ($all as $d) {
			if ($d->PosY > $maxY) {
				$maxY = $d->PosY + 1;
			}
		}
		
		$dashlet->PosY = $maxY > 1 ? $maxY : 1;
		$dashlet->write();
	}

	public function WidgetControllers() {
		$set   = new ArrayList();
		$items = $this->ItemsToRender();

		foreach($items as $dashlet) {
			$class = '';
	
			foreach(array_reverse(ClassInfo::ancestry($dashlet->class)) as $class) {
				if(class_exists($class = "{$class}_Controller")) break;
			}

			$controller = Injector::inst()->create($class, $dashlet, $this->parent->getController());
			$controller->init();

			$set->push($controller);
		}

		return $set;
	}

	public function permissionSource() {
		return $this->Dashboard();
	}
}
