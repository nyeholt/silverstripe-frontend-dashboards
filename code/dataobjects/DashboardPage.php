<?php

/**
 * A dashboard page is a url identifiable dashboard in the system that a user can
 * customise to their whim. This is NOT inherited from SS's SiteTree/Page, 
 * but is a standalone type. 
 * 
 * These are accessed in the context of a DashboardController; that dashboard can be the controller
 * to a SilverStripe Page object, but doesn't need to be (ie this can 
 * operate via direct controller requests). 
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class DashboardPage extends DataObject
{

    private static $layouts = array(
        'dynamic'    => 'Dynamic',
		'2colLW' => 'Two Columns - Left Wide',
		'2colRW' => 'Two Columns - Right Wide',
		'3col'   => 'Three Columns'
    );

    private static $default_layout = 'dynamic';

    public static $max_dashboards = 3;
    private static $db = array(
        'Title'             => 'Varchar(128)',
        'URLSegment'        => 'Varchar(64)',
        'Layout'            => 'Varchar(15)',
    );
    
    private static $defaults = array(
        'ColumnLayout'            => 'dynamic'
    );

    private static $has_many = array(
        'Dashboards'            => 'MemberDashboard',
    );

    private static $extensions = array(
        'Restrictable',
    );
    
    private static $summary_fields = array(
        'Title', 'Owner.Email',
    );

    private $controller;

    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param type $controller 
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }
    
    public function __construct($record = null, $isSingleton = false, $model = null)
    {
        parent::__construct($record, $isSingleton, $model);
    }

    public function getDashboard($index)
    {
        $dashboards = $this->Dashboards();
        if (!$dashboards->count()) {
            $dashboards = $this->createDefaultBoards();
        }
        $board = $dashboards->offsetGet($index);
        $board->parent = $this;

        return $board;
    }
    
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        
        if ($this->isChanged('URLSegment')) {
            $this->URLSegment = URLSegmentFilter::create()->filter($this->URLSegment);
        }
        
        if (!$this->URLSegment) {
            $this->URLSegment = URLSegmentFilter::create()->filter($this->Title);
        }
        if (!$this->URLSegment) {
            $this->URLSegment = 'main';
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        $dashboards = $this->Dashboards();
        if (!$dashboards->Count()) {
            $this->createDefaultBoards();
        }
    }
    
    public function onBeforeDelete() 
    {
        parent::onBeforeDelete();
        
        $dashboards = $this->Dashboards();
        if ($dashboards->count()) {
            foreach ($dashboards as $board) {
                $board->delete();
            }
        }
    }
    
    protected function createDefaultBoards() {
        $i = 0;
        $boards = ArrayList::create();
        while ($i < self::$max_dashboards) {
            $area = MemberDashboard::create();
            $area->DashboardID = $this->ID;
            $area->write();
            $boards->push($area);
            $i++;
        }
        return $boards;
    }
    
    public function getFrontEndFields($params = null)
    {
        $fields = parent::getFrontEndFields($params);
        
        $fields->replaceField('Layout', $dd = DropdownField::create('Layout', null, $this->config()->layouts));
        $dd->setHasEmptyDefault(true);


        $fields->removeByName('InheritPerms');
        $fields->removeByName('OwnerID');
//		$fields->removeByName('PublicAccess');

        return $fields;
    }

    public function forTemplate()
    {
        if ($this->Layout) {
            $layout = $this->Layout;
        } else {
            $layout = $this->config()->default_layout;
        }
        return $this->renderWith("DashboardPage_$layout");
    }
    
    public function DashboardLayout() {
        return $this->Layout ? $this->Layout : $this->config()->default_layout;
    }

    public function Link($action='')
    {
        $identifier = Member::get_unique_identifier_field();
        $identifier = $this->Owner()->$identifier;
            
        if ($this->controller) {
            return Controller::join_links($this->controller->Link(), 'user', $identifier, $this->URLSegment, $action);
        }
        return Controller::join_links(Director::baseURL(), 'dashboard', 'user', $identifier, $this->URLSegment, $action);
    }
}
