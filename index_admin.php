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

require_once('php-git-repo/lib/PHPgit/Repository.php');

// make sure user is allowed to see this page (admins only)

admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER);


if (isset($_POST['submit'])) {
    $_POST['path_to_git_exec'] = trim($_POST['path_to_git_exec']);
    $_POST['git_email'] = trim($_POST['git_email']);
    $_POST['git_username'] = trim($_POST['git_username']);

    if (!$_POST['path_to_git_exec']){
        $msg->addError('GITHUB_PATCHER_GIT_EXE_EMPTY');
    }

    if (!$_POST['git_username']){
        $msg->addError('GITHUB_PATCHER_GIT_USERNAME_EMPTY');
    }

    if (!$_POST['git_email']){
        $msg->addError('GITHUB_PATCHER_GIT_EMAIL_EMPTY');
    }

    //storing the path to git executable, git username, and git email-id in the database
    if (!$msg->containsErrors()) {
        $_POST['path_to_git_exec'] = $addslashes($_POST['path_to_git_exec']);
        $git_repo = new PHPGit_Repository('../../', false, array('git_executable' => '"'.$_POST['path_to_git_exec'].'"'));
        try {
            $git_repo->git('git status');
        }
        catch (RuntimeException $e) {
            $msg->addError('INVALID_GIT_BINARY');
        }
        if(!$msg->containsErrors()) {
            queryDB('REPLACE INTO %sconfig VALUES ("path_to_git_exec", "%s")', array(TABLE_PREFIX, $_POST['path_to_git_exec']));
            $msg->addFeedback('GITHUB_PATCHER_GIT_EXEC_SAVED');
        }

        $_POST['git_username'] = $addslashes($_POST['git_username']);
        queryDB('REPLACE INTO %sconfig VALUES ("git_username", "%s")', array(TABLE_PREFIX, $_POST['git_username']));
        $msg->addFeedback('GITHUB_PATCHER_GIT_USERNAME_SAVED');

        $_POST['git_email'] = $addslashes($_POST['git_email']);
        queryDB('REPLACE INTO %sconfig VALUES ("git_email", "%s")', array(TABLE_PREFIX, $_POST['git_email']));
        $msg->addFeedback('GITHUB_PATCHER_GIT_EMAIL_SAVED');

        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
}

require (AT_INCLUDE_PATH.'header.inc.php');

?>
<div class="input-form">
<div class="row">
<?php if ($_config['path_to_git_exec'] && $_config['git_email'] && $_config['git_username']): ?>
        <p><?php echo _AT('github_patcher_text'); ?></p>
<?php else : ?>
    <?php if (!isset($_config['path_to_git_exec'])): ?>
        <p><?php echo _AT('github_patcher_missing_git_exec');  ?></p>
    <?php endif; ?>
    <?php if (!isset($_config['git_email'])): ?>
        <p><?php echo _AT('github_patcher_missing_git_email');  ?></p>
    <?php endif; ?>
    <?php if (!isset($_config['git_username'])): ?>
            <p><?php echo _AT('github_patcher_missing_git_username');  ?></p>
    <?php endif; ?>
<?php endif; ?>
</div>
</div>

<form action="<?php  $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="input-form">
        <div class="row">
            <p><label for="path_to_git_exec"><?php echo _AT('path_to_git_exec'); ?></label></p>
            <small><p> <?php echo _AT('git_executable_description'); ?> </p></small>
            <input type="text" name="path_to_git_exec" value="<?php echo $_config['path_to_git_exec']; ?>" id="path_to_git_exec" size="30" style="min-width: 65%;" />
        </div>
        <div class="row">
            <p><label for="git_username"><?php echo _AT('git_username'); ?></label></p>
            <input type="text" name="git_username" value="<?php echo $_config['git_username']; ?>" id="git_username" size="30" style="min-width: 65%;" />
        </div>
        <div class="row">
            <p><label for="git_email"><?php echo _AT('git_email'); ?></label></p>
            <input type="text" name="git_email" value="<?php echo $_config['git_email']; ?>" id="git_email" size="30" style="min-width: 65%;" />
        </div>
        <div class="row buttons">
            <input type="submit" name="submit" value="<?php echo _AT('save'); ?>"  />
        </div>
    </div>
</form>

<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>