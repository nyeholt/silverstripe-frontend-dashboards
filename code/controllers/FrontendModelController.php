<?php

/**
 * A Model controller for working with objects on the frontend
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class FrontendModelController extends Page_Controller {
	
	public static $allowed_actions = array(
		'view',
		'index',
		'all',
		'edit',
		'save',
		'CreateForm',
		'EditForm',
	);

	public static $url_handlers = array(
		'$Action//$ID/$OtherID'	=> 'handleAction',
	);

	protected $record;
	
	public function init() {
		parent::init();
	}

	public function index() {
		$company = Member::currentUser()->CompanyID;
		if ($company) {
			$this->redirect('company/view/'.$company);
		} else {
			$this->redirect('');
		}
	}
	
	public function handleAction($request, $action) {
		$this->record = $this->getRecord();
		if ($this->request->param('ID') && is_int($this->request->param('ID')) && !$this->record) {
			Security::permissionFailure($this, "You do not have permission to that");
			return;
		}
		return parent::handleAction($request, $action);
	}

	public function view() {
		if ($this->record) {
			return $this->customise($this->record)->renderWith(array($this->stat('model_class'), 'Page'));
		}
		
		throw new Exception("Invalid record");
	}
	
	public function all() {
		if (!Member::currentUserID()) {
			Security::permissionFailure($this, "You must be logged in");
			return;
		}
		return $this->renderWith(array($this->stat('model_class').'_list', 'Page'));
	}

	public function edit() {
		if (!Member::currentUserID()) {
			Security::permissionFailure($this, "You must be logged in");
			return;
		}
		if ($this->record && !$this->record->canEdit()) {
			Security::permissionFailure($this, "You must be logged in to edit that");
			return;
		}
		if($this->request->isAjax()) {
			return $this->Form()->forAjaxTemplate();
		} else {
			if($this->record) {
				$controller = $this->customise($this->record);
			} else {
				$controller = $this;
			}

			return $controller->renderWith(array(
				$this->stat('model_class') . '_edit', 'FrontendRecord_edit', 'Page'
			));
		}
	}

	public function Form() {
		return $this->EditForm();
	}

	public function EditForm() {
		$object = $this->getRecord();
		if (!$object) {
			$object = singleton($this->stat('model_class'));
		}

		$fields = $object->getFrontEndFields();
		
		$actions = new FieldList(
			$button = new FormAction('save', _t('Dashboards.SAVE', 'Save'))
		);
		$button->addExtraClass('button');

		$validator = new RequiredFields('Title');

		$form = new Form($this, 'EditForm', $fields, $actions, $validator);
		
		if ($this->record) {
			$form->Fields()->push(new HiddenField('ID', '', $this->record->ID));
			$form->loadDataFrom($this->record);
		}

		return $form;
	}

	public function save($data, Form $form, $request) {
		
		// if there's no existing id passed in the request, we must assume we're
		// creating a new one, so chec kthat it doesn't exist already.
		if (!$this->record) {
			if (!Member::currentUserID()) {
				Security::permissionFailure($this, "You must be logged in");
				return;
			}
			$existing = singleton('DataService')->getOne($this->stat('model_class'),array('Title' => $this->request->requestVar('Title')));
			if ($existing) {
				throw new Exception("Record already exists");
			}
			
			$cls = $this->stat('model_class');
			$this->record = new $cls;
		}

		if (!$this->record->canEdit()) {
			return $this->httpError(403);
		}

		$form->saveInto($this->record);
		$this->record->write();

		//$this->redirect($this->record->Link());
		$this->redirectBack();
	}

	public function Record() {
		return $this->record;
	}
	
	protected function getRecord() {
		$id = (int) $this->request->param('ID'); 
		if (!$id) {
			$id = (int) $this->request->requestVar('ID');
		}
		if ($id) {
			return singleton('DataService')->byId($this->stat('model_class'), $id);
		}
	}
	
	public function Link($action='') {
		return Controller::join_links(Director::baseURL(), strtolower($this->stat('model_class')), $action);
	}
	
	protected function checkSecurityID($request) {
		$secId = $request->postVar(SecurityToken::inst()->getName());
		if ($secId != SecurityToken::inst()->getValue()) {
			Security::permissionFailure($this);
			return false;
		}
		return true;
	}
}
