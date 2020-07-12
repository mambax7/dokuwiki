<?php

/**
 * Info Indexmenu: Displays the index of a specified namespace. 
 *
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author      Samuele Tognini <samuele@cli.di.unipi.it>
 * 
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('INDEXMENU_IMG_RELDIR')) define('INDEXMENU_IMG_RELDIR',DOKU_BASE.'lib/plugins/indexmenu/images');
if(!defined('INDEXMENU_IMG_ABSDIR')) define('INDEXMENU_IMG_ABSDIR',realpath(dirname(__FILE__)."/images"));
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/search.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_indexmenu extends DokuWiki_Syntax_Plugin {
  
  /**
   * return some info
   */
  function getInfo(){
    return array(
		 'author' => 'Samuele Tognini',
		 'email'  => 'samuele@cli.di.unipi.it',
		 'date'   => rtrim(io_readFile(DOKU_PLUGIN.'indexmenu/VERSION.txt')),
		 'name'   => 'Indexmenu',
		 'desc'   => 'Insert the index of a specified namespace.',
		 'url'    => 'http://wiki.splitbrain.org/plugin:indexmenu'
		 );
  }
  
  /**
   * What kind of syntax are we?
   */
  function getType(){
    return 'substition';
  }

  function getPType(){
    return 'block';
  }
 
  /**
   * Where to sort in?
   */
  function getSort(){
    return 138;
  }
 
  /**
   * Connect pattern to lexer
   */
  function connectTo($mode) {
    $this->Lexer->addSpecialPattern('{{indexmenu>.+?}}',$mode,'plugin_indexmenu');
  }
      
  /**
   * Handle the match
   */
  function handle($match, $state, $pos, &$handler){
    $theme="default/";
    $ns=".";
    $level = -1;
    $nons = true;
    $gen_id='random';
    $maxjs=0;
    $max=0;
    $jsajax='';
    $nss=array();
    $sort='nosort';
    $match = substr($match,12,-2);
    //split namespace,level,theme
    $match = preg_split('/\|/u', $match, 2);
    //split optional namespaces
    $nss_temp=preg_split("/ /u",$match[0],-1,PREG_SPLIT_NO_EMPTY);
    //Array optional namespace => level
    for ($i = 1; $i < count($nss_temp); $i++) {
      $nsss=preg_split("/#/u",$nss_temp[$i]);
      $nss[]=array($this->_parse_ns($nsss[0]),(is_numeric($nsss[1])) ? $nsss[1] : $level);
    }
    //split main requested namespace
    if (preg_match('/(.*)#(\S*)/u',$nss_temp[0],$ns_opt)) {
      //split level
      $ns=$ns_opt[1];
      if (is_numeric($ns_opt[2])) $level=$ns_opt[2];
    } else {
      $ns=$nss_temp[0];
    }
    $ns=$this->_parse_ns($ns);
    $opts=preg_split('/ /u',$match[1]);
    //noscroll option
    $noscroll=in_array('noscroll',$opts);
    //Open at current namespace option
    $navbar=in_array('navbar',$opts);
    if ($navbar) $gen_id="1";
    //nocookie option
    $nocookie=in_array('nocookie',$opts);
    //plugin options
    $nons = in_array('nons',$opts);
    //disable toc preview
    $notoc = in_array('notoc',$opts);
    //sort option
    if (in_array('tsort',$opts)) {
      $sort='tsort';
      $jsajax .= "&sort=".$sort;
    }
    //javascript option
    $js = in_array('js',$opts);
    if (!$js) {
      //split theme
      if (preg_match('/js#(\S*)/u',$match[1],$tmp_theme) > 0) {
	if (is_dir(INDEXMENU_IMG_ABSDIR."/".$tmp_theme[1])) {
	  $theme=$tmp_theme[1]."/";
	}
	$js=true;
      } 
    }
    $theme=INDEXMENU_IMG_RELDIR."/".$theme;
    //id generation method 
    if (preg_match('/id#(\S+)/u',$match[1],$id) >0) $gen_id=$id[1];
    //max option
    if (preg_match('/max#(\d+)($|\s+|#(\d+))/u',$match[1],$maxtmp) >0) {
      $max=$maxtmp[1];
      if ($maxtmp[3]) $jsajax .=  "&max=".$maxtmp[3];
      //disable cookie to avoid javascript errors
      $nocookie=true;
    }
    //max js option
    if (preg_match('/maxjs#(\d+)/u',$match[1],$maxtmp) >0) $maxjs=$maxtmp[1];
    //js options
    $js_opts=compact('theme','gen_id','nocookie','navbar','noscroll','maxjs','notoc','jsajax');
    return array($ns,
		 $js_opts,
		 $sort,
		 array('level' => $level,
		       'nons' => $nons,
		       'nss' => $nss,
		       'max' => $max,
		       'js' => $js,
		       'skip_index' => $this->getConf('skip_index'),
		       'skip_file' => $this->getConf('skip_file'),
		       'headpage' => $this->getConf('headpage'),
		       'hide_headpage' => $this->getConf('hide_headpage')
		       )
		 );
  }  
  
  /**
   * Render output
   */
  function render($mode, &$renderer, $data) {
    global $ACT;
    global $conf;
    global $INFO;
    if($mode == 'xhtml'){ 
      //Check user permission to display indexmenu in a preview page
      if ($ACT == 'preview' && 
	  $this->getConf('only_admins') && 
	  $conf['useacl'] &&
	  $INFO['perm'] < AUTH_ADMIN)
	return false;
      $n=$this->_indexmenu($data);
      if (!@$n) {
	$n = $this->getConf('empty_msg');
	$n= str_replace('{{ns}}',cleanID($data[0]),$n);
	$n=p_render('xhtml',p_get_instructions($n),$info);
      }
      $renderer->doc .= $n;
      return true;
    } else if ($mode == 'metadata') {
      //this is an indexmenu page;
      $renderer->meta['indexmenu'] = true;
      unset($renderer->persistent['indexmenu']);
      return true;
    } else {
      return false;
    }
  }
  
  /**
   * Return the index 
   * @author Samuele Tognini <samuele@cli.di.unipi.it>
   *
   * This function is a simple hack of Dokuwiki html_index($ns)
   * @author Andreas Gohr <andi@splitbrain.org>
   */
  function _indexmenu($myns) {
    global $conf;
    $ns = $myns[0];
    $js_opts=$myns[1];
    $sort=$myns[2];
    $opts = $myns[3];
    $output=false;
    $data = array();
    $js_name=false;
    $fsdir="/".utf8_encodeFN(str_replace(':','/',$ns));
    if ($sort=='nosort') {
      search($data,$conf['datadir'],array($this,'_search_index'),$opts,$fsdir);
    } else {
      $this->_search($data,$conf['datadir'],array($this,'_search_index'),$opts,$fsdir);
    }
    if (!$data) return false;

    //javascript index
    if ($opts['js']) {      
      $ns = str_replace('/',':',$ns);
      $output_tmp=$this->_jstree($data,$ns,$js_opts,$js_name,$opts['max']);
      //remove unwanted nodes from standard index 
      $this->_clean_data($data);
    }
    //Nojs dokuwiki index
    $output.="\n".'<div';
    if ($js_name) $output.=' id="nojs_'.$js_name.'" style="display:block;"';
    $output.=">\n";
    $output.=html_buildlist($data,'idx',array($this,"_html_list_index"),"html_li_index");
    $output.="</div>\n";
    $output.=$output_tmp;
    return $output;
  }

  /**
   * Build the browsable index of pages using javascript
   *
   * @author  Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _jstree($data,$ns,$js_opts,&$js_name,$max) {
    global $conf;
    $hns=false;
    //Render requested ns as root
    $headpage=$this->getConf('headpage');
    if (empty($ns) && !empty($headpage)) $headpage.=','.$conf['start'];
    $title=$this->_getTitle($ns,$headpage,$hns);
    if (empty($title)) {
      (empty($ns)) ? $title=$conf['title'] : $title=$ns;
    }
    if (empty($data)) return false;
    // Id generation method
    if (is_numeric($js_opts['gen_id'])) {
      $jsns=$js_opts['gen_id'];
    } elseif ($js_opts['gen_id'] == 'ns') {
      $jsns = sprintf("%u",crc32($ns));
    } else {
      $jsns=uniqid(rand());
    }
    $js_name="indexmenu_".$jsns;
    $out = "<script type='text/javascript' charset='utf-8'>\n";
    $out .= "var $js_name = new dTree('".$js_name."','".$js_opts['theme']."');\n";
    $out .= "$js_name.config.urlbase='".wl()."';\n";
    $out .= "$js_name.config.sepchar='".idfilter(':')."';\n";
    if ($js_opts['notoc']) $out .="$js_name.config.toc=false;\n";
    if ($js_opts['nocookie']) $out .="$js_name.config.useCookies=false;\n";
    if ($js_opts['noscroll']) $out .="$js_name.config.scroll=false;\n";
    if ($js_opts['maxjs'] > 0)  $out .= "$js_name.config.maxjs=".$js_opts['maxjs'].";\n";
    if (!empty($js_opts['jsajax'])) $out .= "$js_name.config.jsajax='".$js_opts['jsajax']."';\n";
    $out .= $js_name.".add('".idfilter(cleanID($ns))."',0,-1,'".$title."'";
    if ($hns) $out .= ",'".idfilter(cleanID($hns))."'";
    $out .= ");\n";    
    $anodes = $this->_jsnodes($data,$js_name);
    $out .= $anodes[0];
    $extra .= $anodes[1];
    $out .= "document.write(".$js_name.");\n";
    //Not all closed
    if ($node>0 || !empty($extra)) {
      //js open nodes
      $extra="addInitEvent(function(){".$js_name.".getOpenTo('".$extra."');});\n";
      $out .= $extra;
    }
    if ($js_opts['navbar']) $out .= "addInitEvent(function(){".$js_name.".openCurNS(".$max.");});\n";
    if (!$js_opts['nocookie']) $out .= "addInitEvent(function(){".$js_name.".openCookies();});\n";
    $out .= "</script>\n";
    return $out;
  }

  /**
   * Return array of javascript nodes and nodes to open.
   *
   * @author  Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _jsnodes($data,$js_name,$noajax=1) {
    if (empty($data)) return false;
    //Array of nodes to check
    $q=array('0');
    //Current open node
    $node=0;
    $out='';
    $extra='';
    if ($noajax) {
      $jscmd=$js_name.".add";
      $com=";\n";
    } else {
      $jscmd="new Array ";
      $com=",";
    }
    foreach ($data as $i=>$item){
      $i++;
      //Remove already processed nodes (greater level = lower level)
      while ($item['level'] <= $data[end($q)-1]['level']) {
	array_pop($q);  
      }

      //till i found its father node
      if ($item['level']==1) {
	//root node
	$father='0';
      } else {
	//Father node
	$father=end($q);
      }
      //add node and its options
      if ($item['type'] == 'd' ) {
	//Searh the lowest open node of a tree branch in order to open it.
	if ($item['open']) ($item['level'] < $data[$node]['level']) ? $node=$i : $extra .= "$i,";
	//insert node in last position
	array_push($q,$i);
      }
      $out .= $jscmd."('".idfilter($item['id'])."',$i,".$father.",'".$item['title']."'";
      //hns
      ($item['hns']) ? $out .= ",'".idfilter($item['hns'])."'" : $out .= ",0";
      //MAX option
      if ($item['type']=='l') $out .= ",1";
      $out .= ")".$com;
    }
    $extra=rtrim($extra,',');
    return array($out,$extra);
  }
  /**
   * Get page title, checking for headpages
   *
   * @author  Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _getTitle ($ns,$headpage,&$hns) {
    global $conf;
    $hns=false;
    $title=noNS($ns);
    if (empty($headpage)) return $title;
    $ahp=explode(",",$headpage);
    foreach ($ahp as $hp) {
      switch ($hp) {
      case ":inside:":
	$page=$ns.":".noNS($ns);
	break;
      case ":same:":
	$page=$ns;
	break;
	//it's an inside start
      case ":start:":
	$page=$ns.":".$conf['start'];
	break;
	//inside pages
      default:
	$page=$ns.":".cleanID($hp);
      }
      //check headpage
      if (@file_exists(wikiFN($page)) && auth_quickaclcheck($page) >= AUTH_READ) {
	if ($conf['useheading'] && $title_tmp=p_get_metadata($page,'title')) $title=$title_tmp;
	$title=htmlspecialchars($title,ENT_QUOTES);
	$hns=$page;
	//headpage found, exit for
	break;
      }
    }
    return $title;
  }

  /**
   * Parse namespace request
   *
   * @author  Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _parse_ns ($ns) {
    global $ID;
    $ns=preg_replace("/^\.(:|$)/",dirname(str_replace(':','/',$ID))."$1",$ns);
    $ns=str_replace("/",":",$ns);
    $ns = cleanID($ns);
    return $ns;
  }
  
  /**
   * Clean index data from unwanted nodes in nojs mode.
   *
   * @author  Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _clean_data(&$data) {
    foreach ($data as $i=>$item) {
      //closed node
      if ($item['type'] == "d" && !$item['open']) {
	$a=$i+1;
	$level=$data[$i]['level'];
	//search and remove every lower and closed nodes
	while ($data[$a]['level'] > $level && !$data[$a]['open']) {
	  unset($data[$a]);
	  $a++;
	}
      }
      $i++;
    }
  }

  /**
   * Build the browsable index of pages
   *
   * $opts['ns'] is the current namespace
   *
   * @author  Andreas Gohr <andi@splitbrain.org>
   * modified by Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _search_index(&$data,$base,$file,$type,$lvl,$opts){
    global $conf;
    $hns=false;
    $return=false;
    $isopen=false;
    $item = array();
    $skip_index=$opts['skip_index'];
    $skip_file=$opts['skip_file'];
    $headpage=$opts['headpage'];
    $id = pathID($file);
    if($type == 'd'){
      // Skip folders in plugin conf
      if (!empty($skip_index) &&
	  preg_match($skip_index, $file))
	return false;
      //check ACL (for namespaces too)
      if (auth_quickaclcheck($id.':*') < AUTH_READ) return false;
      //Open requested level
      if ($opts['level'] > $lvl || $opts['level'] == -1) $isopen=true;
      //Search optional namespaces
      if (!empty($opts['nss'])){
	$nss=$opts['nss'];
	for ($a=0; $a<count($nss);$a++) {
	  if (preg_match("/^".$id."($|:.+)/i",$nss[$a][0],$match)) {
	    //It contains an optional namespace
	    $isopen=true;
	  } elseif (preg_match("/^".$nss[$a][0]."(:.*)/i",$id,$match)) {
	    //It's inside an optional namespace
	    if ($nss[$a][1] == -1 || substr_count($match[1],":") < $nss[$a][1]) {
	      $isopen=true; 
	    } else {
	      $isopen=false;
	    }
	  }
	}
      }
      //MAX option
      if ($opts['nons']) {
	return $isopen;
      } elseif ($opts['max'] >0 && !$isopen && $lvl >= $opts['max']) {
	$isopen=false;
	//Stop recursive searching
	$return=false;
	//change type
	$type="l";
      } elseif ($opts['js']) {
	$return=true;
      } else {
	$return=$isopen;
      }
      //Set title and headpage
      $title=$this->_getTitle($id,$headpage,$hns);
    } else {
      //Nons.Set all pages at first level
      if ($opts['nons']) $lvl=1;
      //don't add
      if (!preg_match('#\.txt$#',$file)) return false;
      //check hiddens and acl
      if (isHiddenPage($id) || auth_quickaclcheck($id) < AUTH_READ) return false;
      //Skip files in plugin conf
      if (!empty($skip_file) &&
	  preg_match($skip_file, $file))
	return false;
      //Skip headpages to hide
      if (!$opts['nons'] && 
	  !empty($headpage) && 
	  $opts['hide_headpage']) {
	if ($id==$conf['start']) return false;
	$ahp=explode(",",$headpage);
	foreach ($ahp as $hp) {
	  switch ($hp) {
	  case ":inside:":
	    if (noNS($id)==noNS(getNS($id)))  return false;
	    break;
	  case ":same:":
	    if (@is_dir(dirname(wikiFN($id))."/".utf8_encodeFN(noNS($id)))) return false;
	    break;
	    //it' s an inside start
	  case ":start:":
	    if (noNS($id)==$conf['start']) return false;
	    break;
	  default:
	    if (noNS($id)==cleanID($hp)) return false;
	  }
	}
      }
      //Set title
      if (!$conf['useheading'] || !$title=p_get_metadata($id,'title')) $title=noNS($id);
      $title=htmlspecialchars($title,ENT_QUOTES);
    }

    $data[]=array( 'id'    => $id,
		   'type'  => $type,
		   'level' => $lvl,
		   'open'  => $isopen, 
		   'title' => $title,
		   'hns'   => $hns
		   );

    return $return;
  }  


  /**
   * Index item formatter
   *
   * User function for html_buildlist()
   *
   * @author Andreas Gohr <andi@splitbrain.org>
   * modified by Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _html_list_index($item){
    $ret = '';
    //namespace
    if($item['type']=='d' || $item['type']=='l'){
      $link=$item['id'];
      $more='idx='.$item['id'];
      //namespace link
      if ($item['hns']) {
	$link=$item['hns'];
	$tagid="indexmenu_idx_head";
	$more='';
      } else {
	//namespace with headpage
	$tagid="indexmenu_idx";
      }
      $ret .= '<a href="'.wl($link,$more).'" class="'.$tagid.'">';
      $ret .= $item['title'];
      $ret .= '</a>';
    }else{
      //page link
      $ret .= html_wikilink(':'.$item['id']);
    }  
    return $ret;
  }


  /**
   * recurse direcory
   *
   * This function recurses into a given base directory
   * and calls the supplied function for each file and directory
   *
   * @param   array ref $data The results of the search are stored here
   * @param   string    $base Where to start the search
   * @param   callback  $func Callback (function name or arayy with object,method)
   * @param   string    $dir  Current directory beyond $base
   * @param   int       $lvl  Recursion Level
   * @author  Andreas Gohr <andi@splitbrain.org>
   * modified by Samuele Tognini <samuele@cli.di.unipi.it>
   */
  function _search(&$data,$base,$func,$opts,$dir='',$lvl=1){
    $dirs   = array();
    $files  = array();
    $data_tmp=array();

    //read in directories and files
    $dh = @opendir($base.'/'.$dir);
    if(!$dh) return;
    while(($file = readdir($dh)) !== false){
      if(preg_match('/^[\._]/',$file)) continue; //skip hidden files and upper dirs
      if(is_dir($base.'/'.$dir.'/'.$file)){
	$dirs[] = $dir.'/'.$file;
	continue;
      }elseif(substr($file,-5) == '.lock'){
	//skip lockfiles
	continue;
      }
      $files[] = $dir.'/'.$file;
    }
    closedir($dh);
    sort($dirs);
    //give directories to userfunction then recurse
    foreach($dirs as $dir){
      if (search_callback($func,$data,$base,$dir,'d',$lvl,$opts)){
	$this->_search($data,$base,$func,$opts,$dir,$lvl+1);
      }
    }
    //now handle the files
    foreach($files as $file){
      search_callback($func,$data_tmp,$base,$file,'f',$lvl,$opts);
    }
    usort($data_tmp,array($this,"_cmp"));
    $data=array_merge($data,$data_tmp);
  }

  /**
   * Sort nodes
   *
   */
  function _cmp($a, $b) {
    return strnatcasecmp($a['title'], $b['title']);
  }
} //Indexmenu class end  
