<?php

/**
 * @file controllers/grid/settings/roles/UserGroupGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupGridCategoryRow
 * @ingroup controllers_grid_settings
 *
 * @brief UserGroup grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');

class UserGroupGridCategoryRow extends GridCategoryRow {

	/**
	 * Constructor
	 */
	function UserGroupGridCategoryRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$stageId = $this->getId();
		if (!empty($stageId) && is_numeric($stageId)) {
			$stage =& $this->getData();
			$actionArgs = array('stageId' => $stageId);
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();

			$ajaxModal = new AjaxModal($router->url($request, null, null, 'addToStage', null, $actionArgs));
			$editUserGroupLinkAction = new LinkAction(
				'addToStage',
				$ajaxModal,
				__('grid.action.addItem'),
				'add'
			);
			$this->addAction($editUserGroupLinkAction);
		}
	}

	/**
	 * Category rows only have one cell and one label.  This is it.
	 * return string
	 */
	function getCategoryLabel() {
		$data =& $this->getData();
		return __($data['name']);
	}
}
?>