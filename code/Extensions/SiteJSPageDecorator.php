<?php

class SiteJSPageDecorator extends DataExtension {
	protected static $db = array(
		'Javascripts'	=>	'Text'
	);
	
	public function updateCMSFields(FieldList $fields) {
		if (!Member::currentUser()) { return; }
		if (!Member::currentUser()->inGroup('Administrators')) { return; }
		Requirements::javascript("silverstripe-js-manager/js/js-manger.scripts.js");
		
		$fields->addFieldToTab('Root.Javascripts', new TextareaField('Javascripts', 'JS'));
		$fields->addFieldToTab('Root.Javascripts', $cusJS = new TextField('CustomJS', 'Path to the js file'));
		$cusJS->setAttribute('placeholder', 'e.g. themes/default/js/custom.scripts.js');
		$fields->addFieldToTab('Root.Javascripts', new LiteralField('JSPool','<ul id="page-js-files"></ul>'));
	}
		
	public function getPageJS() {
		$JS = $this->owner->Javascripts;
		if (!empty($JS)) {
			$JS = explode(',', $JS);
			$output = '';
			foreach ($JS as $item) {
				$output .= '<script type="text/javascript" src="/'. $item . (  SS_ENVIRONMENT_TYPE == 'dev' ? ('?m='.time()) : ''  ) .'"></script>' . "\n";
			}
			
			return trim($output);
		}
		
		return false;
	}
	
}