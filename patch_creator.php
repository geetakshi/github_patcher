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
require_once (AT_INCLUDE_PATH.'vitals.inc.php');
admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER);

require_once('php-git-repo/lib/PHPgit/Repository.php');
require_once('php-github-api/library/autoload.php');


$repo = new PHPGit_Repository('../../', false, array('git_executable' => '"'.$_config['path_to_git_exec'].'"'));

//Checking out to a new branch to make changes
if (isset($_POST['checkout'])) {
    try {
        $repo->git('git checkout master');
    }
    catch (RuntimeException $e) {
        $msg->addError('INVALID_GIT_BINARY');
    }

    $_POST['new_branch_checkout'] = trim($_POST['new_branch_checkout']);

    if (!isset($_POST['new_branch_checkout'])) {
        $missing_fields[] = _AT('new_branch_checkout');
    }
    else if (!$missing_fields) {
        try {
            $repo->git('git checkout -b '. $_POST['new_branch_checkout']);
            $msg->addFeedback('CHECKED_OUT');
            $msg->printFeedbacks();
        }
        catch (RuntimeException $e) {
            if($repo->hasBranch($_POST['new_branch_checkout'])) {
                $msg->addError('BRANCH_ALREADY_EXISTS');
            }
            else {
                $msg->addError('CANNOT_CHECKOUT');
            }
        }
    }

    if ($missing_fields) {
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
    }
    $msg->printErrors();
}
/**
* Function to trim each array value using trim()
*/
function trim_value(&$value) {
    $value = trim($value);
}

//Adding modified, new and deleted files to respective arrays
$files = array();
$files['mod'] = array(); //Array to store modified files
$files['del'] = array(); //Array to store deleted files
$files['new'] = array(); //Array to store new files

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

//Adding and Committing files
if (isset($_POST['commit'])) {
    $_POST['commit_message'] = trim($_POST['commit_message']);
    if (!isset($_POST['commit_message'])) {
        $missing_fields[] = _AT('commit_message');
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        $msg->printErrors();
    }
    else {
        if (!empty($_POST['mod_select_file']))
        foreach($_POST['mod_select_file'] as $check) {
            try {
                $repo->git('git add '.$check);
            }
            catch (RuntimeException $e) {
                $msg->addError('UNABLE_TO_ADD_MOD');
            }
        }
        if (!empty($_POST['new_select_file']))
        foreach($_POST['new_select_file'] as $check) {
            try {
                $repo->git('git add '.$check);
            }
            catch (RuntimeException $e) {
                $msg->addError('UNABLE_TO_ADD_NEW');
            }
        }
        if (!empty($_POST['del_select_file']))
        foreach($_POST['del_select_file'] as $check) {
            try {
                $repo->git('git rm '.$check);
            }
            catch (RuntimeException $e) {
                $msg->addError('UNABLE_TO_DELETE');
            }
        }
        try {
            $repo->git('git commit -m "'.$_POST['commit_message'].'" --author="'.$_config['git_username'].' < '.$_config['git_email'].' >'.'"');
            $msg->addFeedback('COMMITTED');
            $msg->printFeedbacks();
        }
        catch (RuntimeException $e) {
            $msg->addError('CANNOT_COMMIT');
        }
        $msg->printErrors();
    }
}

//Pushing the committed files
if (isset($_POST['push'])) {
    try {
        $_POST['github_username'] = trim($_POST['github_username']);
        $_POST['github_password'] = trim($_POST['github_password']);
        $repo->git('git push https://'.$_POST["github_username"].':'.$_POST["github_password"].'@github.com/'.$_POST["github_username"].'/ATutor.git '.$_POST["new_branch_checkout"]);
        $msg->addFeedback('PUSHED');
        $msg->printFeedbacks();
    }
    catch (RuntimeException $e) {
        $msg->printErrors('CANNOT_PUSH');
    }
}

//Making a Pull Request out of the pushed changes
$client = new Github\Client();

if (isset($_POST['create_patch'])) {
    if (!isset($_POST['github_password'])) {
        $missing_fields[] = _AT('github_password');
    }
    if (!isset($_POST['github_username'])) {
        $missing_fields[] = _AT('github_username');
    }

    if(!empty($missing_fields)) {
        $missing_fields = implode(', ', $missing_fields);
        $msg->addError(array('EMPTY_FIELDS', $missing_fields));
        $msg->printErrors();
    }

    $method = Github\Client::AUTH_HTTP_PASSWORD;
    $username = $_POST['github_username'];
    $password = $_POST['github_password'];

    $client->authenticate($username, $password, $method);
    try {
        $pullRequest = $client->api('pull_request')->create('atutor', 'ATutor', array(
            'base'  => 'master',
            'head'  => $_POST['new_branch_checkout'],
            'title' => $_POST['pr_title'],
            'body'  => $_POST['pr_body']
        ));
        $msg->addFeedback('PR_SUCCESS');
        $msg->printFeedbacks();
    }
    catch (RuntimeException $e) {
        $msg->printErrors('PR_FAILED');
    }
}

?>
