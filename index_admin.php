<?php
define('AT_INCLUDE_PATH', '../../include/');
require (AT_INCLUDE_PATH.'vitals.inc.php');

// make sure user is allowed to see this page (admins only)

admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER);

if (isset($_POST['submit'])) {
    $_POST['path_to_git_exec'] = trim($_POST['path_to_git_exec']);

    if (!$_POST['path_to_git_exec']){
        $msg->addError('GITHUB_PATCHER_ADD_EMPTY');
    }

    if (!$msg->containsErrors()) {
        $_POST['path_to_git_exec'] = $addslashes($_POST['path_to_git_exec']);
        queryDB('REPLACE INTO %sconfig VALUES ("path_to_git_exec", "%s")', array(TABLE_PREFIX, $_POST['path_to_git_exec']));
        $msg->addFeedback('GITHUB_PATCHER_GIT_EXEC_SAVED');

        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
}

require (AT_INCLUDE_PATH.'header.inc.php');

?>

<?php if ($_config['path_to_git_exec']): ?>
    <div class="input-form">
        <div class="row">
            <p><?php echo _AT('github_patcher_text'); ?></p>
        </div>
    </div>
<?php else: ?>
    <div class="input-form">
        <div class="row">
            <p><?php echo _AT('github_patcher_missing_git_exec');  ?></p>
        </div>
    </div>
<?php endif; ?>

<form action="<?php  $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="input-form">
        <div class="row">
            <p><label for="path_to_git_exec"><?php echo _AT('path_to_git_exec'); ?></label></p>
            <small><p>On most Unix system, it's '/usr/bin/git'. On Windows, it may be 'C:\Program Files\Git\bin'. </p></small>
            <input type="text" name="path_to_git_exec" value="<?php echo $_config['path_to_git_exec']; ?>" id="path_to_git_exec" size="30" style="min-width: 65%;" />
        </div>
        <div class="row buttons">
            <input type="submit" name="submit" value="<?php echo _AT('save'); ?>"  />
        </div>
    </div>
</form>

<?php require (AT_INCLUDE_PATH.'footer.inc.php'); ?>