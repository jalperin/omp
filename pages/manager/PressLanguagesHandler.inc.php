<?php

/**
 * @file PressLanguagesHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressLanguagesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for changing press language settings. 
 */

// $Id$


class PressLanguagesHandler extends ManagerHandler {

	/**
	 * Display form to edit language settings.
	 */
	function languages() {
		parent::validate();
		parent::setupTemplate(true);

		import('manager.form.LanguageSettingsForm');

		$settingsForm = &new LanguageSettingsForm();
		$settingsForm->initData();
		$settingsForm->display();
	}

	/**
	 * Save changes to language settings.
	 */
	function saveLanguageSettings() {
		parent::validate();
		parent::setupTemplate(true);

		import('manager.form.LanguageSettingsForm');

		$settingsForm = &new LanguageSettingsForm();
		$settingsForm->readInputData();

		if ($settingsForm->validate()) {
			$settingsForm->execute();

			$templateMgr = &TemplateManager::getManager();
			$templateMgr->assign(array(
				'currentUrl' => Request::url(null, null, 'languages'),
				'pageTitle' => 'common.languages',
				'message' => 'common.changesSaved',
				'backLink' => Request::url(null, Request::getRequestedPage()),
				'backLinkLabel' => 'manager.pressManagement'
			));
			$templateMgr->display('common/message.tpl');

		} else {
			$settingsForm->display();
		}
	}
	
	function reloadLocalizedDefaultSettings() {
		// make sure the locale is valid
		$locale = Request::getUserVar('localeToLoad');
		if ( !Locale::isLocaleValid($locale) ) {
			Request::redirect(null, null, 'languages');
		}

		parent::validate();
		parent::setupTemplate(true);
					
		$press = &Request::getPress();
		$pressSettingsDao = &DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->reloadLocalizedDefaultSettings($press->getPressId(), 'registry/pressSettings.xml', array(
				'indexUrl' => Request::getIndexUrl(),
				'pressPath' => $press->getData('path'),
				'primaryLocale' => $press->getPrimaryLocale(),
				'pressName' => $press->getPressName($press->getPrimaryLocale())
			),
			$locale);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign(array(
			'currentUrl' => Request::url(null, null, 'languages'),
			'pageTitle' => 'common.languages',
			'message' => 'common.changesSaved',
			'backLink' => Request::url(null, Request::getRequestedPage()),
			'backLinkLabel' => 'manager.pressManagement'
		));
		$templateMgr->display('common/message.tpl');
	}

	

}
?>