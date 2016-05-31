<?php

class SiteJSControllerExtension extends Extension {
	public function __construct() {
		$config = SiteConfig::current_site_config();
		$components = $config->JSLibrary;
		if (!empty($components)) {
			$components = explode(',', $components);
			Requirements::combine_files(
				'scripts.js',
				$components
			);
		}
	
		parent::__construct();
	}
}