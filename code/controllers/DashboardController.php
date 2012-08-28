<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class DashboardController extends FrontendModelController {
	
	public static $model_class = 'DashboardPage';
	
	static $url_handlers = array(
		'widget/$ID'					=> 'handleDashlet',
		'dashlet/$ID'					=> 'handleDashlet',	// what it should be
		'board/$URLSegment/$MemberID'	=> 'handleBoard',
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
	
	public static $dependencies = array(
		'injector'				=> '%$Injector', 
		'securityContext'		=> '%$SecurityContext',
		'dataService'			=> '%$DataService',
	);

	public $injector;
	public $securityContext;
	
	/**
	 * @var DataService
	 */
	public $dataService;
	
	protected $currentDashboard;

	public function __construct($page=null, $dashboard=null) {
		if ($dashboard && $dashboard instanceof DashboardPage) {
			$this->currentDashboard = $dashboard;
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
		
		if (!$this->currentDashboard) {
			Restrictable::set_enabled(false);
			if (Member::currentUserID()) {
				Restrictable::set_enabled(true);
				$this->currentDashboard = $this->getDashboard();
			}
			Restrictable::set_enabled(true);
			
			
		}
		
		parent::init();
		
		if ($this->currentDashboard && !$this->currentDashboard->checkPerm('View')) {
			if (!Member::currentUserID() && !$this->redirectedTo()) {
				Security::permissionFailure($this, "You must be logged in");
				return;
			}
		}
		

		// add the following to your own page init() to ensure requirements
		// are met - but you're likely to have them anyway.
		// Requirements::javascript('dashboards/javascript/jquery-1.4.3.min.js');
		// Requirements::javascript('dashboards/javascript/jquery-ui-1.8.5.custom.min.js');
		
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-form/jquery.form.js');
		Requirements::javascript(THIRDPARTY_DIR.'/jquery-livequery/jquery.livequery.js');
		Requirements::javascript('dashboards/thirdparty/jquery-cookie/jquery.cookie.js');
		Requirements::javascript('dashboards/javascript/dashboards.js');
		Requirements::javascript('dashboards/javascript/dashboard-dialogs.js');
		Requirements::css('dashboards/css/dashboards.css');
	}

	public static function set_allowed_dashlets($dashlets) {
		self::$allowed_dashlets = $dashlets;
	}

	protected $allowedDashlets = null;
	
	public static function get_allowed_dashlets() {
		return self::$allowed_dashlets;
	}
	
	public function getDashletsList() {
		if ($this->allowedDashlets) {
			return $this->allowedDashlets;
		}

		// prune any that have specific requirements
		foreach (self::$allowed_dashlets as $cls => $title) {
			$clazz = is_int($cls) ? $title : $cls;
			$dummy = singleton($clazz);
			if (!$dummy->canCreate()) {
				unset(self::$allowed_dashlets[$cls]);
			}
		}
		
		$dashlets = self::$allowed_dashlets;
		
		$keys = array_keys($dashlets);
		if (count($keys) && is_int($keys[0])) {
			foreach (array_values($dashlets) as $dashletClass) {
				$title = Object::get_static($dashletClass, 'title');
				if (!$title) {
					FormField::name_to_label($dashletClass);
				}
				$this->allowedDashlets[$dashletClass] = $title; 
			}
		} else {
			$this->allowedDashlets = self::$allowed_dashlets;
		}
		
		return $this->allowedDashlets;
	}

	public function index() {
		$page = $this->currentDashboard ? $this->currentDashboard : $this->getDashboard();
		if (!$page || !$page->exists()) {
			if (!$this->securityContext->getMember()) {
				return Security::permissionFailure($this, _t('DashboardController.USER_REQUIRED', 'You must be logged in to do that'));
			}
			$page = $this->securityContext->getMember()->createDashboard('main', true);
		}
		return $this->customise(array('Dashboard' => $page))->renderWith(array('Dashboard', 'Page'));
	}

	/**
	 * Handler for when the board action is triggered by a nested controller
	 */
	public function board() {
		return $this->index();
	}

	protected function getDashboard($name='main', $memberId = null) {
		if ($memberId) {
			// try and get the page from that user, if there's read access
			// we're deliberately loading the member without permission checks 
			$member = Member::get()->byID($memberId);
			// $member = $this->dataService->memberById($memberId);
			
			if (!$member) {
				throw new PermissionDeniedException('View');
			}
		} else {
			$member = $this->securityContext->getMember();
		}
		if ($member) {
			$page = $member->getNamedDashboard($name);
			if ($page) {
				$page->setController($this);
			}
			return $page;
		}
	}

	/**
	 * Called to update a dashboard structure
	 */
	public function updateDashboard() {
		$dashboardId = (int) $this->request->postVar('dashboard');
		$items = (array) $this->request->postVar('order');
		
		if ($dashboardId) {
			$dashboard = $this->dataService->memberDashboardById($dashboardId);
			if ($dashboard && $dashboard->exists()) {
				
				$dashboard->Widgets()->removeAll();
				if (is_array($items)) {
					foreach ($items as $i => $widgetId) {
						$widget = $this->dataService->dashletById($widgetId);
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
		$fields = new FieldList(
			new TextField('Title', _t('Dashboard.TITLE', 'Title'))
		);

		$actions = new FieldList(new FormAction('adddashboard', _t('Dashboard.ADD_NEW', 'Add Dashboard')));
		$form = new Form($this, 'DashboardForm', $fields, $actions);
		return $form;
	}

	public function adddashboard($data, Form $form) {
		$title = isset($data['Title']) ? $data['Title'] : '';
		if ($title) {
			$page = $this->securityContext->getMember()->createDashboard($title);
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

		$dashlets = $this->getDashletsList();

		asort($dashlets);

		$fields = new FieldList(
			new DropdownField('DashletClass', 'Dashlet', $dashlets, null, null, 'Add dashlet...')
		);

		return new Form($this, 'AddDashletForm', $fields, new FieldList(
			new FormAction('doAddDashlet', _t('Dashboards.ADD_DASHLET', 'Add Dashlet'))
		));
	}

	public function doAddDashlet($data, $form) {
		$classes = $this->getDashletsList();
		$type    = $data['DashletClass'];

		if(isset($classes[$type])) {
			$dashlet = $this->injector->create($type);
			if (!$dashlet->canCreate()) {
				throw new PermissionDeniedException('CreateChildren');
			}
			$dashlet->ParentID = $this->currentDashboard->getDashboard(0)->ID;
			$dashlet->write();
		}

		return $this->redirect($this->currentDashboard->Link());
	}
	
	public function handleBoard($request) {
		$segment = $this->request->param('URLSegment');
		$userId = $this->request->param('MemberID');
		try {
			$board = $this->getDashboard($segment, $userId);
		} catch (PermissionDeniedException $pde) {
			return Security::permissionFailure($this, 'You do not have permission to view that');
		}

		if ($board) {
			// need this call to make sure the params are properly processed
			$this->request->allParams();
			$cls = get_class($this);
			$controller = $this->injector->create($cls, $this->dataRecord, $board);
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
		$dataService = $this->dataService;
		
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
		
		return $this->injector->create($controllerClass, $widget, $this);
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
		
		/* @var $fields FieldList */
		// there's some that we KNOW we don't want
		
		$actions = new FieldList(
			new FormAction('savedashlet', 'Save'),
				new FormAction('deletedashlet', 'Delete')
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
	
	public function deletedashlet($data, Form $form) {
		$dashlet = $this->getRequestedDashlet();

		if ($dashlet->checkPerm('Delete')) {
			$dashlet->delete();

			$this->response->addHeader('Content-Type', 'application/json');
			$this->response->setBody('{ "success": true }');
			return $this->response;
		}
		
		throw new PermissionDeniedException('Delete');
	}

	public function loaddashlet() {
		$dashlet = $this->getRequestedDashlet();
		$controller = $dashlet->class.'_Controller';
		$renderObj = $dashlet;
		if (class_exists($controller)) {
			$renderObj = $this->injector->create($controller, $dashlet, $this);
		}
		
		return $renderObj->renderWith('DashletLayout');
		return $dashlet->renderWith(array_reverse(ClassInfo::ancestry($dashlet->class)));
	}
	
	protected function getRequestedDashlet() {
		$dashletId = (int) $this->request->requestVar('DashletID');
		if (!$dashletId) {
			throw new Exception("Invalid $dashletId in request");
		}
		
		$dashlet = $this->dataService->dashletById($dashletId);
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
