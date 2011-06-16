{**
 * templates/controllers/grid/user/author/addAuthor.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Tabs to search and then add a submission contributor
 *
 *}

{assign var='uniqueId' value=""|uniqid}

<script type="text/javascript">
	// Attach the Wizard handler.
	$(function() {ldelim}
		$('#addAuthor').pkpHandler(
				'$.pkp.controllers.wizard.uniqueAuthor.UniqueAuthorWizardHandler',
				{ldelim}
                    editAuthorUrl: '{url op="editAuthor" monographId=$monographId}',
                    uniqueAuthorGridUrl: '{url router=$smarty.const.ROUTE_COMPONENT component="grid.user.uniqueAuthor.UniqueAuthorGridHandler" op="fetchGrid"}',
                    potentialAuthorGridUrl: '{url router=$smarty.const.ROUTE_COMPONENT component="grid.user.uniqueAuthor.PotentialAuthorGridHandler" op="fetchGrid"}',
					cancelButtonText: '{translate|escape:javascript key="common.cancel"}',
					continueButtonText: '{translate|escape:javascript key="common.continue"}',
					finishButtonText: '{translate|escape:javascript key="common.finish"}',
				{rdelim});
	{rdelim});
</script>

<div id="addAuthor">
	<ul>
		<li><a href="{url op="searchAuthor" monographId=$monographId}">{translate key="common.search"}</a></li>
		<li><a href="{url op="editAuthor" monographId=$monographId}">{translate key="common.add"}</a></li>
	</ul>
</div>
