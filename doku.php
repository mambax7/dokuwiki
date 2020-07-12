<?php
/**
 * DokuWiki mainscript
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

//  xdebug_start_profiling();

  if(!defined('DOKU_INC')) define('DOKU_INC',dirname(__FILE__).'/');
  require_once(DOKU_INC.'inc/init.php');
  require_once(DOKU_INC.'inc/common.php');
  require_once(DOKU_INC.'inc/events.php');
  require_once(DOKU_INC.'inc/pageutils.php');
  require_once(DOKU_INC.'inc/html.php');
  require_once(DOKU_INC.'inc/auth.php');
  require_once(DOKU_INC.'inc/actions.php');

  //import variables
  $QUERY = trim($_REQUEST['id']);
  $ID    = getID();
  $NS    = getNS($ID);
  $REV   = $_REQUEST['rev'];
  $ACT   = $_REQUEST['do'];
  $IDX   = $_REQUEST['idx'];
  $DATE  = $_REQUEST['date'];
  $RANGE = $_REQUEST['lines'];
  $HIGH  = $_REQUEST['s'];
  if(empty($HIGH)) $HIGH = getGoogleQuery();

  $TEXT  = cleanText($_POST['wikitext']);
  $PRE   = cleanText($_POST['prefix']);
  $SUF   = cleanText($_POST['suffix']);
  $SUM   = $_REQUEST['summary'];

  //sanitize revision
  $REV = preg_replace('/[^0-9]/','',$REV);

  //we accept the do param as HTTP header, too:
  if(!empty($_SERVER['HTTP_X_DOKUWIKI_DO'])){
    $ACT = trim(strtolower($_SERVER['HTTP_X_DOKUWIKI_DO']));
  }

  if(!empty($IDX)) $ACT='index';
  //set default #FIXME not needed here? done in actions?
  if(empty($ACT)) $ACT = 'show';

  //make infos about the selected page available
  $INFO = pageinfo();

  // handle debugging
  if($conf['allowdebug'] && $ACT == 'debug'){
    html_debug();
    exit;
  }

  //send 404 for missing pages if configured
  if($conf['send404'] &&
     ($ACT == 'show' || substr($ACT,0,7) == 'export_') &&
     !$INFO['exists']){
    header('HTTP/1.0 404 Not Found');
  }

  //prepare breadcrumbs (initialize a static var)
  breadcrumbs();

  // check upstream
  checkUpdateMessages();

  trigger_event('DOKUWIKI_STARTED',$tmp=array());

  //close session
    //start edit jayjay
	header('Content-Type: text/html; charset='._CHARSET); 
	include XOOPS_ROOT_PATH."/header.php";
	ob_start();
	//end edit jayjay
	
  //do the work
  act_dispatch($ACT);

  //start edit jayjay
	$xoops_doku_content = $GLOBALS['doku_content'];
	ob_end_clean();
	//define("_CHARSET_BASE", empty($xlanguage['charset_base'])?_CHARSET:$xlanguage['charset_base']);
	echo doku2xoops($xoops_doku_content);
	$GLOBALS['doku_header'] = doku2xoops($GLOBALS['doku_header']);
	$xoopsTpl->assign('xoops_module_header', $GLOBALS['doku_header']);

 //start xoops_pagetitle hack
 if ($conf['userewrite'] == '0') {
	  $REQUEST_URI = $_SERVER['REQUEST_URI'];
	  if(preg_match('/\?do\=/i', $REQUEST_URI)) {
		  $xoopsTpl->assign('xoops_pagetitle', $title.' | '.$xoopsModule->name());
	  } elseif(preg_match('/\?id\=/i', $REQUEST_URI)) {
		  $title = preg_replace('((.*?)\?id\=(.*))', '$2', $_SERVER['REQUEST_URI']);
		  $title = ucfirst(ereg_replace('_', ' ', $title));
		  if ($conf['useslash'] == '0') {
		  $title = ereg_replace(':', ' - ', $title);
		  }
		  if ($conf['useslash'] == '1') {
		  $title = ereg_replace('/', ' - ', $title);
		  }
		  $xoopsTpl->assign('xoops_pagetitle', $title.' | '.$xoopsModule->name());
	  } else {
		  $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->name());
	  }
 }
 elseif ($conf['userewrite'] == '1') {
	  $REQUEST_URI = $_SERVER['REQUEST_URI'];
	  if(preg_match('/doku.php/i', $REQUEST_URI) or preg_match('/\?do\=/i', $REQUEST_URI)) {
		  $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->name());
	   } else {
		  $title = preg_replace('((.*?)\/wiki\/(.*))', '$2', $REQUEST_URI);
		  $title = ucfirst(ereg_replace('_', ' ', $title));
		  if ($conf['useslash'] == '0') {
		  $title = ereg_replace(':', ' - ', $title);
		  }
		  if ($conf['useslash'] == '1') {
		  $title = ereg_replace('/', ' - ', $title);
		  }
		  $xoopsTpl->assign('xoops_pagetitle', $title.' | '.$xoopsModule->name());
	   }
 }
 elseif ($conf['userewrite'] == '2') {
	  $REQUEST_URI = $_SERVER['REQUEST_URI'];
	  if(preg_match('/\?do\=/i', $REQUEST_URI)) {
		  $xoopsTpl->assign('xoops_pagetitle', $title.' | '.$xoopsModule->name());
	  } elseif(preg_match('/\/doku.php\//i', $REQUEST_URI)) {
		  $title = preg_replace('((.*?)\/doku.php\/(.*))', '$2', $REQUEST_URI);
		  $title = ucfirst(ereg_replace('_', ' ', $title));
		  if ($conf['useslash'] == '0') {
		  $title = ereg_replace(':', ' - ', $title);
		  }
		  if ($conf['useslash'] == '1') {
		  $title = ereg_replace('/', ' - ', $title);
		  }
		  $xoopsTpl->assign('xoops_pagetitle', $title.' | '.$xoopsModule->name());
	  } else {
		  $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->name());
	  }
 }
 else {
	  $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->name());
 }
 //end xoops_pagetitle hack

	include_once XOOPS_ROOT_PATH."/footer.php";
  //end edit jayjay

  trigger_event('DOKUWIKI_DONE', $tmp=array());

//  xdebug_dump_function_profile(1);
?>
