<?php

/**
 * @file controllers/grid/users/author/form/AuthorForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorForm
 * @ingroup controllers_grid_users_author_form
 *
 * @brief Form for adding/editing a author
 */

import('lib.pkp.classes.form.Form');

class AuthorForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/** Author the author being edited **/
	var $_author;

	/**
	 * Constructor.
	 */
	function AuthorForm($monograph, $author) {
		parent::Form('controllers/grid/users/author/form/authorForm.tpl');
		$this->setMonograph($monograph);
		$this->setAuthor($author);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'firstName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'lastName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'installer.form.emailRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'url', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	* Get the author
	* @return Author
	*/
	function getAuthor() {
		return $this->_author;
	}

	/**
	* Set the author
	* @param @author Author
	*/
	function setAuthor($author) {
		$this->_author =& $author;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	* Initialize form data from the associated author.
	* @param $uniqueAuthor Unique Author
	*/
	function initData($uniqueAuthorId = null) {
		$author =& $this->getAuthor();

        $this->setData('uniqueAuthorId', $uniqueAuthorId);
		if ( $author ) {
			$this->_data = array_merge($this->_data, array(
				'authorId' => $author->getId(),
				'firstName' => $author->getFirstName(),
				'middleName' => $author->getMiddleName(),
				'lastName' => $author->getLastName(),
				'affiliation' => $author->getAffiliation(Locale::getLocale()),
				'country' => $author->getCountry(),
				'email' => $author->getEmail(),
				'url' => $author->getUrl(),
				'userGroupId' => $author->getUserGroupId(),
				'biography' => $author->getBiography(Locale::getLocale()),
				'primaryContact' => $author->getPrimaryContact()
			));
		} elseif ( $uniqueAuthorId ) {
            $uniqueAuthorDao =& DAORegistry::getDAO('UniqueAuthorDAO');

            // some special handling for internal data types
            // Try PkpAuthor and PkpUser
            if ( $uniqueAuthor =& $uniqueAuthorDao->getUniqueAuthorByIdAndType($uniqueAuthorId, 'PkpAuthor') ) {
                $authorDao =& DAORegistry::getDAO('AuthorDAO');
                $author =& $authorDao->getAuthor($uniqueAuthor->getIdentifierId());
                // Unset the primary contact and the id, since they belong to another monograph.
                $author->setId(null);
                $author->setPrimaryContact(null);
            } elseif ( $uniqueAuthor =& $uniqueAuthorDao->getUniqueAuthorByIdAndType($uniqueAuthorId, 'PkpUser') ) {
                $userDao =& DAORegistry::getDAO('UserDAO');
                $user =& $userDao->getUser($uniqueAuthor->getIdentifierId());
                $author = new Author();
                $author->setFirstName($user->getFirstName());
                $author->setMiddleName($user->getMiddleName());
                $author->setLastName($user->getLastName());
                $author->setAffiliation($user->getAffiliation(null), null);
                $author->setCountry($user->getCountry());
                $author->setEmail($user->getEmail());
                $author->setUrl($user->getUrl());
                $author->setBiography($user->getBiography(null), null);
            } else {
                // We could try to parse here.
            }
        }
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();

		$author =& $this->getAuthor();
        if ( $author ) {
            // We are editing, display the form with buttons (no wizard)
            $templateMgr->assign('addButtons', true);
        }

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByRoleId($context->getId(), ROLE_ID_AUTHOR);
		$authorUserGroups = array();
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$authorUserGroups[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}
		$templateMgr->assign_by_ref('authorUserGroups', $authorUserGroups);

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'authorId',
			'firstName',
			'middleName',
			'lastName',
			'affiliation',
			'country',
			'email',
			'url',
			'userGroupId',
			'biography',
			'primaryContact'
		));
	}

	/**
	 * Save author
	 * @see Form::execute()
	 */
	function execute() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$monograph = $this->getMonograph();

		$author =& $this->getAuthor();
		if (!$author) {
			// this is a new submission contributor
			$author = new Author();
			$author->setMonographId($monograph->getId());
			$existingAuthor = false;
		} else {
			$existingAuthor = true;
		}

		assert($monograph->getId() == $author->getMonographId());

		$author->setFirstName($this->getData('firstName'));
		$author->setMiddleName($this->getData('middleName'));
		$author->setLastName($this->getData('lastName'));
		$author->setAffiliation($this->getData('affiliation'), Locale::getLocale()); // localized
		$author->setCountry($this->getData('country'));
		$author->setEmail($this->getData('email'));
		$author->setUrl($this->getData('url'));
		$author->setUserGroupId($this->getData('userGroupId'));
		$author->setBiography($this->getData('biography'), Locale::getLocale()); // localized
		$author->setPrimaryContact(($this->getData('primaryContact') ? true : false));

		if ($existingAuthor) {
			$authorDao->updateAuthor($author);
			$authorId = $author->getId();
		} else {
			$authorId = $authorDao->insertAuthor($author);
		}

		return $authorId;
	}
}

?>
