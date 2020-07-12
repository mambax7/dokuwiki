<?php
/**
 * AJAX Backend for indexmenu
 *
 * @author Samuele Tognini <samuele@netsons.org>
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
 
//fix for Opera XMLHttpRequests
if(!count($_POST) && @$HTTP_RAW_POST_DATA){
  parse_str($HTTP_RAW_POST_DATA, $_POST);
}
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../../').'/');
require_once(DOKU_INC.'inc/init.php');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('INDEXMENU_IMG_RELDIR')) define('INDEXMENU_IMG_RELDIR',DOKU_BASE.'lib/plugins/indexmenu/images');
if(!defined('INDEXMENU_IMG_ABSDIR')) define('INDEXMENU_IMG_ABSDIR',DOKU_PLUGIN."indexmenu/images");
//close session
session_write_close();

$ajax_indexmenu=new ajax_indexmenu_plugin;
$ajax_indexmenu->render();

class ajax_indexmenu_plugin {
  /**
   * Output
   *
   * @author Samuele Tognini <samuele@netsons.org>
   */ 

  function render () {
    $req=$_REQUEST['req']; 
    $succ=false;
    //send the zip
    if ($req == 'send' and isset($_REQUEST['t'])) $succ=$this->send_theme($_REQUEST['t']);
    if ($succ) return true;
    
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, max-age=3600');
    header('Pragma: public');
    //list themes
    if ($req == 'local') print $this->local_themes();
    if (isset($_REQUEST['id'])) {
      //print toc preview
      if ($req == 'toc') print $this->print_toc($_REQUEST['id']);
      //print index
      if ($req == 'index') print $this->print_index($_REQUEST['id']);
    }
  }

  /**
   * Print a list of local themes
   *
   * @author Samuele Tognini <samuele@netsons.org>
   */ 

  function local_themes() {
    $dir=INDEXMENU_IMG_ABSDIR;
    $list='indexmenu,'.'http://'.$_SERVER['SERVER_NAME'].",".INDEXMENU_IMG_RELDIR.",";
    $data=array();
    $handle=@opendir($dir);
    while (false !== ($file = readdir($handle))) {
      if ( is_dir ($dir.'/'.$file)
	   && $file != "."
	   && $file != ".."
	   && $file != "repository"
	   && $file != "tmp"
	   ) {
	$data[]=$file;
      }
    }
    closedir($handle);
    sort($data);
    $list .= implode(",",$data);
    return $list;
  }

  /**
   * Send a zipped theme
   *
   * @author Samuele Tognini <samuele@netsons.org>
   */

  function send_theme ($file) {
    require_once(DOKU_PLUGIN.'indexmenu/syntax/indexmenu.php');
    $idxm=new syntax_plugin_indexmenu_indexmenu();
    //clean the file name
    $file=cleanID($file);
    //check config
    if(!$idxm->getConf('be_repo') || empty($file)) return false;
    $repodir=INDEXMENU_IMG_ABSDIR."/repository";
    $zipfile=$repodir."/$file.zip";
    $localtheme=INDEXMENU_IMG_ABSDIR."/$file/";
    //theme does not exists
    if (!file_exists($localtheme)) return false;
    if (!io_mkdir_p($repodir)) return false;
    $lm=@filemtime($zipfile);
    //no cached zip or older than 1 day
    if ($lm < time()-(60*60*24)) {
      //create the zip
      require_once(DOKU_PLUGIN."indexmenu/inc/pclzip.lib.php");
      @unlink($zipfile);
      $zip=new PclZip($zipfile);
      $status=$zip->add($localtheme,PCLZIP_OPT_REMOVE_ALL_PATH);
      //error
      if ($status == 0) return false;
    }
    $len = (int) filesize($zipfile);
    //don't send large zips
    if ($len > 2*1024*1024) return false;
    //headers
    header('Cache-Control: must-revalidate, no-transform, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="'.basename($zipfile).'";');
    header("Content-Transfer-Encoding: binary");
    //send zip
    $fp=@fopen($zipfile, 'rb');
    if ($fp) {
      $ct=@fread($fp, $len); 
      print $ct;
    }
    @fclose($fp);
    return true;
  }

  /**
   * Print a toc preview
   *
   * @author Samuele Tognini <samuele@netsons.org>
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  function print_toc($id) {
    require_once(DOKU_INC.'inc/parser/xhtml.php');
    $id=cleanID($id);
    //fix for DW 2006-11-06
    $this->fixacl();
    if (auth_quickaclcheck($id) < AUTH_READ) return;
    $meta = p_get_metadata($id);
    $toc = $meta['description']['tableofcontents'];
    $out .= '<div class="tocheader toctoggle">'.DOKU_LF;
    if (count($toc)>1) {
      $out .= $this->render_toc($toc);
    } else {
      $out .= '<a href="'.wl($id).'">';
      $out .= ($meta['title']) ? htmlspecialchars($meta['title']) :  htmlspecialchars(noNS($id));
      $out .= '</a>'.DOKU_LF;
      if ($meta['description']['abstract']) {
	$out .= '</div>'.DOKU_LF;
	$out .= '<div class="indexmenu_toc_inside">'.DOKU_LF;
	$out .=  htmlspecialchars($meta['description']['abstract']);
	$out .= '</div>'.DOKU_LF;
      }
    }
    $out .= '</div>'.DOKU_LF;
    return $out;
  }

  /**
   * Return the TOC rendered to XHTML
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  function render_toc($toc){
    global $lang;
    $r = new Doku_Renderer_xhtml;
    $r->toc = $toc;
    $out .= $lang['toc'];
    $out .= '</div>'.DOKU_LF;
    $out .= '<div class="indexmenu_toc_inside">'.DOKU_LF;
    $out .= html_buildlist($r->toc,'toc',array($this,'_tocitem'));
    $out .= '</div>'.DOKU_LF;
    return $out;
  }

  /**
   * Callback for html_buildlist
   */
  function _tocitem($item){
    $id=cleanID($_POST['id']);
    return '<span class="li"><a href="'.wl($id,'#'.$item['hid'],false,'').'" class="toc">'.
      htmlspecialchars($item['title']).'</a></span>';
  }

  /**
   * Print index nodes
   *
   * @author Samuele Tognini <samuele@netsons.org>
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  function print_index($ns) {
    require_once(DOKU_PLUGIN.'indexmenu/syntax/indexmenu.php');
    //fix for DW 2006-11-06
    $this->fixacl();
    global $conf;
    $level=-1;
    $max=0;
    $data = array();
    if ($_REQUEST['max'] > 0) {
      $max=$_REQUEST['max'];
      $level=$max;
    }
    $nss= ($_REQUEST['nss']) ? cleanID($_REQUEST['nss']) : '';
    $out="ajxnodes = new Array(";
    $idxm=new syntax_plugin_indexmenu_indexmenu();
    $idxm->sort=$_REQUEST['sort'];
    $idxm->msort=$_REQUEST['msort'];
    $idxm->rsort=$_REQUEST['rsort'];
    $idxm->nsort=$_REQUEST['nsort'];
    $fsdir="/".utf8_encodeFN(str_replace(':','/',$ns));
    $opts= array('level' => $level,
		 'nons' => 0,
		 'nss' => array(array($nss,1)),
		 'max' => $max,
		 'js' => false,
		 'skip_index' => $idxm->getConf('skip_index'),
		 'skip_file' => $idxm->getConf('skip_file'),
		 'headpage' => $idxm->getConf('headpage'),
		 'hide_headpage' => $idxm->getConf('hide_headpage')
		 );
    if ($idxm->sort||$idxm->msort||$idxm->rsort) {
      $idxm->_search($data,$conf['datadir'],array($idxm,'_search_index'),$opts,$fsdir);
    } else {
      search($data,$conf['datadir'],array($idxm,'_search_index'),$opts,$fsdir);
    }
    $nodes=$idxm->_jsnodes($data,'',0);
    $out.=rtrim($nodes[0],",");
    $out.=");";
    return $out;
  }

  /**
   * fix DW 2006-11-06 acls
   *
   * @author Samuele Tognini <samuele@netsons.org>
   */
  function fixacl() {
    global $conf,$AUTH_ACL;
    require_once(DOKU_INC.'inc/auth.php');
    if (!is_null($AUTH_ACL)) return;
    if($conf['acl'] && is_readable(DOKU_CONF.'acl.auth.php')){
      if(isset($_SERVER['REMOTE_USER'])){
	$AUTH_ACL = str_replace('@USER@',$_SERVER['REMOTE_USER'],$AUTH_ACL);
      }
    } else {
      $AUTH_ACL = array();
    }
  }

}
