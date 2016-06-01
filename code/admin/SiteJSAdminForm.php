<?php

class SiteJSAdminForm extends GridFieldDetailForm {
	
}

class SiteJSAdminForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private static $allowed_actions = array(
		'ItemEditForm'
	);
	
	public function ItemEditForm() {
		
		$form = parent::ItemEditForm();
		/* @var $actions FieldList */
		if ($form instanceof Form) {
			$actions = $form->Actions();
			$fields = $form->Fields();
			
			$record = $this->record;
			
			$actions->removeByName('action_doSave');
			$actions->removeByName('action_doDelete');
			$btnSave = FormAction::create('saveConfig', 'Save');
			$btnSave->addExtraClass('ss-ui-action-constructive');
			$actions->push($btnSave);
			
			if (!empty($record->ID)) {
				
				$btnDel = FormAction::create('deleteConfig', 'Delete');
				$btnDel->addExtraClass('ss-ui-action-destructive');
				$actions->push($btnDel);
				
				$label = 'Activate';
				$class = 'ss-ui-action-constructive';
				$function = 'doActivation';
				
				if (Versioned::get_by_stage('SiteJsConfig', 'Live')->byID($record->ID)) {
					$label = 'Deactivate';
					$class = 'ss-ui-action-destructive';
					$function = 'doDeactivation';
				}
			
				
				$btnActivate = FormAction::create($function, $label);
				$btnActivate->addExtraClass($class);
			
				$actions->push($btnActivate);
			}
			
		}

		return $form;
	}
	
	public function deleteConfig($data, $form) {
		$record = $this->record;
		if (!empty($record->ID) && Versioned::get_by_stage('SiteJsConfig', 'Live')->byID($record->ID)) {
			$record->deleteFromStage('Live');
		}
		$record->deleteFromStage('Stage');
		$controller = Controller::curr();
		$controller->redirect('/admin/site-js-configs');
	}
	
	public function saveConfig($data, $form) {
		$record = $this->record;
		$form->saveInto($record);
		$record->writeToStage('Stage');
		
		if (!empty($record->ID) && Versioned::get_by_stage('SiteJsConfig', 'Live')->byID($record->ID)) {
			$record->writeToStage('Live');
		}
		
		$message = 'This config is now deactivated';
		
		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}
	
	public function doDeactivation($data, $form) {
		$record = $this->record;
		$form->saveInto($record);
		$record->writeToStage('Stage');
		$record->deleteFromStage('Live');
		
		$message = 'This config is now deactivated';
		
		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}
	
	public function doActivation($data, $form) {
		$record = $this->record;
		$form->saveInto($record);
		$record->writeToStage('Stage');
		$record->writeToStage('Live');
		
		$message = 'This config is now activated';
		$form->sessionMessage($message, 'good');
		return $this->edit(Controller::curr()->getRequest());
	}
	
}
