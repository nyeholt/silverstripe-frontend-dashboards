<?php

class NoteDashlet extends Dashlet
{
    public static $title    = 'Note';
	public static $cmsTitle = 'Note';
    public static $description = "A dashlet that can hold notes which use markdown syntax";
    
    public static $db        = array(
        'Content' => 'Text'
    );

    /*public function EditContent() {
        return "Testing";
    }*/

    public function getDashletFields()
    {
        $fields = parent::getDashletFields();

        $fields->push(new TextareaField('Content', _t('NoteDashlet.NOTE_CONTENT', 'Content'), 'Your content goes here.'));

        return $fields;
    }
}

class NoteDashlet_Controller extends Dashlet_Controller
{
    public function init()
    {
        parent::init();

        Requirements::javascript('frontend-dashboards/thirdparty/markdown/markdown.min.js');
        Requirements::javascript('frontend-dashboards/javascript/notedashlet.js');
        Requirements::css('frontend-dashboards/css/notedashlet.css');
    }

    public function NoteContent()
    {
        return $this->Content;
    }
}
