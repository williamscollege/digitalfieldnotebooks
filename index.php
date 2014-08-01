<?php
	$pageTitle = 'Home';
	require_once('head.php');


	if ($IS_AUTHENTICATED) {
		// SECTION: authenticated

		echo "<hr />";
		echo '<h3>'.ucfirst(util_lang('you_possesive')).' '.ucfirst(util_lang('notebooks')).'</h3>';

		# is system admin?
		if ($USER->flag_is_system_admin) {
            // TODO: show special admin-only stuff
		}

        // TODO: show user stuff (list of notebooks et al)
        $notebooks = $USER->getAccessibleNotebooks(Action::getOneFromDb(['name'=>'view'],$DB));
        $num_notebooks = count($notebooks);

        $counter = 0;
        echo "<ul class=\"unstyled\" id=\"listOfUserNotebooks\" data-notebook-count=\"$num_notebooks\">\n";
        foreach ($notebooks as $notebook) {
            $counter++;
            echo $notebook->renderAsListItem('notebook_item_'.$counter)."\n";
        }
        echo "</ul>\n";
?>
        <input type="button" id="btn-add-notebook" value="<?php echo util_lang('add_notebook'); ?>"/>
<?php
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