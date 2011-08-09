{**
 * templates/controllers/grid/settings/roles/form/addUserGroupToStageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a user group
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addUserGroupToStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

{include file="common/formErrors.tpl"}

<form class="pkp_form" id="addUserGroupToStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.roles.UserGroupGridHandler" op="addToStage" form="mastheadForm"}">
	{if $stageId}
		<input type="hidden" id="stageId" name="stageId" value="{$stageId|escape}" />
	{/if}
	{fbvFormArea id="userGroupDetails"}
		<h3>{translate key="grid.roles.stageAssignment"}</h3>
		{fbvFormSection title="user.group" for="userGroupId" required="true"}
			{fbvElement type="select" name="userGroupId" from=$unassignedGroups id="userGroupId" selected=$userGroupId translate=false}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
