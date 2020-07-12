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
require_once( XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->getVar("dirname").'/conf/local.php' );

function doku_getPathStatus($path, $isFile = false)
{
	if(empty($path)) return false;
	if(@is_writable($path)){
		$path_status = _AM_DOKUWIKI_AVAILABLE;
	}elseif(!@is_dir($path) && empty($isFile)){
		$path_status = _AM_DOKUWIKI_NOTAVAILABLE." <a href=index.php?op=create&amp;path=$path>"._AM_DOKUWIKI_CREATE.'</a>';
	}elseif(!@is_file($path) && !empty($isFile)){
		$path_status = _AM_DOKUWIKI_NOTAVAILABLE." <a href=index.php?op=create&amp;path=$path&amp;isfile=$isFile>"._AM_DOKUWIKI_CREATE.'</a>';
	}else{
		$path_status = _AM_DOKUWIKI_NOTWRITABLE." <a href=index.php?op=setperm&amp;path=$path>"._AM_DOKUWIKI_SETMPERM.'</a>';
	}
	return $path_status;
}

function doku_createdir($target, $mode=0777)
{
	clearstatcache();
	// http://www.php.net/manual/en/function.mkdir.php
	return is_dir($target) or ( doku_createdir(dirname($target), $mode) and mkdir($target, $mode) );
}

function doku_createfile($target, $mode=0777)
{
	clearstatcache();
	if(!doku_createdir(dirname($target), $mode)) return false;
	if(!$file = fopen($target, "a")) return false;
	fclose($file);
	return true;
}

function doku_chmod($target, $mode = 0777)
{
	return @chmod($target, $mode);
}

xoops_cp_header();
loadModuleAdminMenu(0);

$op = isset($_POST['op']) ? $_POST['op'] : ( isset($_GET['op']) ? $_GET['op'] : "" );

switch ($op) {

    case "update":
        header("location: update.php?clear=".(@$_GET["clear"]));
        exit();
        break;

    case "create":
		if (isset($_GET['path'])) $path = $_GET['path'];
		if(empty($_GET['isfile'])){
	        $res = doku_createdir($path);
        }else{
	        $res = doku_createfile($path);
        }
        $msg = ($res)?_AM_DOKUWIKI_CREATED:_AM_DOKUWIKI_NOTCREATED;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        exit();
        break;

    case "setperm":
        $res = doku_chmod($path, 0777);
        $msg = ($res)?_AM_DOKUWIKI_PERMSET:_AM_DOKUWIKI_PERMNOTSET;
        redirect_header('index.php', 2, $msg . ': ' . $path);
        exit();
        break;
        
	default:
		echo "
			<style type=\"text/css\">
			label,text {
				display: block;
				float: left;
				margin-bottom: 2px;
			}
			label {
				text-align: right;
				width: 150px;
				padding-right: 20px;
			}
			br {
				clear: left;
			}
			</style>
		";
		echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_DOKUWIKI_CONFIG . "</legend>";
		echo "<div style='padding: 8px;'>";
		echo "<label>" . "<strong>PHP Version:</strong>" . ":</label><text>" . phpversion() . "</text><br />";
		echo "<label>" . "<strong>MySQL Version:</strong>" . ":</label><text>" . mysql_get_server_info() . "</text><br />";
		echo "<label>" . "<strong>XOOPS Version:</strong>" . ":</label><text>" . XOOPS_VERSION . "</text><br />";
		echo "<label>" . "<strong>DokuWiki Version:</strong>" . ":</label><text>" . $xoopsModule->getInfo('version') . "</text><br />";
		echo "</div>";
		
		echo "<div style='padding: 8px;'>";
		$path_savedir = $conf['savedir']."/";
		$path_status = doku_getPathStatus($path_savedir);
		echo "<label>savedir:</label><text>". $path_savedir . ' ( ' . $path_status . ' )';
		echo "</text><br />";
		$path = $conf['datadir']."/";
		$path_status = doku_getPathStatus($path);
		echo "<label>datadir:</label><text>". $path . ' ( ' . $path_status . ' )';
		echo "</text><br />";
		$path = $conf['olddir']."/";
		$path_status = doku_getPathStatus($path);
		echo "<label>olddir:</label><text>". $path . ' ( ' . $path_status . ' )';
		echo "</text><br />";
		$path = $conf['cachedir'];
		$path_status = doku_getPathStatus($path);
		echo "<label>cachedir:</label><text>". $path . ' ( ' . $path_status . ' )';
		echo "</text><br />";
		$path = $conf['savedir']."/changes.log";
		$path_status = doku_getPathStatus($path, true);
		echo "<label>changelog:</label><text>". $path . ' ( ' . $path_status . ' )';
		echo "</text><br />";
		echo "</div>";
		echo "<h2>"._AM_DOKUWIKI_UPDATE_IDX." - "._ALL.": <a href='index.php?op=update&amp;clear=1'>"._YES."</a> <a href='index.php?op=update'>"._NO."</a>";
		echo "</fieldset><br />";
		break;
}

xoops_cp_footer();
?>
