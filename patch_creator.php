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

if (isset($_POST['checkout'])) {
    if (!isset($_POST['path_to_git_exec']) || trim($_POST['path_to_git_exec']) == "") {
        $missing_fields[] = _AT('path_to_git_exec');
    }
    else {
        try {
            $repo = new PHPGit_Repository('../../google_talk', false, array('git_executable' => '"'.$_POST['path_to_git_exec'].'"'));
            $repo->git('git status');
        }
        catch (RuntimeException $e) {
            $msg->addError('INVALID_GIT_BINARY');
        }
    }

    if (!isset($_POST['new_branch_checkout']) || trim($_POST['new_branch_checkout']) == "") {
        $missing_fields[] = _AT('new_branch_checkout');
    }
    else if (!$missing_fields) {
        try {
            $repo->git('git checkout -b '. $_POST['new_branch_checkout']);
            $msg->addFeedback('Checked Out');
            $msg->printFeedbacks();
        }
        catch (RuntimeException $e) {
            $msg->addError('CANNOT_CHECKOUT');
        }
    }

    if ($missing_fields) {
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
    }
    $msg->printErrors();
}

function trim_value(&$value) {
    $value = trim($value);
}

$files = array();
$files['mod'] = array(); //store modified files
$files['del'] = array(); //store deleted files
$files['new'] = array(); //store new files

if (isset($_REQUEST['select_files_to_add'])) {
    $result = $repo->git('git status -s');
    $all_files = preg_split("/\r\n|\n|\r/", $result);
    array_walk($all_files, 'trim_value');
    foreach ($all_files as $get_type) {
        if ($get_type['0'] == 'M') {
            $get_type = str_replace("M ", "", $get_type);
            $files['mod'][] = $get_type;
        }
        else if ($get_type['0'] == 'D') {
            $get_type = str_replace("D ", "", $get_type);
            $files['del'][] = $get_type;
        }
        else {
            $get_type = str_replace("?? ", "", $get_type);
            $files['new'][] = $get_type;
        }
    }
    echo json_encode($files);
}

if (isset($_POST['commit'])) {
    if (!isset($_POST['commit_message']) || trim($_POST['commit_message']) == "") {
        $missing_fields[] = _AT('commit_message');
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        $msg->printErrors();
    }
    else {
        if (!empty($_POST['mod_select_file']))
        foreach($_POST['mod_select_file'] as $check) {
            $repo->git('git add '.$check);
        }
        if (!empty($_POST['new_select_file']))
        foreach($_POST['new_select_file'] as $check) {
            $repo->git('git add '.$check);
        }
        if (!empty($_POST['del_select_file']))
        foreach($_POST['del_select_file'] as $check) {
            $repo->git('git rm '.$check);
        }
        try {
            $repo->git('git commit -m "'.$_POST['commit_message'].'"');
            $msg->addFeedback('COMMITTED');
            $msg->printFeedbacks();
        }
        catch (RuntimeException $e) {
            $msg->printErrors('CANNOT_COMMIT');
        }
    }
}

if (isset($_POST['push'])) {
    try {
        $repo->git('git push origin '.$_POST['new_branch_checkout']);
        $msg->addFeedback('PUSHED');
        $msg->printFeedbacks();
    }
    catch (RuntimeException $e) {
        $msg->printErrors('CANNOT_PUSH');
    }
}

?>
