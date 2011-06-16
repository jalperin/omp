<?php

/**
 * @file controllers/grid/users/author/AuthorGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridHandler
 * @ingroup controllers_grid_users_author
 *
 * @brief Handle author grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import author grid specific classes
import('controllers.grid.users.author.AuthorGridCellProvider');
import('controllers.grid.users.author.AuthorGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.WizardModal');

class AuthorGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function AuthorGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addAuthor', 'searchAuthor',
                     'fetchSearchForm', 'createAuthorAssociation', 'editAuthor',
                     'updateAuthor', 'deleteAuthor'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this author grid.
	 * @return Monograph
	 */
	function &getMonograph() {
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
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		// Basic grid configuration
		$this->setTitle('submission.submit.addAuthor');

		// Get the monograph id
		$monograph =& $this->getMonograph();
		assert(is_a($monograph, 'Monograph'));
		$monographId = $monograph->getId();

		// Retrieve the authors associated with this monograph to be displayed in the grid
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$data =& $authorDao->getAuthorsBySubmissionId($monographId, true);
		$this->setGridDataElements($data);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addAuthor',
				new WizardModal(
					$router->url($request, null, null, 'addAuthor', null, $actionArgs),
					__('grid.action.addAuthor'),
					'addUser'
				),
				__('grid.action.addAuthor'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new AuthorGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'email',
				'author.users.contributor.email',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'author.users.contributor.role',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'principalContact',
				'author.users.contributor.principalContact',
				null,
				'controllers/grid/users/author/primaryContact.tpl',
				$cellProvider
			)
		);
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return AuthorGridRow
	 */
	function &getRowInstance() {
		$row = new AuthorGridRow();
		return $row;
	}

    /**
	 * Get the arguments that will identify the data in the grid
     * In this case, the monograph.
	 * @return array
	 */
    function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
            );
    }


	//
	// Public Author Grid Actions
	//
    /**
	 * Display the search form (basically a search box and a button).
	 * @param $args array
	 * @param $request PKPRequest
	 */
    function fetchSearchForm($args, &$request) {
		// Form handling
		import('controllers.grid.user.uniqueAuthor.form.SearchAuthorForm');
		$searchAuthorForm = new SearchAuthorForm();
		$searchAuthorForm->initData();

		$json = new JSONMessage(true, $searchAuthorForm->fetch($request));
		return $json->getString();
    }

    /**
	 * This holds the logic for how authors are joined together into a "disambiguated" author.
     * It uses the uniqueAuthorId of the first grid and all of ids from the potential authors grid.
     * It falls on the uniqueAuthorDAO to make the connections.
	 * @param $args array
	 * @param $request PKPRequest
	 */
    function createAuthorAssociation($args, &$request) {
        $uniqueAuthorId = (int) $request->getUserVar('uniqueAuthorId');
        $potentialAuthorIds = $request->getUserVar('potentialAuthorId');

        if ( $potentialAuthorIds && is_array($potentialAuthorIds) ) {
            $uniqueAuthorDao =& DAORegistry::getDAO('UniqueAuthorDAO');
            foreach ( $potentialAuthorIds as $id) {
                $idArray = explode('-', $id);
                $identifierId = array_pop($idArray);
                $identifierType = implode('-', $idArray);
                $content = $request->getUserVar($id);

                $resultingUniqueAuthor =& $uniqueAuthorDao->addUniqueAuthorIdentifier(
                            $uniqueAuthorId, $identifierType, $identifierId, $content
                            );
                // We will always be returned a unique author which may, or may not, be the same as the one
                // the user selected (merges can occur). So make sure we call with the new id the next time through.
                $uniqueAuthorId = $resultingUniqueAuthor->getId();
            }
        }

        // FIXME: Perhaps this should create a new kind of JS event.
        // A workaround to that is to return the ID and let the calling JS use it to create a new event there.
		$json = new JSONMessage(true, (int) $uniqueAuthorId);
		return $json->getString();
    }


    /**
	 * Display the tab that holds the search form and the grids the user will work with.
	 * @param $args array
	 * @param $request PKPRequest
	 */
    function searchAuthor($args, &$request) {
        $monograph =& $this->getMonograph();
        $templateMgr =& PKPTemplateManager::getManager();
        $templateMgr->assign('monographId', $monograph->getId());
        return $templateMgr->fetchJson('controllers/grid/users/uniqueAuthor/searchAuthor.tpl');
    }

	/**
	 * An action to add a new author (opens the wizard).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addAuthor($args, &$request) {
		$monograph =& $this->getMonograph();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographId', $monograph->getId());
		return $templateMgr->fetchJson('controllers/grid/users/author/addAuthor.tpl');
	}

	/**
	 * Edit an existing author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
        $uniqueAuthorId = $request->getUserVar('uniqueAuthorId');

		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author = $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->initData($uniqueAuthorId);

		$json = new JSONMessage(true, $authorForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
        $uniqueAuthorId = $request->getUserVar('uniqueAuthorId');
		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->readInputData();
		if ($authorForm->validate()) {
			$authorId = $authorForm->execute();
            $author =& $authorDao->getAuthor($authorId);

            // FIXME: is this the right place for this? Should get the author plugin to do this?
            $uniqueAuthorDao =& DAORegistry::getDAO('UniqueAuthorDAO');
            $uniqueAuthorDao->addUniqueAuthorIdentifier($uniqueAuthorId, 'PkpAuthor', $authorId, $author->getFullName());

			// Render the row into a JSON response
			if($author->getPrimaryContact()) {
				// If this is the primary contact, redraw the whole grid
				// so that it takes the checkbox off other rows.
				return DAO::getDataChangedEvent();
            } else {
            	return DAO::getDataChangedEvent($authorId);
			}
		} else {
			$json = new JSONMessage(false, Locale::translate('editor.monograph.addUserError'));
            return $json->getString();
		}

	}

	/**
	 * Delete a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteAuthor($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the author to be deleted
		$authorId = $request->getUserVar('authorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($authorId, $monographId);

		if ($result) {
			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, Locale::translate('submission.submit.errorDeletingAuthor'));
		}
		return $json->getString();
	}
}

?>
