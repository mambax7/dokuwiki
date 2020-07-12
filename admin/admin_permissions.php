<?php
// $Id: xoops_version.php,v 1.8 2005/06/03 01:35:02 phppp Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: phppp (D.J.)                                                      //
// URL: http://xoopsforge.com, http://xoops.org.cn                           //
// ------------------------------------------------------------------------- //
include 'admin_header.php';
require_once( XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->getVar("dirname").'/include/functions.php' );
xoops_cp_header();

loadModuleAdminMenu(1);

$title_of_form = _AM_DOKUWIKI_PERM;
$perm_name = "global";
$anonymous = true;
$perm_desc = "";

$module_id = $xoopsModule->getVar('mid');
include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';
$form = new XoopsGroupPermForm($title_of_form, $module_id, $perm_name, $perm_desc, 'admin/admin_permissions.php', $anonymous);

$form->addItem(1, _AM_DOKUWIKI_PERM_READ);
$form->addItem(2, _AM_DOKUWIKI_PERM_EDIT);
$form->addItem(3, _AM_DOKUWIKI_PERM_CREATE);
$form->addItem(4, _AM_DOKUWIKI_PERM_UPLOAD);
$form->addItem(5, _AM_DOKUWIKI_PERM_DELETE);

$form->display();
xoops_cp_footer();
?>