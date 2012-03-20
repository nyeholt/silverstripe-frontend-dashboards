<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class DashboardController extends FrontendModelController {
	
	protected $currentDashboard;
	
	public static $model_class = 'DashboardPage';
	
	static $url_handlers = array(
		'widget/$ID/$Action'			=> 'handleDashlet',
		'dashlet/$ID/$Action'			=> 'handleDashlet',	// what it should be
		'board/$URLSegment'				=> 'handleBoard',
	);

	public static $allowed_actions = array(
		'index',
		'board',
		'handleDashlet',
		'handleBoard',
		'adddashlet',
		'AddDashletForm',
		'updateDashboard',
		'DashboardForm',
		'editorfor',
		'EditDashletForm',
		'loaddashlet',
		'deletedashlet'
	);

	private static $allowed_dashlets = array();
	
	public function __construct($page=null, $dashboard=null) {
		if ($dashboard && $dashboard instanceof DashboardPage) {
			$this->currentDashboard = $dashboard;
		} else {
			Restrictable::set_enabled(false);
			if (Member::currentUserID()) {
				Restrictable::set_enabled(true);
				$this->currentDashboard = $this->getDashboard();
			}
			Restrictable::set_enabled(true);
		}
		
		if ($this->currentDashboard) {
			$this->currentDashboard->setController($this);
		}

		if (!count(self::$allowed_dashlets)) {
			$widgets = ClassInfo::subclassesFor('Dashlet');
			array_shift($widgets);
			self::$allowed_dashlets = array_combine($widgets, $widgets);
		}

		parent::__construct($page);
	}

	/**
	 * Get the currnet dashboard that the user is viewing
	 */
	public function getCurrentDashboard() {
		return $this->currentDashboard;
	}
	
	public function init() {
		parent::init();

		// add the following to your own page init() to ensure requirements
		// are met - but you're likely to have them anyway.
		// Requirements::javascript('dashboards/javascript/jquery-1.4.3.min.js');
		// Requirements::javascript('dashboards/javascript/jquery-ui-1.8.5.custom.min.js');
		
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-form/jquery.form.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('dashboards/thirdparty/jquery-cookie/jquery.cookie.js');
		Requirements::javascript('dashboards/javascript/dashboards.js');
		Requirements::css('dashboards/css/dashboards.css');
	}
	
	public static function set_allowed_dashlets($dashlets) {
		$keys = array_keys($dashlets);
		if (count($keys) && is_int($keys[0])) {
			foreach (array_values($dashlets) as $dashletClass) {
				$title = Object::get_static($dashletClass, 'title');
				if (!$title) {
					FormField::name_to_label($dashletClass);
				}
				self::$allowed_dashlets[$dashletClass] = $title; 
			}
		} else {
			self::$allowed_dashlets = $dashlets;
		}
	}
	
	public static function get_allowed_dashlets() {
		return self::$allowed_dashlets;
	}
	
	
	public function index() {
		$page = $this->currentDashboard ? $this->currentDashboard : $this->getDashboard();
		if (!$page || !$page->exists()) {
			$page = singleton('SecurityContext')->getMember()->createDashboard('main', true);
		}
		return $this->customise(array('Dashboard' => $page))->renderWith(array('Dashboard', 'Page'));
	}
	
	/**
	 * Handler for when the board action is triggered by a nested controller
	 */
	public function board() {
		return $this->index();
	}

	protected function getDashboard($name='main') {
		$page = singleton('SecurityContext')->getMember()->getNamedDashboard($name);
		return $page;
	}

	/**
	 * Called to update a dashboard structure
	 */
	public function updateDashboard() {
		$dashboardId = (int) $this->request->postVar('dashboard');
		$items = (array) $this->request->postVar('order');
		
		if ($dashboardId) {
			$dashboard = singleton('DataService')->memberDashboardById($dashboardId);
			if ($dashboard && $dashboard->exists()) {
				
				$dashboard->Widgets()->removeAll();
				if (is_array($items)) {
					foreach ($items as $i => $widgetId) {
						$widget = singleton('DataService')->dashletById($widgetId);
						if ($widget) {
							$widget->ParentID = $dashboard->ID;
							$widget->Sort = $i+1;	// need +1 here so there's no 0 sort val, otherwise onbeforewrite sets it automatically.
							$widget->write();
						}
					}
				}
			}
		}
	}

	public function DashboardForm() {
		$fields = new FieldSet(
			new TextField('Title', _t('Dashboard.TITLE', 'Title'))
		);

		$actions = new FieldSet(new FormAction('adddashboard', _t('Dashboard.ADD_NEW', 'Add Dashboard')));
		$form = new Form($this, 'DashboardForm', $fields, $actions);
		return $form;
	}

	public function adddashboard($data, Form $form) {
		$title = isset($data['Title']) ? $data['Title'] : '';
		if ($title) {
			$page = singleton('SecurityContext')->getMember()->createDashboard($title);
			$this->redirect($page->Link());
			return;
		} else {
			$form->sessionMessage("Failed creating new dashboard", "bad");
		}
		$this->redirect($this->Link());
	}

	public function adddashlet() {
		return $this->AddDashletForm()->forAjaxTemplate();
	}

	public function AddDashletForm() {
		$dashlets = array();

		foreach(self::$allowed_dashlets as $class) {
			$dashlets[$class] = $class::$title == 'Widget Title' ? $class : $class::$title;
		}

		asort($dashlets);

		$fields = new FieldSet(
			new DropdownField('DashletClass', 'Dashlet', $dashlets)
		);

		return new Form($this, 'AddDashletForm', $fields, new FieldSet(
			new FormAction('doAddDashlet', _t('Dashboards.ADD_DASHLET', 'Add Dashlet'))
		));
	}

	public function doAddDashlet($data, $form) {
		$classes = ClassInfo::subclassesFor('Widget');
		$type    = $data['DashletClass'];

		if(in_array($type, $classes)) {
			$dashlet = new $type();
			$dashlet->ParentID = $this->currentDashboard->getDashboard(0)->ID;
			$dashlet->write();
		}

		return $this->redirect($this->currentDashboard->Link());
	}
	
	public function handleBoard($request) {
		$segment = $this->request->param('URLSegment');
		$board = $this->getDashboard($segment);
		if ($board) {
			$this->request->allParams();
			$controller = new DashboardController($this->dataRecord, $board);
			return $controller;
		}
		return $this->httpError(404, "Board $segment does not exist");
	}

	/**
	 * Handles widgets attached to a page through one or more {@link WidgetArea} elements.
	 * Iterated through each $has_one relation with a {@link WidgetArea}
	 * and looks for connected widgets by their database identifier.
	 * Assumes URLs in the following format: <URLSegment>/widget/<Widget-ID>.
	 * 
	 * @return RequestHandler
	 */
	function handleDashlet() {
		$SQL_id = $this->request->param('ID');
		if(!$SQL_id) return false;

		// find widget
		$dataService = singleton('DataService');
		
		$widget = $dataService->dashletById($SQL_id);
		if (!$widget) {
			throw new Exception("Invalid widget #$SQL_id");
		}

		// find controller
		$controllerClass = '';
		foreach(array_reverse(ClassInfo::ancestry($widget->class)) as $widgetClass) {
			$controllerClass = "{$widgetClass}_Controller";
			if(class_exists($controllerClass)) break;
		}
		if(!$controllerClass) user_error(
			sprintf('No controller available for %s', $widget->class),
			E_USER_ERROR
		);

		return new $controllerClass($widget);
	}
	
	/**
	 * Gets an editing form for the particular widget
	 */
	public function editorfor() {
		return $this->EditDashletForm()->forTemplate();
	}

	public function EditDashletForm() {
		$dashlet = $this->getRequestedDashlet();
		
		$fields = $dashlet->getDashletFields();
		$fields->push(new HiddenField('DashletID', '', $dashlet->ID));
		
		/* @var $fields FieldSet */
		// there's some that we KNOW we don't want
		
		$actions = new FieldSet(
			new FormAction('savedashlet', 'Save')
		);
		
		$form = new Form($this, 'EditDashletForm', $fields, $actions);
		$form->loadDataFrom($dashlet);
		return $form;
	}
	
	public function savedashlet($data, Form $form) {
		$dashlet = $this->getRequestedDashlet();
		
		if ($dashlet->checkPerm('Write')) {
			$form->saveInto($dashlet);
			$dashlet->write();
			
			return $this->loaddashlet();
		}
	}

	public function deletedashlet() {
		$dashlet = $this->getRequestedDashlet();

		if ($dashlet->checkPerm('Delete')) {
			$dashlet->delete();

			$this->response->addHeader('Content-Type', 'application/json');
			$this->response->setBody('{ "success": true }');
			return $this->response;
		}
	}

	public function loaddashlet() {
		$dashlet = $this->getRequestedDashlet();
		$controller = $dashlet->class.'_Controller';
		$renderObj = $dashlet;
		if (class_exists($controller)) {
			$renderObj = new $controller($dashlet);
		}
		
		return $renderObj->renderWith('DashletLayout');
		return $dashlet->renderWith(array_reverse(ClassInfo::ancestry($dashlet->class)));
	}
	
	protected function getRequestedDashlet() {
		$dashletId = (int) $this->request->requestVar('DashletID');
		if (!$dashletId) {
			throw new Exception("Invalid $dashletId in request");
		}
		
		$dashlet = singleton('DataService')->dashletById($dashletId);
		if (!$dashlet) {
			throw new Exception("Invalid dashlet #$dashletId");
		}
		return $dashlet;
	}

	public function Link($action='') {
		if ($this->currentDashboard && $this->currentDashboard->URLSegment != 'main') {
			return $this->currentDashboard->Link($action);
		}
		
		return $this->dataRecord->Link($action);
	}
}
