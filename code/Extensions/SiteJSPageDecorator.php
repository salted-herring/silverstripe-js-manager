<?php

class SiteJSPageDecorator extends DataExtension {
	protected static $db = array(
		
	);
	
	protected static $has_one = array(
		'JSConfig'		=>	'SiteJsConfig'
	);
	
	
	public function updateCMSFields(FieldList $fields) {
		if (!Member::currentUser()) { return; }
		if (!Member::currentUser()->inGroup('Administrators')) { return; }
		
		$configs = Versioned::get_by_stage('SiteJsConfig','Live');
		$defaultItem = $configs->filter(array('isDefault' => true));
		$default = $defaultItem->count() == 1 ? $defaultItem->first()->Title : null;
		
		if (!empty($default)) {
			$fields->addFieldToTab('Root.JavascriptConfig', new LiteralField('SiteDefault', 'Site default JS config: <strong style="font-style: italic;">' . $default . '</strong>'));
		}
		
		$options = $configs->map();
		$fields->addFieldToTab('Root.JavascriptConfig', $optfield = new OptionsetField('JSConfigID', 'Use a different JS Config?', $options));
		
		
		
		//Requirements::javascript("silverstripe-js-manager/js/js-manger.scripts.js");
		
		/*$fields->addFieldToTab('Root.Javascripts', new TextareaField('Javascripts', 'JS'));
		$fields->addFieldToTab('Root.Javascripts', $cusJS = new TextField('CustomJS', 'Path to the js file'));
		$cusJS->setAttribute('placeholder', 'e.g. themes/default/js/custom.scripts.js');
		$fields->addFieldToTab('Root.Javascripts', new LiteralField('JSPool','<ul id="page-js-files"></ul>'));*/
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