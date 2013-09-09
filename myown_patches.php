<?php
/************************************************************************/
/* ATutor                                                               */
/************************************************************************/
/* Copyright (c) 2002-2013                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

function add_remove_patch($status, $msg, $repo, $client) {
    try {
        $commits_list = $client->api('pull_request')->commits('atutor', 'ATutor', $_POST['id']);
    }
    catch (RuntimeException $e) {
        $msg->addError('CANNOT_CONNECT_TO_GITHUB');
    }
    if(!$msg->containsErrors()) {
        if($status == 'test') {
            try {
                if($repo->hasBranch($_POST['patch_test_branch'])) {
                    $msg->addError('BRANCH_ALREADY_EXISTS');
                }
                else {
                    $repo->git('git checkout -b '.$_POST['patch_test_branch']);
                }
            }
            catch(RuntimeException $e) {
                $msg->printErrors('CANNOT_CHECKOUT_TO_TEST_BRANCH');
            }
        }
        else {
            $repo->git('git checkout master');
        }
        foreach(array_reverse($commits_list) as $commit) {
            $sha = $commit['sha'];
            if($status == 'install') {
                try {
                    $repo->git('git cherry-pick '.$sha);
                    $msg->addFeedback('PATCH_INSTALLED_SUCCESSFULLY');
                }
                catch (RuntimeException $e) {
                    $msg->addError('UNABLE_TO_INSTALL');
                }
            }
            else if($status == 'uninstall') {
                try {
                    $repo->git('git revert --no-edit '.$sha);
                    $msg->addFeedback('PATCH_UNINSTALLED_SUCCESSFULLY');
                }
                catch (RuntimeException $e) {
                    $msg->addError('UNABLE_TO_UNINSTALL');
                }
            }
            else if($status == 'test') {
                if(!isset($_POST['patch_test_branch'])) {
                    $missing_fields[] = _AT('patch_test_branch');
                    $missing_fields = implode(', ', $missing_fields);
                    $msg->addError(array('EMPTY_FIELDS', $missing_fields));
                }
                else {
                    try {
                        $repo->git('git cherry-pick '.$sha);
                        $msg->addFeedback('PATCH_APPLIED_TO_TEST_BRANCH');
                    }
                    catch (RuntimeException $e) {
                        $msg->addError('UNABLE_TO_APPLY_TO_TEST_BRANCH');
                    }
                }
            }
        }
    }
    $msg->printErrors();
    $msg->printFeedbacks();
}

function print_row($pr_values) {
?>
    <tr>
        <td><input type="radio" name="id" value="<?php echo $pr_values['number']; ?>"></td>
        <td><label><?php echo $pr_values['number']; ?></label></td>
        <td><label><?php echo $pr_values['title']; ?></label></td>
        <td><label><?php echo $pr_values['state']; ?></label></td>
        <td><label><?php echo $pr_values['merged_at']; ?></label></td>
        <td><label><?php echo $pr_values['user']['login']; ?></label></td>
    </tr>
<?php } ?>

<?php function list_patches($state, $msg, $per_page = 20) { ?>
<form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table summary="" class="data" rules="cols" align="center" style="width: 100%;">
<thead>
<tr>
    <th scope="col">&nbsp;</th>
    <th scope="col"><?php echo _AT('patch_id'); ?></th>
    <th scope="col"><?php echo _AT('patch_title'); ?></th>
    <th scope="col"><?php echo _AT('pr_status'); ?></th>
    <th scope="col"><?php echo _AT('merged_at'); ?></th>
    <th scope="col"><?php echo _AT('author'); ?></th>
</tr>
</thead>

<?php
$current_page = (@$_GET['page'])?($_GET['page']):1;
$client = new Github\Client();
try {
    $PullRequest = $client->api('pull_request')->all('atutor', 'ATutor', $state, $current_page, $per_page);
    foreach($PullRequest as $pr_values) {
        print_row($pr_values);
    }
}
catch(RuntimeException $e) {
    $msg->printErrors('CANNOT_CONNECT_TO_GITHUB');
}
?>
</table>
<span><input type="submit" name="uninstall" value="<?php echo _AT('uninstall'); ?>" style="float:right;"/></span>
<span><input type="submit" name="install" value="<?php echo _AT('install'); ?>" style="float:right;" /></span>
<?php
if($state == 'closed') {
    $state_tab = 'closed_patches';
}
else if($state == 'open') {
    $state_tab = 'open_patches';
?>
<p><?php echo _AT('patch_test_description') ?></p>
<label><?php echo _AT('patch_test_branch'); ?></label>
<input type="text" name="patch_test_branch" maxlength="100" size="30" /><br />
<span><input type="submit" name="patch_test" value="<?php echo _AT('patch_test'); ?>" /><br /></span>
<?php
}

if ($current_page > 1)
    echo '<a href="mods/github_patcher/'.$state_tab.'.php?page=' . ($current_page - 1) . '">&lt; Previous page</a><span class="tab"></span>';

echo 'Page '. $current_page;

if(count($PullRequest) == $per_page) {
    echo '<span class="tab"></span><a href="mods/github_patcher/'.$state_tab.'.php?page=' . ($current_page + 1) . '" class="right">Next page &gt;</a><br />';
}
else if(count($PullRequest) == 0) {
    echo '<span class="tab"></span><a mods/github_patcher/'.$state_tab.'.php?page=' . 1 . '" class="right">Go To Page 1 &gt;</a><br />';
}
}?>
</form>
