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
        <td><label><?php echo $pr_values['body']; ?></label></td>
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
    <th scope="col"><?php echo _AT('patch_description'); ?></th>
    <th scope="col"><?php echo _AT('author'); ?></th>
</tr>
</thead>
<?php
$closePullRequests = $client->api('pull_request')->all('atutor', 'ATutor', 'open');
foreach($closePullRequests as $row_id=>$pr_values) {
    print_row($pr_values);
}
?>
</table>
</form>

<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>
