<?php
// $Id: xoops_version.php,v 1.9 2004/09/27 16:57:22 phppp Exp $
//  ------------------------------------------------------------------------ //
//                        DOKUWIKI for XOOPS                                 //
//             Copyright (c) 2004 Xoops China Community                      //
//                    <http://www.xoops.org.cn/>                             //
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
// Author: D.J.(phppp) php_pp@hotmail.com                                    //
// URL: http://xoops.org.cn, http://xoopsforge.com                           //
// ------------------------------------------------------------------------- //

$modversion['name'] = _MI_DOKUWIKI_NAME;
$modversion['version'] = 2.20;
$modversion['description'] = _MI_DOKUWIKI_DESC;
$modversion['credits'] = "Andreas Gohr for DokuWiki Project; Marco Garbelini for first XOOPS DokuWiki module (http://www.garbelini.net/modules/dokuwiki/)";
$modversion['author'] = "jayjay (http://www.denktenk.com) - Based on work by D.J. (http://xoopsforge.com)";
$modversion['image'] = "images/dokuwiki.png";
$modversion['dirname'] = "dokuwiki";
$modversion['license']      = "GNU/GPL";
 
//Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Main
$modversion['hasMain'] = 1;

// Configs
$modversion["config"][] = array(
	"name" 			=> "savedir",
	"title" 		=> "_MI_DOKUWIKI_SAVEDIR",
	"description" 	=> "_MI_DOKUWIKI_SAVEDIR_DESC",
	"formtype" 		=> "textbox",
	"valuetype" 	=> "text",
	"default" 		=> $modversion['dirname']);
	
$modversion["config"][] = array(
	"name" 			=> "datadir",
	"title" 		=> "_MI_DOKUWIKI_DATADIR",
	"description" 	=> "_MI_DOKUWIKI_DATADIR_DESC",
	"formtype" 		=> "textbox",
	"valuetype" 	=> "text",
	"default" 		=> "pages");
	
$modversion["config"][] = array(
	"name" 			=> "olddir",
	"title" 		=> "_MI_DOKUWIKI_OLDDIR",
	"description" 	=> "_MI_DOKUWIKI_OLDDIR_DESC",
	"formtype" 		=> "textbox",
	"valuetype" 	=> "text",
	"default" 		=> "attic");
	
$modversion["config"][] = array(
	"name" 			=> "cachetime",
	"title" 		=> "_MI_DOKUWIKI_CACHETIME",
	"description" 	=> "_MI_DOKUWIKI_CACHETIME_DESC",
	"formtype" 		=> "textbox",
	"valuetype" 	=> "int",
	"default" 		=> 24);
	
$modversion["config"][] = array(
	"name" 			=> "dformat",
	"title" 		=> "_MI_DOKUWIKI_DFORMAT",
	"description" 	=> "_MI_DOKUWIKI_DFORMAT_DESC",
	'formtype' => 'select',
	'valuetype' => 'text',
	'options' => array(
					_DATESTRING=>_DATESTRING,
					_MEDIUMDATESTRING=>_MEDIUMDATESTRING,
					_SHORTDATESTRING=>_SHORTDATESTRING),
	'default' => _DATESTRING);
	
//$modversion["config"][] = array( //edit jayjay: spellchecker is updated in newer dokuwiki releases! - Please refer to conf/dokuwiki.php
//	"name" 			=> "spellchecker",
//	"title" 		=> "_MI_DOKUWIKI_SPELLCHECKER",
//	"description" 	=> "_MI_DOKUWIKI_SPELLCHECKER_DESC",
//	"formtype" 		=> "yesno",
//	"valuetype" 	=> "int",
//	"default" 		=> 0);
	
//$modversion["config"][] = array( //edit jayjay: there is a new discussion plugin now!
//	"name" 			=> "enable_discussion",
//	"title" 		=> "_MI_DOKUWIKI_DISCUSSION",
//	"description" 	=> "_MI_DOKUWIKI_DISCUSSION_DESC",
//	"formtype" 		=> "yesno",
//	"valuetype" 	=> "int",
//	"default" 		=> 1);

// Search
$modversion['hasSearch'] = 1;
$modversion['search']['file'] = "include/search.php";
$modversion['search']['func'] = "doku_search";
?>
