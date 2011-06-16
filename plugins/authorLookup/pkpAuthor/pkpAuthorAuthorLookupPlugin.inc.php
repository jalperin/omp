<?php

/**
 * @defgroup plugins_authorLookup_pkpAuthor
 */

/**
 * @file plugins/authorLookup/pkpAuthor/pkpAuthorAuthorLookupPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPBknPeopleLookupPlugin
 * @ingroup plugins_authorLookup_bnkPeople
 *
 * @brief Plugin lookup/query existing authors
 */


import('classes.plugins.Plugin');


class PkpAuthorAuthorLookupPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PkpAuthorAuthorLookupPlugin() {
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
		return 'PkpAuthorAuthorLookupPlugin';
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
		return Locale::translate('plugins.authorLookup.pkpAuthor.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return Locale::translate('plugins.authorLookup.pkpAuthor.description');
	}

	/**
	 * Do the lookup
	 */
	function getAuthors($name) {
		$returner = array();
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors =& $authorDao->getAuthorsByName($name);
		while ($author =& $authors->next()) {
			$returner[$author->getId()] = $author->getFullName();
			unset($author);
		}
		return $returner;
	}
}

?>
