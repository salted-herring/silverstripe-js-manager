<?php
use SaltedHerring\Debugger as Debugger;
class SiteJSExtension extends DataExtension {
	protected static $db = array(
		'BowerDirectory'	=>	'Varchar(4096)',
		'JSLibrary'			=>	'Text'
	);
	
	protected static $defaults = array(
		'BowerDirectory'	=>	'themes/default/js/components'
	);
	
	public function updateCMSFields(FieldList $fields) {
		if (!Member::currentUser()) { return; }
		if (!Member::currentUser()->inGroup('Administrators')) { return; }
		Requirements::javascript("silverstripe-js-manager/js/js-manger.scripts.js");
		
		$fields->addFieldToTab('Root.Javascripts', $bowerPath = new TextField('BowerDirectory', 'Bower component directory path'));
		$fields->addFieldToTab('Root.Javascripts', new TextareaField('JSLibrary', 'Imported Javascripts'));
		
		if (empty($this->owner->BowerDirectory)) {
			$bowerFolder = self::$defaults['BowerDirectory'];
			$bowerPath->setAttribute('value', $bowerFolder);
		}else{
			$bowerFolder = $this->owner->BowerDirectory;
		}
		
		$bowerFolder = Director::baseFolder() . '/' . $bowerFolder;
		$components = array_filter(glob($bowerFolder.'/*'), 'is_dir');
		$libs = array();
		foreach ($components as $component) {
			$foldername = explode('/', $component);
			$foldername = $foldername[count($foldername) -1];
			$items = $this->jsFinder($component, Director::baseFolder());
			if (count($items) > 0) {
				$cbs = new CheckboxSetField($foldername, $foldername, $items);
				$cbs->addExtraClass('bower-components');
				$libs[] = $cbs;
			}
		}
		
		if (count($libs) > 0) {
			$fields->addFieldsToTab('Root.Javascripts', $libs);
		}else{
			$fields->addFieldToTab('Root.Javascripts', new LiteralField('EmptyLibrary','<h3 id="empty-lib">- Library is empty -</h3>'));
		}
		
		$fields->addFieldToTab('Root.Javascripts', new LiteralField('ImportedComponents','<ul id="imported-components"></ul>'));
		
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$baseFolder = Director::baseFolder() . '/';
		//SaltedHerring\Debugger::inspect();
	}
	
	private function jsFinder($path, $base) {
		$js = array();
		$it = new RecursiveDirectoryIterator($path);
		$display = Array ( 'js' );
		foreach(new RecursiveIteratorIterator($it) as $file)
		{
			$fn = $file->getFilename();
			$ext = $file->getExtension();
			if (in_array(strtolower($ext), $display)) {
				$path = str_replace($base, '', $file->getRealPath());
				$path = ltrim($path, '/');
				$js[$path] = $fn;
			}
		}
		
		return $js;
	}
}