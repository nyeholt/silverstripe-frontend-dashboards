<?php

/**
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class DashboardUser extends DataExtension
{
    
    private static $default_dashlets = array();
    
    private static $default_layout = array();
    
    public $dataService;
    public $securityContext;
    
    public static $dependencies = array(
        'dataService'        => '%$DataService',
        'securityContext'    =>    '%$SecurityContext'
    );
    
    public function gravatarHash()
    {
        return md5(strtolower(trim($this->owner->Email)));
    }

    public function myDashboards()
    {
        return $this->dataService->getAllDashboardPage('"OwnerID" = '.$this->owner->ID);
    }
    
    public function sharedDashboards()
    {
        return $this->dataService->getAllDashboardPage('"OwnerID" <> '.$this->owner->ID);
    }

    public function getNamedDashboard($segment)
    {
        $dashboard = $this->dataService->getOneDashboardPage('"OwnerID" = '.$this->owner->ID.' AND "URLSegment" = \''.Convert::raw2sql($segment).'\'');
        return $dashboard;
    }
    
    public function getAnyDashboard()
    {
        $dashboard = $this->dataService->getOneDashboardPage('"OwnerID" = '.$this->owner->ID);
        if (!$dashboard) {
            $dashboard = $this->securityContext->getMember()->createDashboard('main', true);
        }
        return $dashboard;
    }

    public function createDashboard($name, $createDefault = false)
    {
        $url = preg_replace('/ +/', '-', trim($name)); // Replace any spaces
        $url = preg_replace('/[^A-Za-z0-9.+_\-]/', '', $url); // Replace non alphanumeric characters
        $url = strtolower($url);

        $existing = $this->getNamedDashboard($url);
        if ($existing) {
            return $existing;
        }

        $dashboard = new DashboardPage;
        $dashboard->URLSegment = $url;
        $dashboard->Title = trim($name);
        $dashboard->OwnerID = $this->owner->ID;
        $dashboard->write();
        
        if ($createDefault) {
            $currentStage = null;
            
            if (Widget::has_extension('Versioned')) {
                $currentStage = Versioned::current_stage();
                Versioned::reading_stage('Stage');
            }
            $layout = Config::inst()->get('DashboardUser', 'default_layout');
            if (count($layout)) {
                foreach ($layout as $type => $properties) {
                    if (class_exists($type)) {
                        $dashlet = $type::create();
                        /* @var $dashlet Dashlet */
                        $dashletColumn = isset($properties['DashletColumn']) ? $properties['DashletColumn'] : 0;

                        $db = $dashboard->getDashboard($dashletColumn);
                        if ($db && $dashlet->canCreate()) {
                            $dashlet->ParentID = $db->ID;
                            if (is_array($properties)) {
                                $dashlet->update($properties);
                            }
                            $dashlet->write();
                        }
                    }
                }
            }

            if ($currentStage) {
                Versioned::reading_stage($currentStage);
            }
            
            $dashboard = $this->getNamedDashboard($url);
            
            
            $this->owner->extend('updateCreatedDashboard', $dashboard);
        }
        return $dashboard;
    }
}
