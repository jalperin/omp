{**
 * controllers/tab/settings/submissionStage/form/submissionStageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission stage management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submissionStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="submissionStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="submissionStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submissionsStageFormNotification"}

	{url|assign:submissionLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_SUBMISSION}
	{load_url_in_div id="submissionLibraryGridDiv" url=$submissionLibraryUrl}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.submissionPreparationChecklist"}</h3>
	<p>{translate key="manager.setup.submissionPreparationChecklistDescription"}</p>

	{url|assign:submissionChecklistGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.submissionChecklist.SubmissionChecklistGridHandler" op="fetchGrid"}
	{load_url_in_div id="submissionChecklistGridDiv" url=$submissionChecklistGridUrl}

	{if !$wizardMode}
		{fbvFormButtons id="submissionStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>