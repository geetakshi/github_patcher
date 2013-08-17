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

define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER);
require(AT_INCLUDE_PATH.'header.inc.php');
require_once('php-github-api/library/autoload.php');

$client = new Github\Client();

function print_row($pr_values) {
?>
    <tr>
        <td><input type="radio" name="id" value="<?php echo $row_id; ?>"></td>
        <td><label><?php echo $pr_values['number']; ?></label></td>
        <td><label><?php echo $pr_values['title']; ?></label></td>
        <td><label><?php echo $pr_values['state']; ?></label></td>
        <td><label><?php echo $pr_values['user']['login']; ?></label></td>
    </tr>
<?php } ?>

<form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<table summary="" class="data" rules="cols" align="center" style="width: 100%;">
<thead>
<tr>
    <th scope="col">&nbsp;</th>
    <th scope="col"><?php echo _AT('patch_id'); ?></th>
    <th scope="col"><?php echo _AT('patch_title'); ?></th>
    <th scope="col"><?php echo _AT('pr_status'); ?></th>
    <th scope="col"><?php echo _AT('author'); ?></th>
</tr>
</thead>

<?php
$per_page = 20;
$state = 'open';
$current_page = (@$_GET['page'])?($_GET['page']):1;
try {
    $openPullRequest = $client->api('pull_request')->all('atutor', 'ATutor', $state, $current_page, $per_page);
    if(count($openPullRequest) != 0)
        foreach($openPullRequest as $pr_values) {
            print_row($pr_values);
        }
}
catch(RuntimeException $e) {
    $msg->printErrors('CANNOT_CONNECT_TO_GITHUB');
}
?>
</table>
<?php
if ($current_page > 1)
echo '<a href="mods/github_patcher/myown_patches.php?page=' . ($current_page - 1) . '">&lt; Previous page</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo 'Page '. $current_page;
if(count($openPullRequest) == $per_page) {
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<a href="mods/github_patcher/myown_patches.php?page=' . ($current_page + 1) . '" class="right">Next page &gt;</a><br />';
}
else if(count($openPullRequest) == 0) {
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp<a href="mods/github_patcher/myown_patches.php?page=' . 1 . '" class="right">Next page &gt;</a><br />';
}
?>
</form>

<?php require(AT_INCLUDE_PATH.'footer.inc.php');?>
