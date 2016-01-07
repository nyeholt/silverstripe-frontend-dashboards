<?php

/**
 * A widget specifically for use on the frontend
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class Dashlet extends Widget
{
    private static $db = array(
        'Title'                    => 'Varchar',
        'Width'                    => 'Int',
        'Height'                => 'Int',
        'PosX'                    => 'Int',
        'PosY'                    => 'Int',
        'ExtraClasses'            => 'MultiValueField'
    );

    private static $defaults = array(
        'Width'            => 3,
        'Height'        => 2,
        'PosX'            => 1,
        'PosY'            => 1,
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Title) {
            $this->Title = Config::inst()->get($this->class, 'title');
        }
    }

    public function Title()
    {
        return $this->dbObject('Title');
    }

    /**
     * Gets the fields used for editing this dashlet on the frontend
     *
     * @return FieldSet 
     */
    public function getDashletFields()
    {
        /**
        *	if you want to use jQuery color picker instead of HTML5
        *	<input type='color' />, uncomment out the lined which add
        *	the extra class, and comment out the lines which are setting
        *	the attribute to type => color
        */
        
        $extraClasses = MultiValueTextField::create('ExtraClasses');

        $fields = new FieldList(new TextField('Title', _t('Dashlet.TITLE', 'Title')), $extraClasses);
        $this->extend('updateDashletFields', $fields);
        return $fields;
    }
    
    public function ExtraDashletClasses()
    {
        $classes = $this->ExtraClasses->getValues();
        if ($classes && count($classes)) {
            return implode(' ', $classes);
        }
    }

    public function permissionSource()
    {
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
    protected function requiredPermission()
    {
        return 'View';
    }
    
    /**
     * Can this dashlet be created by the current user?
     * 
     * @param type $member
     * @return type 
     */
    public function canCreate($member=null)
    {
        if (!$member) {
            $member = singleton('SecurityContext')->getMember();
        }
        
        if ($member) {
            // check dashboard controller's allowed_dashlets
            $allowed = Config::inst()->get('DashboardController', 'allowed_dashlets');
            if (in_array(get_class($this), $allowed)) {
                return true;
            }
            
            $config = SiteConfig::current_site_config();
            $required = $this->requiredPermission();
            if ($config->hasMethod('checkPerm') && $config->checkPerm($required)) {
                return true;
            }
        }
        
        
        return parent::canCreate($member);
    }
}

class Dashlet_Controller extends WidgetController
{
    protected $parentDashboardPage = null;
    private static $allowed_actions = array(
        'save'
    );
    private static $url_handlers = array(
        'widget/$ID',
        'dashlet/$ID',
        'widget/$ID/save',
        'dashlet/$ID/save'
    );
    
    /**
     * Store the page we were attached to
     * 
     * @param type $widget
     * @param type $parent 
     */
    public function __construct($widget = null, $parent = null)
    {
        parent::__construct($widget);
        $this->parentDashboardPage = $parent;
    }
    
    /**
     * Overloaded from {@link Widget->Content()}
     * to allow for controller/form linking.
     * 
     * @return string HTML
     */
    public function Content()
    {
        $templates = array_reverse(array_values(ClassInfo::ancestry($this->widget->class)));
        return $this->renderWith($templates);
    }
    
    /**
    *	Called on every instance of resize.stop and draggable.stop in dashboards.js
    *	Takes the parameters and saves them to dashlet of the ID given.
    *	Values are automatically escaped.
    */
    public function save()
    {
        //Note : Gridster uses weird names... size_x? Why just not width, Argh...
        //			Admittedly, col and row makes sense since it's essentially
        //			using cells to align up objects.
        $obj = self::get()->byID($this->request->param('ID'));
        $obj->PosX = $this->request->postVar('col');
        $obj->PosY = $this->request->postVar('row');
        $obj->Width = $this->request->postVar('size_x');
        $obj->Height = $this->request->postVar('size_y');
        $obj->write();
    }

    /**
     * Overridden to avoid infinite loop bug if $this === Controller::curr()
     */
    public function Link($action = null)
    {
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
