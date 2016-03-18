<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class UserProfileDashlet extends Dashlet
{
    public static $title = 'User profile';
	public static $cmsTitle = 'User Profile';
    
    public function canCreate($member = null)
    {
        if (!$member) {
            $member = Member::currentUser();
        }
        return $member->ID > 0;
    }
}

class UserProfileDashlet_Controller extends Dashlet_Controller
{
    public function UpdateForm()
    {
        $member = singleton('SecurityContext')->getMember();
        if (!$member) {
            return '';
        }
        // if there's a member profile page availble, use it
        $filter = array();
        if (class_exists('Multisites')) {
            $filter = array('SiteID' => Multisites::inst()->getCurrentSiteId());
        }
        
        // use member profile page if possible
        if (class_exists('MemberProfilePage') && $profilePage = MemberProfilePage::get()->filter($filter)->first()) {
            $controller = MemberProfilePage_Controller::create($profilePage);
            $form = $controller->ProfileForm();
            $form->addExtraClass('ajax-form');
            $form->loadDataFrom($member);
            return $form;
        } else {
            $password = new ConfirmedPasswordField(
                'Password',
                $member->fieldLabel('Password'),
                null,
                null,
                (bool) $this->ID
            );
            $password->setCanBeEmpty(true);
            
            $fields = FieldList::create(
                TextField::create('FirstName', $member->fieldLabel('FirstName')),
                TextField::create('Surname', $member->fieldLabel('Surname')),
                EmailField::create('Email', $member->fieldLabel('Email')),
                $password
            );
            
            $actions = FieldList::create($update = FormAction::create('updateprofile', 'Update'));
            
            $form = Form::create($this, 'UpdateForm', $fields, $actions);
            $form->loadDataFrom($member);
            
            $this->extend('updateProfileDashletForm', $form);
            
            return $form;
        }

        return;
    }
}
