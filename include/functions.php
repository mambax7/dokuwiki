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

require_once XOOPS_ROOT_PATH."/Frameworks/art/functions.ini.php";

function xoops2doku($text){
	load_functions("locale");
	return XoopsLocal::convert_encoding($text, $GLOBALS['lang']['encoding'], @_CHARSET_BASE);
}

function doku2xoops($text){
	load_functions("locale");
	$text = XoopsLocal::convert_encoding($text, @_CHARSET_BASE, $GLOBALS['lang']['encoding']);
	return $text;
}
?>