<?php

/**
 * @defgroup plugins_authorLookup_pkpUser
 */

/**
 * @file plugins/authorLookup/pkpUser/pkpUserAuthorLookupPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPBknPeopleLookupPlugin
 * @ingroup plugins_authorLookup_bnkPeople
 *
 * @brief Cross-application CrossRef citation lookup plugin
 */


import('classes.plugins.Plugin');


class PkpUserAuthorLookupPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PkpUserAuthorLookupPlugin() {
		parent::Plugin();
	}


	//
	// Override protected template methods from PKPPlugin
	//
	/**
	 * @see PKPPlugin::register()
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;
		$this->addLocaleData();
		return true;
	}

	/**
	 * @see PKPPlugin::getName()
	 */
	function getName() {
		return 'PkpUserAuthorLookupPlugin';
	}

    /**
     * shorter version of the plugin name
     * @return string
     */
    function getShortName() {
        return String::substr($this->getName(), 0, -18);
    }

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return Locale::translate('plugins.authorLookup.pkpUser.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return Locale::translate('plugins.authorLookup.pkpUser.description');
	}

	/**
	 * Do the lookup
	 */
	function getAuthors($name) {
		$returner = array();
		$userDao =& DAORegistry::getDAO('UserDAO');
		$users =& $userDao->getUsersByName($name);
		while ($user =& $users->next()) {
			$returner[$user->getId()] = $user->getFullName();
			unset($user);
		}
		return $returner;
	}}

?>
