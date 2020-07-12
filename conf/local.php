<?php
global $conf;

$conf['useacl']      = 1;                //Use Access Control Lists to restrict access?
$conf['superuser']   = (is_object($GLOBALS["xoopsUser"]) && $GLOBALS["xoopsUser"]->isAdmin()) ? $GLOBALS["xoopsUser"]->uname() : '!!not set!!';
$conf['lang']        = strtolower(_LANGCODE);              //your language
switch($GLOBALS["xoopsConfig"]['language']){
	case "schinese":
	case "schinese_utf8":
		$conf['lang'] = "zh";              //Simplified Chinese
		break;
	case "tchinese":
	case "tchinese_utf8":
		$conf['lang'] = "zh-tw";              //Traditional Chinese
		break;
}

$GLOBALS['lang']['encoding'] = "UTF-8"; //a trick

$conf['authtype']    = 'xoops';          //which authentication backend should be used
$conf['title']       = is_object( @$GLOBALS["xoopsModule"] ) ? xoops2doku($GLOBALS["xoopsModule"]->getVar("name")) : "Dokuwiki";

if(!empty($GLOBALS["xoopsModule"]) && "dokuwiki" == $GLOBALS["xoopsModule"]->getVar("dirname")){
	$DokuConfig =& $GLOBALS["xoopsModuleConfig"];
}else{
	$module_handler =& xoops_gethandler('module');
	$doku =& $module_handler->getByDirname('dokuwiki');
	$config_handler =& xoops_gethandler('config');
	$DokuConfig = $config_handler->getConfigsByCat(0, $doku->getVar('mid'));
	unset($doku);
}
foreach(array_keys($DokuConfig) as $key){
	$conf[$key] = xoops2doku($DokuConfig[$key]);
}
$conf['savedir']     	= XOOPS_UPLOAD_PATH."/".$conf['savedir'];

$conf['confdir']     	= $conf['savedir'];          //where to store config file
$conf['mediadir'] 	= $conf['savedir'].'/media/';	//edit jayjay: avoid clutter!
$conf['metadir'] 		= $conf['savedir'].'/meta/'; //edit jayjay: avoid clutter!
$conf['lockdir'] 		= $conf['savedir'].'/locks/'; //edit jayjay: avoid clutter!
$conf['datadir'] 		= $conf['savedir'].'/'.$conf['datadir'];
$conf['olddir'] 		= $conf['savedir'].'/'.$conf['olddir'];
$conf['cachedir'] 		= $conf['savedir'].'/cache/';          //where to store all the files

$conf['camelcase']   = 0;                 //Use CamelCase for linking? (I don't like it) 0|1
$conf['signature']   = ' --- //[[@MAIL@|@NAME@]] @DATE@//'; //signature see wiki:config for details
?>
