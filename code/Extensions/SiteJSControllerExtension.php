<?php
use SaltedHerring\Debugger as Debugger;
class SiteJSControllerExtension extends Extension {
	public function __construct() {
		$controller = Controller::curr();
		if ($controller->request->getVar('url') != '/admin/pages/edit/EditForm') {
			$config = Versioned::get_by_stage('SiteJsConfig','Live')->filter(array(
				'isDefault'		=>	true
			));
			if ($config->count() > 0) {
				
				$components = $config->first()->Config;
				if (!empty($components)) {
					$components = json_decode($components);
					if (!empty($components)) {
						$components = $this->getJSList($components);
						if (count($components) > 0) {
							Requirements::combine_files(
								'scripts.js',
								$components
							);
						}
					}
				}
			}
		}
		parent::__construct();
	}
	
	
	private function getJSList($components) {
		$lst = array();
		foreach ($components as $component) {
			if ($component->name  == 'Library' || $component->name == 'Sitewide') {
				$paths = $component->files;
				foreach ($paths as $path) {
					$lst[] = $path;
				}
			}
		}
		return $lst;
	}
}