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

function trim_value(&$value) {
    $value = trim($value);
}

$mod_files = array(); //store modified files
$del_files = array(); //store deleted files
$new_files = array(); //store new files

if (isset($_POST['select_files_to_add'])) {
    $result = $repo->git('git status -s');
    $files = preg_split("/\r\n|\n|\r/", $result);
    array_walk($files, 'trim_value');
    foreach ($files as $get_type) {
        if ($get_type['0'] == 'M') {
            $get_type = str_replace("M", "", $get_type);
            $mod_files[] = $get_type;
        }
        else if ($get_type['0'] == 'D') {
            $get_type = str_replace("D", "", $get_type);
            $del_files[] = $get_type;
        }
        else {
            $get_type = str_replace("??", "", $get_type);
            $new_files[] = $get_type;
        }
    }
}

if(isset($_POST['add_selected_files'])) {
    if(empty($_POST['mod_select_file']) && empty($_POST['del_select_file']) && empty($_POST['new_select_file'])) {
        //echo 'no file selected, plz select a file';
    }
    else {
        foreach($_POST['mod_select_file'] as $check) {
            $repo->git('git add '.$check);
            echo $check ."added";
        }
        foreach($_POST['new_select_file'] as $check) {
            $repo->git('git add '.$check);
            echo $check ."added";
        }
        foreach($_POST['del_select_file'] as $check) {
            $repo->git('git rm '.$check);
            echo $check ."removed";
        }
    }
}

//to test
//$str = array(1,2,3);

if (isset($_POST['commit'])) {
    if (!isset($_POST['commit_message']) || trim($_POST['commit_message']) == "") {
        $missing_fields[] = _AT('commit_message');
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        $msg->printErrors();
    }
    else {
        $repo->git('git commit -m "'.$_POST['commit_message'].'"');
    }
}

if(isset($_POST['push'])) {
    $repo->git('git push origin '.$_POST['new_branch_checkout']);
}


?>
