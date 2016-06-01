<?php
/**
 * @file SiteJSModelAdmin.php
 *
 * Left-hand-side tab : Admin PromoTile
 * */

class SiteJSModelAdmin extends ModelAdmin {
	private static $managed_models = array('SiteJsConfig');
	private static $url_segment = 'site-js-configs';
	private static $menu_title = 'Site JS Configs';
	private static $menu_icon = 'silverstripe-js-manager/images/js-file.png';
	
	public function getEditForm($id = null, $fields = null) {
		
		Versioned::set_reading_mode('Stage.Stage');
		$form = parent::getEditForm($id, $fields);
				
		$grid = $form->Fields()->fieldByName($this->sanitiseClassName($this->modelClass));
		$grid->getConfig()
			->removeComponentsByType('GridFieldPaginator')
			->removeComponentsByType('GridFieldDetailForm')
			->removeComponentsByType('GridFieldExportButton')
			->removeComponentsByType('GridFieldPrintButton')
			->addComponents(
				new SiteJSAdminForm(),
				new GridFieldPaginatorWithShowAll(30)
			);
		return $form;
	}
}