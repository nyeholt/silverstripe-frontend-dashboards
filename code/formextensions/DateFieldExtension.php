<?php

/**
 * Adds a simple rendering option for the field so it still gets
 * the calendar stuff added, but none of the junk added by FieldHolder
 *
 * @author marcus@silverstripe.com.au
 */
class DateFieldExtension extends Extension {
	public function SimpleFieldHolder() {
		$d = Object::create('DateField_View_JQuery', $this->owner); 
		$d->onBeforeRender(); 
		$class = 'field date '.$this->owner->XML_val('extraClass');
		$html = '<span class="'.$class.'">'.$this->owner->Field().'</span>';
		$html = $d->onAfterRender($html); 
		return $html;
	}
}