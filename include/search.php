<?php
/**
 * DokuWiki xoops search integration
 *
 * @license		GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author		Andreas Gohr <andi@splitbrain.org>
 *				D.J.
 */
function &doku_search($queryarray, $andor, $limit, $offset, $userid = null) 
{	
	$ret = array();
	if (!$queryarray || $userid){
		return $ret;
	}
	
	global $module, $conf;
	
	if(!defined('DOKU_INC')) define('DOKU_INC', XOOPS_ROOT_PATH."/modules/dokuwiki/");
	require_once(DOKU_INC.'inc/init.php');
	require_once(DOKU_INC.'inc/common.php');
	require_once(DOKU_INC.'inc/pageutils.php');
	require_once(DOKU_INC.'inc/html.php');
	require_once(DOKU_INC.'inc/auth.php');
	require_once(DOKU_INC.'inc/actions.php');
	require_once(DOKU_INC.'inc/search.php');
	require_once(DOKU_INC.'inc/fulltext.php');
	
	if($andor=='AND'){
		$query = join(' ', $queryarray);
	}else{
		$query = join('|', $queryarray);
	}
	
	if($_GET["action"] == "showall"){
		header("location: ".XOOPS_URL."/modules/dokuwiki/doku.php?do=search&id=".$query);
		return;
	}
	
	//do quick pagesearch
	$data = array();
	$data = ft_pageLookup(cleanID(xoops2doku($query)));
	$i = 0;
	if(count($data)){
		sort($data);
		foreach($data as $id){
			$ret[$i]['image'] = "images/icon.gif";
			$ret[$i]['link'] = "doku.php?id=".$id;
			$ret[$i]['title'] = doku2xoops($id);
			$ret[$i]['time'] = "";
			$ret[$i]['uid'] = ""; // N/A
			$i++;
			if($i >= $limit) break;
		}
	}
	if(empty($ret) || count($ret) < $limit){
		$ret[$i]['image'] = "images/icon.gif";
		$ret[$i]['link'] = "doku.php?do=search&id=".$query;
		$ret[$i]['title'] = _MORE;
		$ret[$i]['time'] = "";
		$ret[$i]['uid'] = ""; // N/A
	}
	return $ret;
}

?>