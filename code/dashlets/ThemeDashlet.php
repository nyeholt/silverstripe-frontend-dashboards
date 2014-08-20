<?php

/**
 * 
 *
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class ThemeDashlet extends Dashlet {
	private static $db = array(
		'CssStatements'	=> 'Text',
	);
	
	public function getDashletFields() {
		$fields = parent::getDashletFields();
		$fields->insertBefore(TextareaField::create('CssStatements', 'CSS'), 'ExtraClasses');
		return $fields;
	}
}

class ThemeDashlet_Controller extends Dashlet_Controller {
	
}