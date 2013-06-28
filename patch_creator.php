<?php
/************************************************************************/
/* ATutor                                                               */
/************************************************************************/
/* Copyright (c) 2002-2010                                              */
/* Inclusive Design Institute                                           */
/* http://atutor.ca                                                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/
// $Id$

define('AT_INCLUDE_PATH', '../../include/');
require_once (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER);

require_once('php-git-repo/lib/PHPgit/Repository.php');

if ($_POST['checkout']) {
    if(!isset($_POST['path_to_git_exec']) || trim($_POST['path_to_git_exec']) == "") {
        $missing_fields[] = _AT('path_to_git_exec');
    }
    else {
        $repo = new PHPGit_Repository('../../', false, array('git_executable' => '"'.$_POST['path_to_git_exec'].'"'));
    }

    if(!isset($_POST['new_branch_checkout']) || trim($_POST['new_branch_checkout']) == "") {
        $missing_fields[] = _AT('new_branch_checkout');
    }
    else {
        $repo->git('git checkout -b '. $_POST['new_branch_checkout']);
    }

    if ($missing_fields) {
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
    }
    $msg->printErrors();
}

if ($_POST['select_files_to_add']) {

    $result = $repo->git('git status -s');
    $files = explode("??", $result);
}

/*if ($_POST['done']) {
    if(empty($_POST['check_file'])) {
        //no file selected, plz select a file
    }
    else {
        foreach($_POST['check_file'] as $check) {
            $repo->git('git add '.$check);
            echo $check." added; ";
        }
    }
}*/

if ($_POST['commit']) {
    if (!isset($_POST['commit_message']) || trim($_POST['commit_message']) == "") {
        $missing_fields[] = _AT('commit_message');
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        $msg->printErrors();
    }
    else {
        $repo->git('git commit -m "'.$_POST['commit_message'].'"');
    }
}

if($_POST['push']) {
    $repo->git('git push origin '.$_POST['new_branch_checkout']);
}


?>
