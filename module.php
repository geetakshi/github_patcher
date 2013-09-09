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

/*******
 * doesn't allow this file to be loaded with a browser.
 */
if (!defined('AT_INCLUDE_PATH')) { exit; }

/******
 * this file must only be included within a Module obj
 */
if (!isset($this) || (isset($this) && (strtolower(get_class($this)) != 'module'))) { exit(__FILE__ . ' is not a Module'); }

/*******
 * assign the instructor and admin privileges to the constants.
 */
define('AT_PRIV_GITHUB_PATCHER',       $this->getPrivilege());
define('AT_ADMIN_PRIV_GITHUB_PATCHER', $this->getAdminPrivilege());


// the text to display on module "detail view" when sublinks are not available
$this->_pages['mods/github_patcher/index.php']['text']      = _AT('github_patcher_text');

/*******
 * add the admin pages when needed.
 */
if (admin_authenticate(AT_ADMIN_PRIV_GITHUB_PATCHER, TRUE) || admin_authenticate(AT_ADMIN_PRIV_ADMIN, TRUE)) {
    $this->_pages[AT_NAV_ADMIN] = array('mods/github_patcher/index_admin.php');
    $this->_pages['mods/github_patcher/index_admin.php']['title_var'] = 'github_patcher';
    $this->_pages['mods/github_patcher/index_admin.php']['parent']    = AT_NAV_ADMIN;

    $this->_pages['mods/github_patcher/index_admin.php']['children'] = array('mods/github_patcher/closed_patches.php', 'mods/github_patcher/open_patches.php', 'mods/github_patcher/patch_create.php');
    $this->_pages['mods/github_patcher/closed_patches.php']['title_var'] = 'closed_patches';
    $this->_pages['mods/github_patcher/closed_patches.php']['parent']   = 'mods/github_patcher/index_admin.php';

    $this->_pages['mods/github_patcher/closed_patches.php']['children'] = array('mods/github_patcher/open_patches.php', 'mods/github_patcher/patch_create.php');
    $this->_pages['mods/github_patcher/open_patches.php']['title_var'] = 'open_patches';
    $this->_pages['mods/github_patcher/open_patches.php']['parent']   = 'mods/github_patcher/closed_patches.php';

    $this->_pages['mods/github_patcher/open_patches.php']['children'] = array('mods/github_patcher/patch_create.php');
    $this->_pages['mods/github_patcher/patch_create.php']['title_var'] = 'create_patch';
    $this->_pages['mods/github_patcher/patch_create.php']['parent']   = 'mods/github_patcher/open_patches.php';
}



?>