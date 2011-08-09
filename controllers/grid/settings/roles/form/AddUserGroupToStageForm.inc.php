<?php

/**
 * @file controllers/grid/settings/roles/form/AddUserGroupToStageForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupForm
 * @ingroup controllers_grid_settings_roles_form
 *
 * @brief Form to add an existing User Group to a Stage
 */

import('lib.pkp.classes.form.Form');

class AddUserGroupToStageForm extends Form {

	/** @var Id of the stage being edited */
	var $_stageId;

	/** @var The press of the stage being edited */
	var $_pressId;

	/**
	 * Constructor.
	 * @param $pressId Press id.
	 * @param $stageId Stage id.
	 */
	function AddUserGroupToStageForm($pressId, $stageId) {
		parent::Form('controllers/grid/settings/roles/form/addUserGroupToStageForm.tpl');
		$this->_pressId = $pressId;
		$this->_stageId = $stageId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'userGroupId', 'required', 'settings.roles.nameRequired'));
		if (!$this->_stageId) {
			$this->addCheck(new FormValidator($this, 'stageId', 'required', 'settings.roles.stageIdRequired'));
		}
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//

	/**
	 * Get the user group id.
	 * @return int userGroupId
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the press id.
	 * @return int pressId
	 */
	function getPressId() {
		return $this->_pressId;
	}

	//
	// Implement template methods from Form.
	//

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('userGroupId', 'stageId'));
	}

	/**
	 * @see Form::validate()
	 */
	function validate($callHooks = true) {
		// Name and abbrev data validation.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); // @var $userGroupDao UserGroupDAO

		$userGroupId = $this->getData('userGroupId');
		$stageId = $this->getData('stageId');

		if (!$stageId) {
			$this->addError('stageId[' . $locale . ']', 'settings.roles.stageIdRequired');
		}

		if (!$userGroupId) {
			$this->addError('userGroupId[' . $locale . ']', 'settings.roles.roleIdRequired');
		} else {
			$userGroup =& $userGroupDao->getById($userGroupId);
			if ($userGroup == null) {
				$this->addError('userGroupId[' . $locale . ']', 'settings.roles.roleIdRequired');
			}
		}

		return parent::validate($callHooks);
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$pressId = $this->getPressId();
		$stageId = $this->getStageId();

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups = $userGroupDao->getByContextId($pressId);
		$unassignedGroups = array();

		foreach ($userGroups->toAssociativeArray() as $userGroup) {
			if (!$userGroupDao->assignmentExists($pressId, $userGroup->getId(), $stageId)) {
				$unassignedGroups[ $userGroup->getId() ] = $userGroup->getLocalizedName(); // translate=false in .tpl
			}
		}
		$templateMgr->assign('unassignedGroups', $unassignedGroups);
		$templateMgr->assign('stageId', $stageId);

		return parent::fetch($request);
	}
}
