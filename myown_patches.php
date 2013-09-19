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

/**
* Function to find the details of last installed patch
* @access  public
* @param   object $repo To access the functions providing git functionality
* @param   object $msg To manage error and feedback handling
* @return  array details of last installed patch
* @status  stable
* @author  Geetakshi Batra
*/
function find_last_patch($repo, $msg) {
    $last_patch = array();
    try {
        $repo->git('git checkout master');
    }
    catch (RuntimeException $e) {}
    try {
        $list_patch = $repo->git('git log --format=medium');
    }
    catch (RuntimeException $e) {}
    if(isset($list_patch)) {
        $split_result = preg_split("/\r\n|\n|\r/", $list_patch);
        $is_merge = substr($split_result['1'], 0, 6);
        if($is_merge == 'Merge:') {
            $author = $split_result['2'];
            $date = $split_result['3'];
            $commit = $split_result['5'];
            $last_patch = array (
                "date" => $date,
                "author" => $author,
                "commit" => $commit,
            );
        }
    }
    return $last_patch;
}

/**
* Function to install a patch
* @access  public
* @param   string $status To check if the patch is open or closed
* @param   object $repo To access the functions providing git functionality
* @param   object $msg To manage error and feedback handling
* @param   object $client To have interaction of code with GitHub
* @status  stable
* @author  Geetakshi Batra
*/
function add_patch($status, $msg, $repo, $client) {
    try {
        $pr_details = $client->api('pull_request')->show('atutor', 'ATutor', $_POST['id']);
    }
    catch (RuntimeException $e) {
        $msg->addError('CANNOT_CONNECT_TO_GITHUB');
    }
    if(!$msg->containsErrors()) {
        if($status == 'test') {
            try {
                $repo->git('git checkout -b '. $_POST['patch_test_branch']);
            }
            catch (RuntimeException $e) {
                if($repo->hasBranch($_POST['patch_test_branch'])) {
                    $msg->addError('BRANCH_ALREADY_EXISTS');
                }
                else {
                    $msg->addError('CANNOT_CHECKOUT');
                }
            }
        }
        else if($status == 'install') {
            $repo->git('git checkout master');
        }
        $username = $pr_details['head']['user']['login'];
        $remote = 'git://github.com/'.$username.'/ATutor.git';
        $branch = $pr_details['head']['ref'];
        try {
            try {
                $repo->git('git remote add '.$username.' '.$remote);
            }
            catch(RuntimeException $e) {}
            $repo->git('git fetch '.$username);
            $repo->git('git merge --no-ff '.$username.'/'.$branch);
            $msg->addFeedback('PATCH_INSTALLED_SUCCESSFULLY');
        }
        catch(RuntimeException $e) {
            try {
                $repo->git('git merge --abort');
                $msg->addError('UNABLE_TO_INSTALL');
            }
            catch(RuntimeException $e) {
                $msg->addError('FUNCTIONAL_ERROR');
            }
        }
    }
    if($msg->containsErrors()) {
        $msg->printErrors();
    }
    if($msg->containsFeedbacks()) {
        $msg->printFeedbacks();
    }
}

/**
* Function to uninstall a patch
* @access  public
* @param   object $repo To access the functions providing git functionality
* @param   object $msg To manage error and feedback handling
* @param   object $client To have interaction of code with GitHub
* @status  stable
* @author  Geetakshi Batra
*/
function remove_patch($msg, $repo, $client) {
    try {
        $repo->git('git checkout master');
        $log = $repo->git('git log --format=medium');
    }
    catch(RuntimeException $e) {
        $msg->addError('UNABLE_TO_UNINSTALL');
    }
    if(!$msg->containsErrors()) {
        $commits = preg_split("/\r\n|\n|\r/", $log);
        $is_merge = substr($commits['1'], 0, 6);
        if($is_merge == 'Merge:') {
            try {
                $repo->git('git reset --hard HEAD~1');
                $msg->addFeedback('PATCH_UNINSTALLED_SUCCESSFULLY');
            }
            catch (RuntimeException $e){
                $msg->addError('UNABLE_TO_UNINSTALL');
            }
        }
        else {
            $msg->addError('PATCH_NOT_INSTALLED');
        }
    }
    if($msg->containsErrors()) {
        $msg->printErrors();
    }
    if($msg->containsFeedbacks()) {
        $msg->printFeedbacks();
    }
}

/**
* Function to print details of a particular patch
* @access  public
* @param   string $state To check if the patch is open or closed
* @param   array $pr_values To fetch details of a particular patch
* @status  stable
* @author  Geetakshi Batra
*/
function print_row($state, $pr_values) {
?>
    <tr>
        <td><input type="radio" name="id" value="<?php echo $pr_values['number']; ?>"></td>
        <td><label><?php echo $pr_values['number']; ?></label></td>
        <td><label><?php echo $pr_values['title']; ?></label></td>
        <td><label><?php echo $pr_values['state']; ?></label></td>
        <td><label><?php if($state == 'closed')
                            echo $pr_values['merged_at'];
                          else if($state == 'open')
                            echo $pr_values['updated_at'];
                    ?></label></td>
        <td><label><?php echo $pr_values['user']['login']; ?></label></td>
    </tr>
<?php }

/**
* Function to print list of patches available
* @access  public
* @param   object $repo To access the functions providing git functionality
* @param   string $state To check if the patch is open or closed
* @param   object $msg To manage error and feedback handling
* @param   int $per_page Number of patches to appear on a single page
* @status  stable
* @author  Geetakshi Batra
*/
function list_patches($repo, $state, $msg, $per_page = 20) { ?>
    <form name="form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table summary="" class="data" rules="cols" align="center" style="width: 100%;">
    <thead>
    <tr>
        <th scope="col">&nbsp;</th>
        <th scope="col"><?php echo _AT('patch_id'); ?></th>
        <th scope="col"><?php echo _AT('patch_title'); ?></th>
        <th scope="col"><?php echo _AT('pr_status'); ?></th>
        <th scope="col"><?php if($state == 'closed')
                            echo _AT('merged_at');
                          else if($state == 'open')
                            echo _AT('updated_at');
                    ?></th>
        <th scope="col"><?php echo _AT('author'); ?></th>
    </tr>
    </thead>

    <?php
        $current_page = (@$_GET['page'])?($_GET['page']):1;
        $client = new Github\Client();
        try {
            $PullRequest = $client->api('pull_request')->all('atutor', 'ATutor', $state, $current_page, $per_page);
            foreach($PullRequest as $pr_values) {
                print_row($state, $pr_values);
            }
        }
        catch(RuntimeException $e) {
            $msg->printErrors('CANNOT_CONNECT_TO_GITHUB');
        }
    ?>
    </table>
    <table style="float:right">
    <tr><td>
    <label style="float:right;"><?php echo _AT('install_label');?></label>
    </td></tr>
    <tr><td>
    <input type="submit" name="install" value="<?php echo _AT('install_selected'); ?>" style="float:right;" />
    </td></tr>
    <tr><td>
    <label style="float:right;"><?php echo _AT('uninstall_label');?></label>
    </td></tr>
    <tr><td>
    <input type="submit" name="uninstall" value="<?php echo _AT('uninstall_last_installed'); ?>" style="float:right;"/>
    </td></tr>
    <tr><td>
    <h6 style="float:right;"><?php $result = find_last_patch($repo, $msg); echo _AT('last_patch_detail'); ?></h6>
    </td></tr>
    <tr><td>
    <p style="float:right;"><?php if(!empty($result)) echo $result['commit']; else echo _AT('PATCH_NOT_INSTALLED'); ?></p>
    </td></tr>
    <tr><td>
    <p style="float:right;"><?php echo $result['date']; ?></p>
    </td></tr>
    <tr><td>
    <p style="float:right;"><?php echo $result['author']; ?></p>
    </td></tr>
    </table>
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
