<?php
	$pageTitle = 'Home';
	require_once('head.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		echo "<hr />";
		echo '<h3>'.util_lang('you_possesive').' '.util_lang('notebooks').'</h3>';
		echo "<ul class=\"unstyled\" id=\"displayEqGroups\">";

		# is system admin?
		if ($USER->flag_is_system_admin) {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo "<li><a href=\"equipment_group.php?eid=" . $ueg->eq_group_id . "\" title=\"\">" . $ueg->name . "</a>: " . $ueg->descr . "</li>";
				}
			}
			else {
				echo "<li>You do not belong to any equipment groups.</li>";
			}
			echo "</ul>";
			# system admin may add new eq_groups
			?>

			<form action="ajax_actions/ajax_eq_group.php" id="frmAddGroup" class="form-horizontal" name="frmAddGroup" method="post">
				<button type="button" id="btnDisplayAddEqGroup" class="btn btn-primary" name="btnDisplayAddEqGroup">Add a new equipment group
				</button>

				<div id="eqGroupFields" class="hide">
					<legend>Add a new equipment group</legend>
					<div class="control-group">
						<label class="control-label" for="groupName">Name</label>

						<div class="controls">
							<input type="hidden" id="ajaxGroupAction" name="ajaxGroupAction" value="add-group" />
							<input type="text" id="groupName" class="input-large" name="groupName" value="" placeholder="Name of group" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="groupDescription">Description</label>

						<div class="controls">
							<input type="text" id="groupDescription" class="input-xlarge" name="groupDescription" value="" placeholder="Description of group" maxlength="200" />
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="btnSubmitAddEqGroup"></label>

						<div class="controls">
							<button type="submit" id="btnSubmitAddEqGroup" class="btn btn-success" data-loading-text="Saving...">Add Group</button>
							<button type="reset" id="btnCancelAddEqGroup" class="btn btn-link btn-cancel">Cancel</button>
						</div>
					</div>
				</div>
			</form>
		<?php
		}
		else {
			# get groups for this ordinary user
			$UserEqGroups = EqGroup::getAllEqGroupsForNonAdminUser($USER);
			if (count($UserEqGroups) > 0) {
				foreach ($UserEqGroups as $ueg) {
					echo $ueg->toListItemLinked();
				}
			}
			else {
				echo "<li>You do not have access to any equipment groups.</li>";
			}
			echo "</ul>";
		}
	}
	else {
		// SECTION: not yet authenticated, wants to log in
		?>
		<div class="hero-unit">
			<h2><?php echo LANG_INSTITUTION_NAME; ?></h2>

			<h1><?php echo LANG_APP_NAME; ?></h1>

			<br />

			<p><?php echo util_lang('app_short_description'); ?></p>

            <p><?php echo util_lang('app_sign_in_msg'); ?></p>

		</div>
	<?php
	}

	require_once('foot.php');
?>