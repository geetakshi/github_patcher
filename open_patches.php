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
require_once('php-git-repo/lib/PHPgit/Repository.php');
include('myown_patches.php');

list_patches('open', $msg);

$client = new Github\Client();
$repo = new PHPGit_Repository('../../', false, array('git_executable' => '"'.$_config['path_to_git_exec'].'"'));

if(isset($_POST['install']) && isset($_POST['id'])) {
    add_remove_patch('install', $msg, $repo, $client);
}

if(isset($_POST['uninstall']) && isset($_POST['id'])) {
    add_remove_patch('uninstall', $msg, $repo, $client);
}

require(AT_INCLUDE_PATH.'footer.inc.php');
?>
