<?php

class SiteJsConfig extends DataObject {
	protected static $db = array(
		'Title'				=>	'Varchar(16)',
		'isDefault'			=>	'Boolean',
		'BowerDirectory'	=>	'Varchar(4096)',
		'Config'				=>	'Text'
	);
	
	protected static $defaults = array(
		'BowerDirectory'	=>	'themes/default/js/components'
	);
	
	private function jsInjectorControl(&$fields, $tabName, $FieldName = 'CustomJS', $FieldTitle = 'Path to the js file') {
		$fields->addFieldToTab($tabName, $cusJS = new TextField($FieldName, 'Path to the js file'));
		$cusJS->setAttribute('placeholder', 'e.g. themes/default/js/custom.scripts.js')->addExtraClass('js-injector');
		$fields->addFieldToTab($tabName, new LiteralField('JSPool','<ul id="'.$FieldName.'-js-files" class="custom-js-orderer"></ul>'));
	}
	
	private function buildLibraries(&$fields) {
		$fields->addFieldToTab('Root.Libraries', $bowerPath = new TextField('BowerDirectory', 'Bower component directory path'));
		$fields->addFieldToTab('Root.Libraries', new TextareaField('JSLibrary', 'Imported Javascripts'));
		
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
			$fields->addFieldsToTab('Root.Libraries', $libs);
		}else{
			$fields->addFieldToTab('Root.Libraries', new LiteralField('EmptyLibrary','<h3 id="empty-lib">- Library is empty -</h3>'));
		}
		
		$fields->addFieldToTab('Root.Libraries', new LiteralField('ImportedComponents','<ul id="imported-components" class="custom-js-orderer"></ul>'));
	}
	
	private function buildBase(&$fields) {
		$fields->addFieldToTab('Root.SitewideJS', new TextareaField('JSBase', 'JS'));
		$fields->fieldByName('Root.SitewideJS')->setTitle('Sitewide');
		$this->jsInjectorControl($fields,'Root.SitewideJS','Sitewide_JS');
	}
	
	private function buildControllerTypes(&$fields) {
		$types = ClassInfo::subclassesFor('ContentController');
		unset($types['ContentController']);
		$fields->insertAfter(new Tab('ControllerJS','per Controller'),'SitewideJS');
		foreach ($types as $type) {
			$controller_heading = new LiteralField($type.'_heading', '<h3 class="group-heading">' . $type . '</h3>');
			$fields->addFieldToTab('Root.ControllerJS', $controller_heading);
			$this->jsInjectorControl($fields,'Root.ControllerJS', $type.'_JS');
		}
	}
	
	private function buildPage(&$fields) {
		$pages = SiteTree::get();
		$fields->insertAfter(new Tab('PageJS','per Page'),'ControllerJS');
		foreach ($pages as $page) {
			$title = $page->Title;
			$controller_heading = new LiteralField($title.'_heading', '<h3 class="group-heading">' . $title . '</h3>');
			$fields->addFieldToTab('Root.PageJS', $controller_heading);
			$this->jsInjectorControl($fields,'Root.PageJS', $page->Title.'_JS');
		}
	}
	
	public function getCMSFields() {
		if (!Member::currentUser()) { return; }
		if (!Member::currentUser()->inGroup('Administrators')) { return; }
		Requirements::javascript("silverstripe-js-manager/js/js-manger.scripts.js");
		
		$fields = parent::getCMSFields();
		$fields->fieldByName('Root.Main.isDefault')->setTitle('Default JS config');
		
		$this->buildLibraries($fields);
		$this->buildBase($fields);
		/*$this->buildControllerTypes($fields);
		$this->buildPage($fields);*/
		
		return $fields;
	}
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->isDefault) {
			$existings = SiteJsConfig::get()->filter(array('isDefault' => true))->exclude('ID', $this->ID);
			if ($existings->count() > 0) {
				foreach ($existings as $existing) {
					$existing->isDefault = false;
					$existing->write();
					if (Versioned::get_by_stage('SiteJsConfig', 'Live')->byID($existing->ID)) {
						$existing->writeToStage('Live');
					}
				}
			}
		}
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