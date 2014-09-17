<?php
    require_once('../app_setup.php');
	$pageTitle = ucfirst(util_lang('search'));
	require_once('../app_head.php');

# 1. determine whether displaying the form or else doing a search
# 2. if displaying the form build it from the metadata
# 3. if doing a search
#     determine the type
#     fetch the results
#     build the results display
#     build the filter/refinement data
#     build the filter/refinement display

echo "TODO: implement search";

require_once('../foot.php');
?>